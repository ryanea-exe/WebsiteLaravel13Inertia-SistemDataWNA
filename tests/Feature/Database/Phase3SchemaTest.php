<?php

use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
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
        'kabupaten',
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
        'id', 'no_register', 'nama', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'kewarganegaraan', 'nama_ayah', 'kewarganegaraan_ayah',
        'nama_ibu', 'kewarganegaraan_ibu', 'nomor_telepon', 'alamat', 'foto',
        'created_at', 'updated_at',
    ];

    foreach ($abgColumns as $col) {
        expect(Schema::hasColumn('abg', $col))->toBeTrue("Column {$col} missing from abg table.");
        expect(Schema::hasColumn('affidavit', $col))->toBeTrue("Column {$col} missing from affidavit table.");
    }

    expect(Schema::hasColumn('abg', 'no_paspor'))->toBeFalse('Column no_paspor should be dropped from abg table.');
    expect(Schema::hasColumn('affidavit', 'no_paspor'))->toBeTrue('Column no_paspor must remain in affidavit table.');
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

test('laporan_izin_tinggal table has required columns', function () {
    $columns = [
        'id',
        'bulan',
        'kategori',
        'layanan',
        'disetujui_l',
        'disetujui_p',
        'ditolak_l',
        'ditolak_p',
        'keterangan',
        'created_at',
        'updated_at',
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('laporan_izin_tinggal', $column))->toBeTrue("Column {$column} missing from laporan_izin_tinggal table.");
    }
});

test('laporan_izin_tinggal enforces uniqueness and not-null on monthly date (CR-008)', function () {
    // Null bulan is rejected by database constraint
    expect(fn () => LaporanIzinTinggal::create([
        'bulan' => null,
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 1,
        'disetujui_p' => 1,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]))->toThrow(QueryException::class);

    // Unique non-null month
    $r1 = LaporanIzinTinggal::create([
        'bulan' => '2026-10-01',
        'kategori' => 'Izin Tinggal Tetap',
        'layanan' => 'Alih Status',
        'disetujui_l' => 5,
        'disetujui_p' => 5,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]);
    expect($r1->bulan->format('Y-m-d'))->toBe('2026-10-01');

    // Duplicate non-null month throws QueryException
    expect(fn () => LaporanIzinTinggal::create([
        'bulan' => '2026-10-01',
        'kategori' => 'Izin Tinggal Tetap',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 1,
        'disetujui_p' => 1,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]))->toThrow(QueryException::class);
});

test('wna table enforces not-null on kabupaten column (CR-002)', function () {
    expect(fn () => Wna::create([
        'no_register' => 'REG-TEST-NULL-KAB',
        'no_paspor' => 'PAS123',
        'nama' => 'Test Person',
        'jenis_kelamin' => 'L',
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => 'ITAS',
        'izin_tinggal_ke' => '1',
        'masa_berlaku' => '2027-01-01',
        'kabupaten' => null,
    ]))->toThrow(QueryException::class);
});

test('batch 2 and CR-008 migrations down and up methods reverse and reapply cleanly in isolated database', function () {
    $migration16 = require database_path('migrations/2026_10_23_000016_make_kabupaten_and_bulan_not_null.php');
    $migration15 = require database_path('migrations/2026_10_23_000015_add_bulan_to_laporan_izin_tinggal_table.php');
    $migration14 = require database_path('migrations/2026_10_22_000014_drop_no_paspor_from_abg_table.php');
    $migration13 = require database_path('migrations/2026_10_22_000013_add_kabupaten_to_wna_table.php');
    $migration12 = require database_path('migrations/2026_10_22_000012_modify_jenis_izin_tinggal_on_wna_table.php');

    // Test rollback (down)
    $migration16->down();
    $migration15->down();
    expect(Schema::hasColumn('laporan_izin_tinggal', 'bulan'))->toBeFalse('Rolling down should drop bulan column from laporan_izin_tinggal table');

    $migration14->down();
    expect(Schema::hasColumn('abg', 'no_paspor'))->toBeTrue('Rolling down should restore no_paspor column to abg table');

    $migration13->down();
    expect(Schema::hasColumn('wna', 'kabupaten'))->toBeFalse('Rolling down should drop kabupaten column from wna table');

    $migration12->down();

    // Reapply (up)
    $migration12->up();
    $migration13->up();
    expect(Schema::hasColumn('wna', 'kabupaten'))->toBeTrue('Reapplying up should add kabupaten column to wna table');

    $migration14->up();
    expect(Schema::hasColumn('abg', 'no_paspor'))->toBeFalse('Reapplying up should drop no_paspor column from abg table');

    $migration15->up();
    expect(Schema::hasColumn('laporan_izin_tinggal', 'bulan'))->toBeTrue('Reapplying up should add bulan column to laporan_izin_tinggal table');

    $migration16->up();
    expect(Schema::hasColumn('wna', 'kabupaten'))->toBeTrue('Reapplying up migration 16 should maintain kabupaten column');
    expect(Schema::hasColumn('laporan_izin_tinggal', 'bulan'))->toBeTrue('Reapplying up migration 16 should maintain bulan column');
});
