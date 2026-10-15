<?php

use App\Enums\JenisIzinTinggal;
use App\Models\Abg;
use App\Models\Affidavit;
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

    // Seed WNA records with known characteristics
    // 1 expired
    Wna::factory()->create([
        'nama' => 'John Expired',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'kewarganegaraan' => 'Japan',
        'masa_berlaku' => $now->copy()->subDays(5)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 2, 10),
    ]);

    // 1 urgent red (10 days remaining)
    Wna::factory()->create([
        'nama' => 'Ali Red',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAP,
        'kewarganegaraan' => 'Malaysia',
        'masa_berlaku' => $now->copy()->addDays(10)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 2, 15),
    ]);

    // 1 urgent blue (40 days remaining)
    Wna::factory()->create([
        'nama' => 'Siti Blue',
        'jenis_izin_tinggal' => JenisIzinTinggal::VOA,
        'kewarganegaraan' => 'Malaysia',
        'masa_berlaku' => $now->copy()->addDays(40)->toDateString(),
        'created_at' => Carbon::createFromDate($currentYear, 5, 20),
    ]);

    // Seed ABG and Affidavit
    Abg::factory()->count(2)->create();
    Affidavit::factory()->count(1)->create();

    $petugas = User::factory()->user()->create();

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('stats.total_wna', 3)
        ->where('stats.total_abg', 2)
        ->where('stats.total_affidavit', 1)
        ->where('stats.early_warning.semua', 2)
        ->where('stats.early_warning.kedaluwarsa', 1)
        ->where('stats.early_warning.merah', 1)
        ->where('stats.early_warning.kuning', 0)
        ->where('stats.early_warning.biru', 1)
        ->where('stats.admin', null)
        ->where('charts.tahun_berjalan', $currentYear)
        ->where('charts.distribusi_izin.0.jenis', 'ITAS')
        ->where('charts.distribusi_izin.0.total', 1)
        ->where('charts.distribusi_izin.1.jenis', 'ITAP')
        ->where('charts.distribusi_izin.1.total', 1)
        ->where('charts.distribusi_izin.2.jenis', 'VOA')
        ->where('charts.distribusi_izin.2.total', 1)
        ->has('charts.top_kewarganegaraan', 2)
        ->where('charts.top_kewarganegaraan.0.negara', 'Malaysia')
        ->where('charts.top_kewarganegaraan.0.total', 2)
        ->where('charts.top_kewarganegaraan.1.negara', 'Japan')
        ->where('charts.top_kewarganegaraan.1.total', 1)
        ->has('charts.tren_wna_bulanan', 12)
        ->where('charts.tren_wna_bulanan.1.bulan', 2) // Feb
        ->where('charts.tren_wna_bulanan.1.total', 2)
        ->where('charts.tren_wna_bulanan.4.bulan', 5) // May
        ->where('charts.tren_wna_bulanan.4.total', 1)
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

test('dashboard includes admin metrics for authenticated admin', function () {
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
        ->where('charts.top_kewarganegaraan', [])
        ->has('charts.tren_wna_bulanan', 12)
    );
});
