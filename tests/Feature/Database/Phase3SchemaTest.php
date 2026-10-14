<?php

use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\RealisasiPnbp;
use App\Models\Setting;
use App\Models\Wna;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

test('all phase 3 tables exist in the schema', function () {
    $expectedTables = [
        'wna',
        'abg',
        'affidavit',
        'permohonan',
        'laporan_izin_tinggal',
        'penerbitan',
        'realisasi_pnbp',
        'settings',
    ];

    foreach ($expectedTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Table {$table} does not exist in schema.");
    }
});

test('wna table has required columns', function () {
    $columns = [
        'id',
        'no_register',
        'no_paspor',
        'nama',
        'jenis_kelamin',
        'kewarganegaraan',
        'jenis_izin_tinggal',
        'izin_tinggal_ke',
        'masa_berlaku',
        'penjamin',
        'alamat_penjamin',
        'nomor_telepon',
        'foto',
        'created_at',
        'updated_at',
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('wna', $column))->toBeTrue("Column {$column} missing from wna table.");
    }
});

test('abg and affidavit tables have required columns', function () {
    $abgColumns = [
        'id', 'no_register', 'no_paspor', 'nama', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'kewarganegaraan', 'nama_ayah', 'kewarganegaraan_ayah',
        'nama_ibu', 'kewarganegaraan_ibu', 'nomor_telepon', 'alamat', 'foto',
        'created_at', 'updated_at',
    ];

    foreach ($abgColumns as $col) {
        expect(Schema::hasColumn('abg', $col))->toBeTrue("Column {$col} missing from abg table.");
        expect(Schema::hasColumn('affidavit', $col))->toBeTrue("Column {$col} missing from affidavit table.");
    }

    expect(Schema::hasColumn('affidavit', 'masa_berlaku'))->toBeTrue('Column masa_berlaku missing from affidavit table.');
});

test('no_register enforces uniqueness separately per table', function () {
    Wna::factory()->create(['no_register' => 'REG-1001']);

    expect(fn () => Wna::factory()->create(['no_register' => 'REG-1001']))
        ->toThrow(QueryException::class);

    // Should succeed in ABG because uniqueness is separate per table
    $abg = Abg::factory()->create(['no_register' => 'REG-1001']);
    expect($abg->no_register)->toBe('REG-1001');

    // Duplicate in ABG should fail
    expect(fn () => Abg::factory()->create(['no_register' => 'REG-1001']))
        ->toThrow(QueryException::class);

    // Should succeed in Affidavit
    $affidavit = Affidavit::factory()->create(['no_register' => 'REG-1001']);
    expect($affidavit->no_register)->toBe('REG-1001');

    // Duplicate in Affidavit should fail
    expect(fn () => Affidavit::factory()->create(['no_register' => 'REG-1001']))
        ->toThrow(QueryException::class);
});

test('realisasi_pnbp enforces uniqueness on normalized monthly date', function () {
    RealisasiPnbp::factory()->create(['bulan' => '2026-05-01']);

    expect(fn () => RealisasiPnbp::factory()->create(['bulan' => '2026-05-15']))
        ->toThrow(QueryException::class);
});

test('settings table enforces uniqueness on key', function () {
    Setting::create(['key' => 'app_name', 'value' => 'Sistem Data WNA']);

    expect(fn () => Setting::create(['key' => 'app_name', 'value' => 'Another Name']))
        ->toThrow(QueryException::class);
});
