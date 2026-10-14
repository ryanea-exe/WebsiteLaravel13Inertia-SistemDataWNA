<?php

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\Setting;
use App\Models\Wna;
use Carbon\CarbonInterface;

test('wna casts attributes correctly to enums and dates', function () {
    $wna = Wna::factory()->create([
        'jenis_kelamin' => Gender::LakiLaki,
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'masa_berlaku' => '2026-11-20',
    ]);

    expect($wna->jenis_kelamin)->toBe(Gender::LakiLaki)
        ->and($wna->jenis_kelamin->label())->toBe('Laki-laki')
        ->and($wna->jenis_izin_tinggal)->toBe(JenisIzinTinggal::ITAS)
        ->and($wna->jenis_izin_tinggal->label())->toBe('Izin Tinggal Terbatas (ITAS)')
        ->and($wna->masa_berlaku)->toBeInstanceOf(CarbonInterface::class)
        ->and($wna->masa_berlaku->format('Y-m-d'))->toBe('2026-11-20');
});

test('wna early warning scope filters permits expiring within three calendar months', function () {
    // Expired 5 days ago (should NOT be in early warning)
    $expired = Wna::factory()->expired()->create();

    // Expires in 10 days (SHOULD be in early warning)
    $urgent = Wna::factory()->urgentRed()->create();

    // Expires in 2 months (SHOULD be in early warning)
    $upcoming = Wna::factory()->urgentBlue()->create();

    // Expires in 5 months (should NOT be in early warning)
    $farFuture = Wna::factory()->create([
        'masa_berlaku' => now()->addMonths(5)->toDateString(),
    ]);

    $results = Wna::query()->earlyWarning()->pluck('id');

    expect($results)->toContain($urgent->id)
        ->and($results)->toContain($upcoming->id)
        ->and($results)->not->toContain($expired->id)
        ->and($results)->not->toContain($farFuture->id);
});

test('wna urgency accessors calculate correctly', function () {
    $red = Wna::factory()->create([
        'masa_berlaku' => now()->addDays(5)->toDateString(),
    ]);
    expect($red->urgensi)->toBe('merah')
        ->and($red->urgensi_label)->toContain('Sangat Mendesak');

    $yellow = Wna::factory()->create([
        'masa_berlaku' => now()->addDays(25)->toDateString(),
    ]);
    expect($yellow->urgensi)->toBe('kuning')
        ->and($yellow->urgensi_label)->toContain('Mendesak');

    $blue = Wna::factory()->create([
        'masa_berlaku' => now()->addDays(60)->toDateString(),
    ]);
    expect($blue->urgensi)->toBe('biru')
        ->and($blue->urgensi_label)->toContain('Perhatian');
});

test('laporan izin tinggal calculates totals accurately', function () {
    $report = LaporanIzinTinggal::factory()->create([
        'disetujui_l' => 20,
        'disetujui_p' => 15,
        'ditolak_l' => 2,
        'ditolak_p' => 3,
    ]);

    expect($report->total_disetujui)->toBe(35)
        ->and($report->total_ditolak)->toBe(5)
        ->and($report->total)->toBe(40);
});

test('realisasi pnbp normalizes bulan and calculates percentage', function () {
    $pnbp = RealisasiPnbp::create([
        'bulan' => '2026-08-23',
        'target' => 100000000.00,
        'realisasi' => 85000000.00,
    ]);

    expect($pnbp->bulan->format('Y-m-d'))->toBe('2026-08-01')
        ->and($pnbp->persentase)->toBe(85.0);

    // If target is zero, percentage should safely be 0.0 without division by zero error
    $pnbpZero = RealisasiPnbp::create([
        'bulan' => '2026-09-01',
        'target' => 0,
        'realisasi' => 50000,
    ]);
    expect($pnbpZero->persentase)->toBe(0.0);
});

test('setting model get and set helpers work correctly', function () {
    expect(Setting::get('non_existent', 'default_val'))->toBe('default_val');

    Setting::set('app_name', 'Sistem Data WNA');
    expect(Setting::get('app_name'))->toBe('Sistem Data WNA');

    // Update existing setting
    Setting::set('app_name', 'Sistem Informasi WNA');
    expect(Setting::get('app_name'))->toBe('Sistem Informasi WNA');
});

test('search scope filters records across multiple models', function () {
    Wna::factory()->create(['nama' => 'John Doe Special', 'no_paspor' => 'PAS123']);
    Wna::factory()->create(['nama' => 'Jane Smith', 'no_paspor' => 'PAS456']);

    expect(Wna::query()->search('Special')->count())->toBe(1)
        ->and(Wna::query()->search('PAS456')->count())->toBe(1)
        ->and(Wna::query()->search('NonExistent')->count())->toBe(0);

    Abg::factory()->create(['nama' => 'Child Wonder', 'no_paspor' => 'ABGPAS1']);
    expect(Abg::query()->search('Wonder')->count())->toBe(1);

    Affidavit::factory()->create(['nama' => 'Affidavit Subject', 'no_paspor' => 'AFFPAS1']);
    expect(Affidavit::query()->search('Subject')->count())->toBe(1);

    Permohonan::factory()->create(['nama' => 'Applicant One', 'layanan' => 'Perpanjangan ITAS']);
    expect(Permohonan::query()->search('Applicant')->count())->toBe(1);

    Penerbitan::factory()->create(['nama' => 'Issuer One', 'kabupaten' => 'Wonosobo']);
    expect(Penerbitan::query()->search('Wonosobo')->count())->toBe(1);
});
