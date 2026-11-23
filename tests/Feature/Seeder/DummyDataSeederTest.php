<?php

use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\Wna;
use Database\Seeders\DummyDataSeeder;

test('dummy data seeder populates all tables within reasonable limits under 100 records', function () {
    $seeder = new DummyDataSeeder;
    $seeder->run();

    // 1. Data WNA
    $wnaCount = Wna::count();
    expect($wnaCount)->toBeGreaterThanOrEqual(10)
        ->toBeLessThanOrEqual(100);

    // 2. Pendaftaran ABG
    $abgCount = Abg::count();
    expect($abgCount)->toBeGreaterThanOrEqual(10)
        ->toBeLessThanOrEqual(100);

    // 3. Pendaftaran Affidavit
    $affidavitCount = Affidavit::count();
    expect($affidavitCount)->toBeGreaterThanOrEqual(10)
        ->toBeLessThanOrEqual(100);

    // 4. Permohonan
    $permohonanCount = Permohonan::count();
    expect($permohonanCount)->toBeGreaterThanOrEqual(10)
        ->toBeLessThanOrEqual(100);

    // 5. Laporan Izin Tinggal
    $laporanCount = LaporanIzinTinggal::count();
    expect($laporanCount)->toBeGreaterThanOrEqual(5)
        ->toBeLessThanOrEqual(100);

    // 6. Penerbitan
    $penerbitanCount = Penerbitan::count();
    expect($penerbitanCount)->toBeGreaterThanOrEqual(10)
        ->toBeLessThanOrEqual(100);

    // 7. Realisasi PNBP
    $pnbpCount = RealisasiPnbp::count();
    expect($pnbpCount)->toBeGreaterThanOrEqual(12)
        ->toBeLessThanOrEqual(100);
});
