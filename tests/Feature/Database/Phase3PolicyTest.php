<?php

use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wna;

test('admin has full authorization across all business modules', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $wna = Wna::factory()->create();
    $abg = Abg::factory()->create();
    $affidavit = Affidavit::factory()->create();
    $permohonan = Permohonan::factory()->create();
    $laporan = LaporanIzinTinggal::factory()->create();
    $penerbitan = Penerbitan::factory()->create();
    $pnbp = RealisasiPnbp::factory()->create();
    $setting = Setting::factory()->create();

    // Wna
    expect($admin->can('viewAny', Wna::class))->toBeTrue()
        ->and($admin->can('create', Wna::class))->toBeTrue()
        ->and($admin->can('update', $wna))->toBeTrue()
        ->and($admin->can('delete', $wna))->toBeTrue()
        ->and($admin->can('printList', Wna::class))->toBeTrue()
        ->and($admin->can('printCard', $wna))->toBeTrue()
        ->and($admin->can('viewEarlyWarning', Wna::class))->toBeTrue();

    // Abg & Affidavit
    expect($admin->can('create', Abg::class))->toBeTrue()
        ->and($admin->can('delete', $abg))->toBeTrue()
        ->and($admin->can('printList', Abg::class))->toBeTrue()
        ->and($admin->can('export', Abg::class))->toBeTrue()
        ->and($admin->can('create', Affidavit::class))->toBeTrue()
        ->and($admin->can('delete', $affidavit))->toBeTrue()
        ->and($admin->can('printList', Affidavit::class))->toBeTrue()
        ->and($admin->can('export', Affidavit::class))->toBeTrue();

    // Restricted modules
    expect($admin->can('viewAny', Permohonan::class))->toBeTrue()
        ->and($admin->can('create', Permohonan::class))->toBeTrue()
        ->and($admin->can('viewAny', LaporanIzinTinggal::class))->toBeTrue()
        ->and($admin->can('create', LaporanIzinTinggal::class))->toBeTrue()
        ->and($admin->can('viewAny', Penerbitan::class))->toBeTrue()
        ->and($admin->can('create', Penerbitan::class))->toBeTrue()
        ->and($admin->can('viewAny', RealisasiPnbp::class))->toBeTrue()
        ->and($admin->can('create', RealisasiPnbp::class))->toBeTrue()
        ->and($admin->can('viewAny', Setting::class))->toBeTrue()
        ->and($admin->can('update', $setting))->toBeTrue();
});

test('petugas has read-only access to wna, abg, and affidavit, and can print individual wna card', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $wna = Wna::factory()->create();
    $abg = Abg::factory()->create();
    $affidavit = Affidavit::factory()->create();

    // Wna permissions for Petugas
    expect($petugas->can('viewAny', Wna::class))->toBeTrue()
        ->and($petugas->can('view', $wna))->toBeTrue()
        ->and($petugas->can('printCard', $wna))->toBeTrue()
        ->and($petugas->can('create', Wna::class))->toBeFalse()
        ->and($petugas->can('update', $wna))->toBeFalse()
        ->and($petugas->can('delete', $wna))->toBeFalse()
        ->and($petugas->can('printList', Wna::class))->toBeFalse();

    // Abg permissions for Petugas
    expect($petugas->can('viewAny', Abg::class))->toBeTrue()
        ->and($petugas->can('view', $abg))->toBeTrue()
        ->and($petugas->can('create', Abg::class))->toBeFalse()
        ->and($petugas->can('update', $abg))->toBeFalse()
        ->and($petugas->can('delete', $abg))->toBeFalse()
        ->and($petugas->can('printList', Abg::class))->toBeFalse()
        ->and($petugas->can('export', Abg::class))->toBeFalse();

    // Affidavit permissions for Petugas
    expect($petugas->can('viewAny', Affidavit::class))->toBeTrue()
        ->and($petugas->can('view', $affidavit))->toBeTrue()
        ->and($petugas->can('create', Affidavit::class))->toBeFalse()
        ->and($petugas->can('update', $affidavit))->toBeFalse()
        ->and($petugas->can('delete', $affidavit))->toBeFalse()
        ->and($petugas->can('printList', Affidavit::class))->toBeFalse()
        ->and($petugas->can('export', Affidavit::class))->toBeFalse();
});

test('petugas has read-only access to layanan and rekap modules, but is forbidden from mutations and admin settings/early warning', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $permohonan = Permohonan::factory()->create();
    $laporan = LaporanIzinTinggal::factory()->create();
    $penerbitan = Penerbitan::factory()->create();
    $pnbp = RealisasiPnbp::factory()->create();
    $setting = Setting::factory()->create();

    // Permohonan (CR-010 & CR-011)
    expect($petugas->can('viewAny', Permohonan::class))->toBeTrue()
        ->and($petugas->can('view', $permohonan))->toBeTrue()
        ->and($petugas->can('create', Permohonan::class))->toBeFalse()
        ->and($petugas->can('update', $permohonan))->toBeFalse()
        ->and($petugas->can('delete', $permohonan))->toBeFalse()
        ->and($petugas->can('printList', Permohonan::class))->toBeTrue()
    // Laporan Izin Tinggal (CR-010 & CR-011)
        ->and($petugas->can('viewAny', LaporanIzinTinggal::class))->toBeTrue()
        ->and($petugas->can('view', $laporan))->toBeTrue()
        ->and($petugas->can('create', LaporanIzinTinggal::class))->toBeFalse()
        ->and($petugas->can('update', $laporan))->toBeFalse()
        ->and($petugas->can('delete', $laporan))->toBeFalse()
        ->and($petugas->can('printList', LaporanIzinTinggal::class))->toBeTrue()
    // Penerbitan (CR-010 & CR-011)
        ->and($petugas->can('viewAny', Penerbitan::class))->toBeTrue()
        ->and($petugas->can('view', $penerbitan))->toBeTrue()
        ->and($petugas->can('create', Penerbitan::class))->toBeFalse()
        ->and($petugas->can('update', $penerbitan))->toBeFalse()
        ->and($petugas->can('delete', $penerbitan))->toBeFalse()
        ->and($petugas->can('printList', Penerbitan::class))->toBeTrue()
    // Realisasi PNBP (CR-010 & CR-011)
        ->and($petugas->can('viewAny', RealisasiPnbp::class))->toBeTrue()
        ->and($petugas->can('view', $pnbp))->toBeTrue()
        ->and($petugas->can('create', RealisasiPnbp::class))->toBeFalse()
        ->and($petugas->can('update', $pnbp))->toBeFalse()
        ->and($petugas->can('delete', $pnbp))->toBeFalse()
        ->and($petugas->can('printList', RealisasiPnbp::class))->toBeTrue()
    // Setting & Early Warning (CR-012)
        ->and($petugas->can('viewAny', Setting::class))->toBeFalse()
        ->and($petugas->can('update', $setting))->toBeFalse()
        ->and($petugas->can('viewEarlyWarning', Wna::class))->toBeFalse();
});
