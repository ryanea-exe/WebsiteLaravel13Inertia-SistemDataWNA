<?php

use App\Enums\JenisIzinTinggal;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\User;
use App\Models\Wna;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard displays accurate operational statistics from database for authenticated users', function () {
    $now = Carbon::now('Asia/Jakarta')->startOfDay();
    $currentYear = (int) $now->year;

    // Seed WNA records with known characteristics across different kabupaten
    // 1 expired ITAS in Ponorogo
    Wna::factory()->create([
        'nama' => 'John Expired',
        'kabupaten' => 'Ponorogo',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'kewarganegaraan' => 'Japan',
        'masa_berlaku' => $now->copy()->subDays(5)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 2, 10),
    ]);

    // 1 urgent red ITAP in Pacitan
    Wna::factory()->create([
        'nama' => 'Ali Red',
        'kabupaten' => 'Pacitan',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAP,
        'kewarganegaraan' => 'Malaysia',
        'masa_berlaku' => $now->copy()->addDays(10)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 2, 15),
    ]);

    // 1 urgent blue VOA in Trenggalek
    Wna::factory()->create([
        'nama' => 'Siti Blue',
        'kabupaten' => 'Trenggalek',
        'jenis_izin_tinggal' => JenisIzinTinggal::VOA,
        'kewarganegaraan' => 'Malaysia',
        'masa_berlaku' => $now->copy()->addDays(40)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 5, 20),
    ]);

    // 1 ITK in Ponorogo (CR-001 & CR-003)
    Wna::factory()->create([
        'nama' => 'Chen ITK',
        'kabupaten' => 'Ponorogo',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITK,
        'kewarganegaraan' => 'China',
        'masa_berlaku' => $now->copy()->addDays(60)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 6, 1),
    ]);

    // Seed ABG and Affidavit
    Abg::factory()->count(2)->create();
    Affidavit::factory()->count(1)->create();

    $petugas = User::factory()->user()->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('stats.total_wna', 4)
        ->where('stats.total_abg', 2)
        ->where('stats.total_affidavit', 1)
        ->where('stats.early_warning.semua', 3)
        ->where('stats.early_warning.kedaluwarsa', 1)
        ->where('stats.early_warning.merah', 1)
        ->where('stats.early_warning.kuning', 0)
        ->where('stats.early_warning.biru', 2)
        ->where('stats.admin', null)
        ->where('charts.tahun_berjalan', $currentYear)
        ->where('charts.distribusi_izin.0.jenis', 'ITAS')
        ->where('charts.distribusi_izin.0.total', 1)
        ->where('charts.distribusi_izin.1.jenis', 'ITAP')
        ->where('charts.distribusi_izin.1.total', 1)
        ->where('charts.distribusi_izin.2.jenis', 'VOA')
        ->where('charts.distribusi_izin.2.total', 1)
        ->where('charts.distribusi_izin.3.jenis', 'ITK')
        ->where('charts.distribusi_izin.3.total', 1)
        // CR-003: 4 Pie chart distributions
        ->where('charts.pie_izin_tinggal.semua.total', 4)
        ->where('charts.pie_izin_tinggal.ponorogo.total', 2)
        ->where('charts.pie_izin_tinggal.pacitan.total', 1)
        ->where('charts.pie_izin_tinggal.trenggalek.total', 1)
        ->where('charts.pie_izin_tinggal.ponorogo.items.0.jenis', 'ITAS')
        ->where('charts.pie_izin_tinggal.ponorogo.items.0.total', 1)
        ->where('charts.pie_izin_tinggal.ponorogo.items.3.jenis', 'ITK')
        ->where('charts.pie_izin_tinggal.ponorogo.items.3.total', 1)
        ->where('charts.pie_izin_tinggal.pacitan.items.1.jenis', 'ITAP')
        ->where('charts.pie_izin_tinggal.pacitan.items.1.total', 1)
        ->where('charts.pie_izin_tinggal.trenggalek.items.2.jenis', 'VOA')
        ->where('charts.pie_izin_tinggal.trenggalek.items.2.total', 1)
        ->has('charts.top_kewarganegaraan', 3)
        ->where('charts.top_kewarganegaraan.0.negara', 'Malaysia')
        ->where('charts.top_kewarganegaraan.0.total', 2)
        ->has('charts.tren_wna_bulanan', 12)
        ->where('charts.tren_wna_bulanan.1.bulan', 2) // Feb
        ->where('charts.tren_wna_bulanan.1.total', 2)
    );
});

test('CR-003: pie charts correctly group by kabupaten and permit type without double counting', function () {
    $now = Carbon::now('Asia/Jakarta');

    // Ponorogo: 2 ITAS, 1 ITK
    Wna::factory()->count(2)->create([
        'kabupaten' => 'Ponorogo',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
    ]);
    Wna::factory()->create([
        'kabupaten' => 'Ponorogo',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITK,
    ]);

    // Pacitan: 3 VOA, 1 ITAP
    Wna::factory()->count(3)->create([
        'kabupaten' => 'Pacitan',
        'jenis_izin_tinggal' => JenisIzinTinggal::VOA,
    ]);
    Wna::factory()->create([
        'kabupaten' => 'Pacitan',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAP,
    ]);

    // Trenggalek: 1 ITAS
    Wna::factory()->create([
        'kabupaten' => 'Trenggalek',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
    ]);

    $petugas = User::factory()->user()->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        // Total across all: 3 (Ponorogo) + 4 (Pacitan) + 1 (Trenggalek) = 8
        ->where('charts.pie_izin_tinggal.semua.total', 8)
        ->where('charts.pie_izin_tinggal.ponorogo.total', 3)
        ->where('charts.pie_izin_tinggal.pacitan.total', 4)
        ->where('charts.pie_izin_tinggal.trenggalek.total', 1)
        // Check Ponorogo items
        ->where('charts.pie_izin_tinggal.ponorogo.items.0.total', 2) // ITAS
        ->where('charts.pie_izin_tinggal.ponorogo.items.0.persen', fn ($p) => (float) $p === 66.7)
        ->where('charts.pie_izin_tinggal.ponorogo.items.3.total', 1) // ITK
        ->where('charts.pie_izin_tinggal.ponorogo.items.3.persen', fn ($p) => (float) $p === 33.3)
        // Check Pacitan items
        ->where('charts.pie_izin_tinggal.pacitan.items.1.total', 1) // ITAP
        ->where('charts.pie_izin_tinggal.pacitan.items.1.persen', fn ($p) => (float) $p === 25.0)
        ->where('charts.pie_izin_tinggal.pacitan.items.2.total', 3) // VOA
        ->where('charts.pie_izin_tinggal.pacitan.items.2.persen', fn ($p) => (float) $p === 75.0)
        // Check Trenggalek items
        ->where('charts.pie_izin_tinggal.trenggalek.items.0.total', 1) // ITAS
        ->where('charts.pie_izin_tinggal.trenggalek.items.0.persen', fn ($p) => (float) $p === 100.0)
        ->where('charts.pie_izin_tinggal.trenggalek.items.1.total', 0) // ITAP
        ->where('charts.pie_izin_tinggal.trenggalek.items.1.persen', fn ($p) => (float) $p === 0.0)
    );
});

test('CR-006: monthly permohonan trend uses tanggal business date column and ignores created_at', function () {
    $now = Carbon::now('Asia/Jakarta');
    $currentYear = (int) $now->year;

    // Create 2 permohonan in March (tanggal: 2026-03-15), but created_at is in October
    Permohonan::factory()->create([
        'tanggal' => Carbon::createFromDate($currentYear, 3, 15)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 10, 1),
    ]);
    Permohonan::factory()->create([
        'tanggal' => Carbon::createFromDate($currentYear, 3, 20)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 10, 5),
    ]);

    // Create 1 permohonan in July (tanggal: 2026-07-01), created_at is in December
    Permohonan::factory()->create([
        'tanggal' => Carbon::createFromDate($currentYear, 7, 1)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 12, 1),
    ]);

    $petugas = User::factory()->user()->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->has('charts.tren_permohonan_bulanan', 12)
        ->where('charts.total_permohonan_tahun_ini', 3)
        // Month 3 (Mar) should have 2
        ->where('charts.tren_permohonan_bulanan.2.bulan', 3)
        ->where('charts.tren_permohonan_bulanan.2.label', 'Mar')
        ->where('charts.tren_permohonan_bulanan.2.total', 2)
        // Month 7 (Jul) should have 1
        ->where('charts.tren_permohonan_bulanan.6.bulan', 7)
        ->where('charts.tren_permohonan_bulanan.6.label', 'Jul')
        ->where('charts.tren_permohonan_bulanan.6.total', 1)
        // Month 10 (Okt) should be 0 because created_at is ignored
        ->where('charts.tren_permohonan_bulanan.9.bulan', 10)
        ->where('charts.tren_permohonan_bulanan.9.total', 0)
        // Month 12 (Des) should be 0 because created_at is ignored
        ->where('charts.tren_permohonan_bulanan.11.bulan', 12)
        ->where('charts.tren_permohonan_bulanan.11.total', 0)
    );
});

test('CR-007: monthly laporan izin tinggal trend uses bulan business date column and ignores created_at', function () {
    $now = Carbon::now('Asia/Jakarta');
    $currentYear = (int) $now->year;

    // Report for April (bulan: 2026-04-01), but created_at in November
    LaporanIzinTinggal::factory()->create([
        'bulan' => Carbon::createFromDate($currentYear, 4, 1)->toDateString(),
        'disetujui_l' => 5,
        'disetujui_p' => 10,
        'ditolak_l' => 1,
        'ditolak_p' => 0,
        'created_at' => Carbon::createFromDate($currentYear, 11, 15),
    ]);

    // Report for August (bulan: 2026-08-01), created_at in December
    LaporanIzinTinggal::factory()->create([
        'bulan' => Carbon::createFromDate($currentYear, 8, 1)->toDateString(),
        'disetujui_l' => 8,
        'disetujui_p' => 4,
        'ditolak_l' => 0,
        'ditolak_p' => 2,
        'created_at' => Carbon::createFromDate($currentYear, 12, 20),
    ]);

    $petugas = User::factory()->user()->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->has('charts.tren_laporan_bulanan', 12)
        ->where('charts.total_laporan_tahun_ini', 2)
        // Month 4 (Apr): 1 report, 15 disetujui, 1 ditolak, 16 volume
        ->where('charts.tren_laporan_bulanan.3.bulan', 4)
        ->where('charts.tren_laporan_bulanan.3.label', 'Apr')
        ->where('charts.tren_laporan_bulanan.3.total', 1)
        ->where('charts.tren_laporan_bulanan.3.disetujui', 15)
        ->where('charts.tren_laporan_bulanan.3.ditolak', 1)
        ->where('charts.tren_laporan_bulanan.3.volume', 16)
        // Month 8 (Agu): 1 report, 12 disetujui, 2 ditolak, 14 volume
        ->where('charts.tren_laporan_bulanan.7.bulan', 8)
        ->where('charts.tren_laporan_bulanan.7.label', 'Agu')
        ->where('charts.tren_laporan_bulanan.7.total', 1)
        ->where('charts.tren_laporan_bulanan.7.disetujui', 12)
        ->where('charts.tren_laporan_bulanan.7.ditolak', 2)
        ->where('charts.tren_laporan_bulanan.7.volume', 14)
        // Month 11 (Nov) should be 0 because created_at is ignored
        ->where('charts.tren_laporan_bulanan.10.bulan', 11)
        ->where('charts.tren_laporan_bulanan.10.total', 0)
    );
});

test('dashboard hides admin metrics from regular petugas user', function () {
    $petugas = User::factory()->user()->create();

    // Create admin-related entities in the database
    Permohonan::factory()->count(3)->create();
    Penerbitan::factory()->count(2)->create();
    RealisasiPnbp::factory()->count(2)->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('stats.admin', null)
    );
});

test('CR-009: dashboard includes 12-month PNBP line series for authenticated admin', function () {
    $admin = User::factory()->admin()->create();
    $now = Carbon::now('Asia/Jakarta');
    $currentYear = (int) $now->year;

    // Create admin-related entities
    Permohonan::factory()->count(4)->create();
    Penerbitan::factory()->count(3)->create();

    RealisasiPnbp::factory()->create([
        'bulan' => Carbon::createFromDate($currentYear, 1, 1)->toDateString(),
        'target' => 10000000,
        'realisasi' => 5000000,
    ]);

    RealisasiPnbp::factory()->create([
        'bulan' => Carbon::createFromDate($currentYear, 2, 1)->toDateString(),
        'target' => 10000000,
        'realisasi' => 10000000,
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('stats.admin.total_permohonan', 4)
        ->where('stats.admin.total_penerbitan', 3)
        ->where('stats.admin.total_users', 1)
        ->where('stats.admin.pnbp.tahun', $currentYear)
        ->where('stats.admin.pnbp.total_target', 20000000)
        ->where('stats.admin.pnbp.total_realisasi', 15000000)
        ->where('stats.admin.pnbp.selisih', -5000000)
        ->where('stats.admin.pnbp.persentase', 75)
        ->has('stats.admin.pnbp.bulanan', 2)
        // CR-009: line series covering 12 months
        ->has('stats.admin.pnbp.line_series', 12)
        ->where('stats.admin.pnbp.line_series.0.bulan', 1)
        ->where('stats.admin.pnbp.line_series.0.label', 'Jan')
        ->where('stats.admin.pnbp.line_series.0.target', 10000000)
        ->where('stats.admin.pnbp.line_series.0.realisasi', 5000000)
        ->where('stats.admin.pnbp.line_series.0.has_data', true)
        ->where('stats.admin.pnbp.line_series.1.bulan', 2)
        ->where('stats.admin.pnbp.line_series.1.target', 10000000)
        ->where('stats.admin.pnbp.line_series.1.realisasi', 10000000)
        ->where('stats.admin.pnbp.line_series.1.has_data', true)
        ->where('stats.admin.pnbp.line_series.2.bulan', 3)
        ->where('stats.admin.pnbp.line_series.2.target', 0)
        ->where('stats.admin.pnbp.line_series.2.realisasi', 0)
        ->where('stats.admin.pnbp.line_series.2.has_data', false)
    );
});

test('dashboard handles completely empty database gracefully without errors', function () {
    $petugas = User::factory()->user()->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('stats.total_wna', 0)
        ->where('stats.total_abg', 0)
        ->where('stats.total_affidavit', 0)
        ->where('stats.early_warning.semua', 0)
        ->where('charts.distribusi_izin.0.persen', 0)
        ->where('charts.distribusi_izin.1.persen', 0)
        ->where('charts.distribusi_izin.2.persen', 0)
        ->where('charts.pie_izin_tinggal.semua.total', 0)
        ->where('charts.pie_izin_tinggal.ponorogo.total', 0)
        ->where('charts.pie_izin_tinggal.pacitan.total', 0)
        ->where('charts.pie_izin_tinggal.trenggalek.total', 0)
        ->where('charts.top_kewarganegaraan', [])
        ->has('charts.tren_wna_bulanan', 12)
        ->has('charts.tren_permohonan_bulanan', 12)
        ->where('charts.total_permohonan_tahun_ini', 0)
        ->has('charts.tren_laporan_bulanan', 12)
        ->where('charts.total_laporan_tahun_ini', 0)
    );
});
