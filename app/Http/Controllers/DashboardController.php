<?php

namespace App\Http\Controllers;

use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\User;
use App\Models\Wna;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the operational dashboard with live database analytics and statistics.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user?->isAdmin() ?? false;

        $referenceDate = Carbon::now('Asia/Jakarta')->startOfDay();
        $currentYear = (int) $referenceDate->year;
        $startOfYear = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        $endOfYear = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();

        // 1. General Operational Counts (Available to Admin & Petugas)
        $totalWna = Wna::query()->count();
        $totalAbg = Abg::query()->count();
        $totalAffidavit = Affidavit::query()->count();
        $earlyWarningCounts = Wna::getEarlyWarningCounts($referenceDate);

        // 2. Permit Type Distribution & 4 Pie Charts (ITAS, ITAP, VOA, ITK)
        $permitCategories = [
            'ITAS' => 'Izin Tinggal Terbatas (ITAS)',
            'ITAP' => 'Izin Tinggal Tetap (ITAP)',
            'VOA' => 'Visa on Arrival (VOA)',
            'ITK' => 'Izin Tinggal Kunjungan (ITK)',
        ];

        $rawPermits = DB::table('wna')
            ->select('kabupaten', 'jenis_izin_tinggal', DB::raw('count(*) as total'))
            ->groupBy('kabupaten', 'jenis_izin_tinggal')
            ->get();

        $matrix = [
            'semua' => array_fill_keys(array_keys($permitCategories), 0),
            'ponorogo' => array_fill_keys(array_keys($permitCategories), 0),
            'pacitan' => array_fill_keys(array_keys($permitCategories), 0),
            'trenggalek' => array_fill_keys(array_keys($permitCategories), 0),
        ];

        foreach ($rawPermits as $row) {
            $kabLower = strtolower((string) $row->kabupaten);
            $jenis = (string) $row->jenis_izin_tinggal;
            $count = (int) $row->total;

            if (isset($matrix['semua'][$jenis])) {
                $matrix['semua'][$jenis] += $count;
            }

            if (isset($matrix[$kabLower][$jenis])) {
                $matrix[$kabLower][$jenis] += $count;
            }
        }

        $regionTitles = [
            'semua' => 'Seluruh Wilayah Kerja',
            'ponorogo' => 'Kabupaten Ponorogo',
            'pacitan' => 'Kabupaten Pacitan',
            'trenggalek' => 'Kabupaten Trenggalek',
        ];

        $pieIzinTinggal = [];
        foreach ($matrix as $key => $counts) {
            $regionTotal = array_sum($counts);
            $items = [];
            foreach ($permitCategories as $jenis => $label) {
                $cnt = $counts[$jenis];
                $items[] = [
                    'jenis' => $jenis,
                    'label' => $label,
                    'total' => $cnt,
                    'persen' => $regionTotal > 0 ? round(($cnt / $regionTotal) * 100, 1) : 0.0,
                ];
            }

            $pieIzinTinggal[$key] = [
                'title' => $regionTitles[$key],
                'kabupaten' => $key,
                'total' => $regionTotal,
                'items' => $items,
            ];
        }

        // Backward compatibility for existing tests/consumers
        $distribusiIzin = $pieIzinTinggal['semua']['items'];

        // 3. Top 5 Nationalities
        $topKewarganegaraan = DB::table('wna')
            ->select('kewarganegaraan', DB::raw('count(*) as total'))
            ->whereNotNull('kewarganegaraan')
            ->where('kewarganegaraan', '!=', '')
            ->groupBy('kewarganegaraan')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'negara' => (string) $item->kewarganegaraan,
                'total' => (int) $item->total,
                'persen' => $totalWna > 0 ? round(((int) $item->total / $totalWna) * 100, 1) : 0.0,
            ]);

        // 4. Monthly WNA Registration Trend in Current Year (Backward Compatibility)
        $monthlyWnaRaw = Wna::query()
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->pluck('created_at');

        $monthlyWnaCounts = array_fill(1, 12, 0);
        foreach ($monthlyWnaRaw as $createdAt) {
            if ($createdAt) {
                $month = (int) Carbon::parse($createdAt)->month;
                if ($month >= 1 && $month <= 12) {
                    $monthlyWnaCounts[$month]++;
                }
            }
        }

        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $trenWnaBulanan = [];
        foreach ($monthlyWnaCounts as $m => $total) {
            $trenWnaBulanan[] = [
                'bulan' => $m,
                'label' => $namaBulan[$m],
                'total' => $total,
            ];
        }

        // 5. CR-006: Monthly Permohonan Trend (Source: permohonan.tanggal)
        $monthlyPermohonanDates = DB::table('permohonan')
            ->whereBetween('tanggal', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->pluck('tanggal');

        $permohonanCounts = array_fill(1, 12, 0);
        foreach ($monthlyPermohonanDates as $date) {
            if ($date) {
                try {
                    $month = (int) Carbon::parse($date)->month;
                    if ($month >= 1 && $month <= 12) {
                        $permohonanCounts[$month]++;
                    }
                } catch (\Throwable) {
                    // Safe handling of anomalous date values
                }
            }
        }

        $trenPermohonanBulanan = [];
        foreach ($permohonanCounts as $m => $total) {
            $trenPermohonanBulanan[] = [
                'bulan' => $m,
                'label' => $namaBulan[$m],
                'total' => $total,
            ];
        }
        $totalPermohonanTahunIni = array_sum($permohonanCounts);

        // 6. CR-007: Monthly Laporan Izin Tinggal Trend (Source: laporan_izin_tinggal.bulan)
        $laporanRecords = DB::table('laporan_izin_tinggal')
            ->select('id', 'bulan', 'disetujui_l', 'disetujui_p', 'ditolak_l', 'ditolak_p')
            ->whereBetween('bulan', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->get();

        $laporanCounts = array_fill(1, 12, 0);
        $laporanVolume = array_fill(1, 12, 0);
        $laporanDisetujui = array_fill(1, 12, 0);
        $laporanDitolak = array_fill(1, 12, 0);

        foreach ($laporanRecords as $lap) {
            if ($lap->bulan) {
                try {
                    $m = (int) Carbon::parse($lap->bulan)->month;
                    if ($m >= 1 && $m <= 12) {
                        $laporanCounts[$m]++;
                        $disetujui = (int) ($lap->disetujui_l + $lap->disetujui_p);
                        $ditolak = (int) ($lap->ditolak_l + $lap->ditolak_p);
                        $laporanDisetujui[$m] += $disetujui;
                        $laporanDitolak[$m] += $ditolak;
                        $laporanVolume[$m] += ($disetujui + $ditolak);
                    }
                } catch (\Throwable) {
                    // Safe handling of anomalous date values
                }
            }
        }

        $trenLaporanBulanan = [];
        foreach ($laporanCounts as $m => $count) {
            $trenLaporanBulanan[] = [
                'bulan' => $m,
                'label' => $namaBulan[$m],
                'total' => $count,
                'disetujui' => $laporanDisetujui[$m],
                'ditolak' => $laporanDitolak[$m],
                'volume' => $laporanVolume[$m],
            ];
        }
        $totalLaporanTahunIni = array_sum($laporanCounts);

        // 7. Operational & PNBP Metrics for All Roles (Permohonan, Laporan, Penerbitan, Realisasi PNBP)
        $pnbpRecords = RealisasiPnbp::query()
            ->whereBetween('bulan', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->orderBy('bulan', 'asc')
            ->get();

        $totalTargetPnbp = (float) $pnbpRecords->sum(fn (RealisasiPnbp $r) => (float) $r->target);
        $totalRealisasiPnbp = (float) $pnbpRecords->sum(fn (RealisasiPnbp $r) => (float) $r->realisasi);
        $persentasePnbp = $totalTargetPnbp > 0 ? round(($totalRealisasiPnbp / $totalTargetPnbp) * 100, 2) : 0.0;

        $monthlyPnbp = $pnbpRecords->map(fn (RealisasiPnbp $r) => [
            'bulan' => Carbon::parse($r->bulan)->translatedFormat('M Y'),
            'target' => (float) $r->target,
            'realisasi' => (float) $r->realisasi,
            'persentase' => (float) $r->persentase,
        ]);

        // CR-009: 12-Month Line Series for Realisasi PNBP
        $pnbpKeyedByMonth = $pnbpRecords->keyBy(fn (RealisasiPnbp $r) => (int) Carbon::parse($r->bulan)->month);

        $pnbpLineSeries = [];
        for ($m = 1; $m <= 12; $m++) {
            $rec = $pnbpKeyedByMonth->get($m);
            $target = $rec ? (float) $rec->target : 0.0;
            $realisasi = $rec ? (float) $rec->realisasi : 0.0;
            $persentase = $rec ? (float) $rec->persentase : 0.0;
            $selisih = $rec ? round($realisasi - $target, 2) : 0.0;

            $pnbpLineSeries[] = [
                'bulan' => $m,
                'label' => $namaBulan[$m],
                'target' => $target,
                'realisasi' => $realisasi,
                'persentase' => $persentase,
                'selisih' => $selisih,
                'has_data' => $rec !== null,
            ];
        }

        $pnbpData = [
            'tahun' => $currentYear,
            'total_target' => $totalTargetPnbp,
            'total_realisasi' => $totalRealisasiPnbp,
            'selisih' => round($totalRealisasiPnbp - $totalTargetPnbp, 2),
            'persentase' => $persentasePnbp,
            'bulanan' => $monthlyPnbp,
            'line_series' => $pnbpLineSeries,
        ];

        $totalPermohonan = Permohonan::query()->count();
        $totalPenerbitan = Penerbitan::query()->count();
        $totalLaporan = LaporanIzinTinggal::query()->count();

        // Admin-only container (preserved for backward compatibility with admin-specific tests)
        $adminMetrics = null;
        if ($isAdmin) {
            $adminMetrics = [
                'total_permohonan' => $totalPermohonan,
                'total_penerbitan' => $totalPenerbitan,
                'total_laporan' => $totalLaporan,
                'total_users' => User::query()->count(),
                'pnbp' => $pnbpData,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_wna' => $totalWna,
                'total_abg' => $totalAbg,
                'total_affidavit' => $totalAffidavit,
                'early_warning' => $earlyWarningCounts,
                'total_permohonan' => $totalPermohonan,
                'total_laporan' => $totalLaporan,
                'total_penerbitan' => $totalPenerbitan,
                'pnbp' => $pnbpData,
                'admin' => $adminMetrics,
            ],
            'charts' => [
                'distribusi_izin' => $distribusiIzin,
                'pie_izin_tinggal' => $pieIzinTinggal,
                'top_kewarganegaraan' => $topKewarganegaraan,
                'tren_wna_bulanan' => $trenWnaBulanan,
                'tren_permohonan_bulanan' => $trenPermohonanBulanan,
                'total_permohonan_tahun_ini' => $totalPermohonanTahunIni,
                'tren_laporan_bulanan' => $trenLaporanBulanan,
                'total_laporan_tahun_ini' => $totalLaporanTahunIni,
                'tahun_berjalan' => $currentYear,
            ],
        ]);
    }
}
