<?php

namespace App\Http\Controllers;

use App\Models\Abg;
use App\Models\Affidavit;
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

        // 2. Permit Type Distribution (ITAS, ITAP, VOA, ITK)
        $izinRaw = Wna::query()
            ->select('jenis_izin_tinggal', DB::raw('count(*) as total'))
            ->groupBy('jenis_izin_tinggal')
            ->pluck('total', 'jenis_izin_tinggal');

        $distribusiIzin = [
            [
                'jenis' => 'ITAS',
                'label' => 'Izin Tinggal Terbatas (ITAS)',
                'total' => (int) ($izinRaw['ITAS'] ?? 0),
                'persen' => $totalWna > 0 ? round(((int) ($izinRaw['ITAS'] ?? 0) / $totalWna) * 100, 1) : 0.0,
            ],
            [
                'jenis' => 'ITAP',
                'label' => 'Izin Tinggal Tetap (ITAP)',
                'total' => (int) ($izinRaw['ITAP'] ?? 0),
                'persen' => $totalWna > 0 ? round(((int) ($izinRaw['ITAP'] ?? 0) / $totalWna) * 100, 1) : 0.0,
            ],
            [
                'jenis' => 'VOA',
                'label' => 'Visa on Arrival (VOA)',
                'total' => (int) ($izinRaw['VOA'] ?? 0),
                'persen' => $totalWna > 0 ? round(((int) ($izinRaw['VOA'] ?? 0) / $totalWna) * 100, 1) : 0.0,
            ],
            [
                'jenis' => 'ITK',
                'label' => 'Izin Tinggal Kunjungan (ITK)',
                'total' => (int) ($izinRaw['ITK'] ?? 0),
                'persen' => $totalWna > 0 ? round(((int) ($izinRaw['ITK'] ?? 0) / $totalWna) * 100, 1) : 0.0,
            ],
        ];

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

        // 4. Monthly WNA Registration Trend in Current Year
        $monthlyWnaRaw = Wna::query()
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->pluck('created_at');

        $monthlyCounts = array_fill(1, 12, 0);
        foreach ($monthlyWnaRaw as $createdAt) {
            if ($createdAt) {
                $month = (int) Carbon::parse($createdAt)->month;
                if ($month >= 1 && $month <= 12) {
                    $monthlyCounts[$month]++;
                }
            }
        }

        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $trenWnaBulanan = [];
        foreach ($monthlyCounts as $m => $total) {
            $trenWnaBulanan[] = [
                'bulan' => $m,
                'label' => $namaBulan[$m],
                'total' => $total,
            ];
        }

        // 5. Admin-only Metrics (Permohonan, Penerbitan, Realisasi PNBP, Data User)
        $adminMetrics = null;
        if ($isAdmin) {
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

            $adminMetrics = [
                'total_permohonan' => Permohonan::query()->count(),
                'total_penerbitan' => Penerbitan::query()->count(),
                'total_users' => User::query()->count(),
                'pnbp' => [
                    'tahun' => $currentYear,
                    'total_target' => $totalTargetPnbp,
                    'total_realisasi' => $totalRealisasiPnbp,
                    'selisih' => round($totalRealisasiPnbp - $totalTargetPnbp, 2),
                    'persentase' => $persentasePnbp,
                    'bulanan' => $monthlyPnbp,
                ],
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_wna' => $totalWna,
                'total_abg' => $totalAbg,
                'total_affidavit' => $totalAffidavit,
                'early_warning' => $earlyWarningCounts,
                'admin' => $adminMetrics,
            ],
            'charts' => [
                'distribusi_izin' => $distribusiIzin,
                'top_kewarganegaraan' => $topKewarganegaraan,
                'tren_wna_bulanan' => $trenWnaBulanan,
                'tahun_berjalan' => $currentYear,
            ],
        ]);
    }
}
