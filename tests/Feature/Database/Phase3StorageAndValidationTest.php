<?php

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Http\Requests\StoreLaporanIzinTinggalRequest;
use App\Http\Requests\StoreRealisasiPnbpRequest;
use App\Http\Requests\StoreWnaRequest;
use App\Models\RealisasiPnbp;
use App\Models\Wna;
use App\Services\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

test('document storage service stores photos on public disk and documents on private disk', function () {
    Storage::fake('public');
    Storage::fake('local');

    $service = new DocumentStorageService;

    // Store public photo
    $photo = UploadedFile::fake()->image('avatar.jpg', 400, 400)->size(500);
    $photoPath = $service->storePublicPhoto($photo, 'wna/photos');

    Storage::disk('public')->assertExists($photoPath);

    // Delete public photo
    expect($service->deletePublic($photoPath))->toBeTrue();
    Storage::disk('public')->assertMissing($photoPath);

    // Store private document
    $doc = UploadedFile::fake()->create('passport_copy.pdf', 1024, 'application/pdf');
    $docPath = $service->storePrivateDocument($doc, 'wna/documents');

    Storage::disk('local')->assertExists($docPath);

    // Delete private document
    expect($service->deletePrivate($docPath))->toBeTrue();
    Storage::disk('local')->assertMissing($docPath);
});

test('document storage service rejects files exceeding maximum size', function () {
    Storage::fake('public');

    $service = new DocumentStorageService;

    // 3MB photo exceeds 2MB limit
    $oversizedPhoto = UploadedFile::fake()->image('big_photo.jpg', 1000, 1000)->size(3072);

    expect(fn () => $service->storePublicPhoto($oversizedPhoto))
        ->toThrow(InvalidArgumentException::class, 'Ukuran foto melebihi batas maksimum');
});

test('document storage service rejects disallowed mime types and extensions', function () {
    Storage::fake('local');

    $service = new DocumentStorageService;

    $phpFile = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');
    expect(fn () => $service->storePrivateDocument($phpFile))
        ->toThrow(InvalidArgumentException::class, 'Tipe konten dokumen tidak valid');

    $svgFile = UploadedFile::fake()->create('raw.svg', 100, 'image/svg+xml');
    expect(fn () => $service->storePublicPhoto($svgFile))
        ->toThrow(InvalidArgumentException::class, 'Tipe konten foto tidak valid');
});

test('store wna request validates required fields, unique register, and enums', function () {
    Wna::factory()->create(['no_register' => 'REG-EXISTING']);

    $request = new StoreWnaRequest;

    // Duplicate register and invalid enum
    $validator = Validator::make([
        'no_register' => 'REG-EXISTING',
        'no_paspor' => 'PAS123',
        'nama' => 'Test Name',
        'jenis_kelamin' => 'X', // Invalid
        'kewarganegaraan' => 'Japan',
        'jenis_izin_tinggal' => 'INVALID_VISA', // Invalid
        'masa_berlaku' => 'invalid-date',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('no_register'))->toBeTrue()
        ->and($validator->errors()->has('jenis_kelamin'))->toBeTrue()
        ->and($validator->errors()->has('jenis_izin_tinggal'))->toBeTrue()
        ->and($validator->errors()->has('masa_berlaku'))->toBeTrue();

    // Valid payload
    $validValidator = Validator::make([
        'no_register' => 'REG-NEW-999',
        'no_paspor' => 'PAS123',
        'nama' => 'Valid Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Japan',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS->value,
        'masa_berlaku' => '2026-12-31',
        'kabupaten' => 'Ponorogo',
    ], $request->rules());

    expect($validValidator->passes())->toBeTrue();
});

test('store laporan izin tinggal request rejects negative integers', function () {
    $request = new StoreLaporanIzinTinggalRequest;

    $validator = Validator::make([
        'kategori' => 'Kategori A',
        'layanan' => 'Layanan B',
        'disetujui_l' => -5,
        'disetujui_p' => 10,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('disetujui_l'))->toBeTrue();
});

test('store realisasi pnbp request enforces unique bulan', function () {
    RealisasiPnbp::create([
        'bulan' => '2026-06-01',
        'target' => 1000,
        'realisasi' => 800,
    ]);

    $request = new StoreRealisasiPnbpRequest;

    $validator = Validator::make([
        'bulan' => '2026-06-01',
        'target' => 2000,
        'realisasi' => 1500,
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('bulan'))->toBeTrue();
});
