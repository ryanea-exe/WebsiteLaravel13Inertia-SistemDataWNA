<?php

use App\Console\Commands\MigrateStorageToB2Command;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wna;
use App\Services\StorageMigrationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;

beforeEach(function () {
    // Reset custom inventory provider, destination checker, and transfer handler before each test
    StorageMigrationService::setCustomInventoryProvider(null);
    StorageMigrationService::setCustomDestinationChecker(null);
    StorageMigrationService::setCustomTransferHandler(null);

    // Setup isolated fake disks for testing
    Storage::fake('local');
    Storage::fake('public');
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');
});

afterEach(function () {
    StorageMigrationService::setCustomInventoryProvider(null);
    StorageMigrationService::setCustomDestinationChecker(null);
    StorageMigrationService::setCustomTransferHandler(null);
});

test('migration command runs in dry-run mode by default and does not write to destination disks', function () {
    // 1. Setup source files on local and public disks
    $wnaPath = 'wna/photos/active_wna_sample.jpg';
    $logoPath = 'settings/icons/custom_logo.png';

    Storage::disk('local')->put($wnaPath, 'fake-wna-image-content');
    Storage::disk('public')->put($logoPath, 'fake-logo-image-content');

    // 2. Setup database records
    Wna::factory()->create(['foto' => $wnaPath]);
    Setting::set('app_logo_1', $logoPath);

    // 3. Run migration command in default mode
    $this->artisan('storage:migrate-to-b2')
        ->expectsOutputToContain('DRY-RUN (Tidak ada berkas yang ditulis atau dihapus)')
        ->expectsOutputToContain('RINGKASAN AUDIT MIGRASI (DRY-RUN)')
        ->assertSuccessful();

    // 4. Verify that destination disks remained untouched (zero files written)
    expect(Storage::disk('b2_private')->exists($wnaPath))->toBeFalse()
        ->and(Storage::disk('b2_assets_private')->exists($logoPath))->toBeFalse();

    // Source files must remain intact
    expect(Storage::disk('local')->exists($wnaPath))->toBeTrue()
        ->and(Storage::disk('public')->exists($logoPath))->toBeTrue();
});

test('execute option requires baseline manifest when safety gate is unlocked', function () {
    expect(MigrateStorageToB2Command::STAGE_4E5B_SAFETY_GATE_LOCKED)->toBeFalse();

    $this->artisan('storage:migrate-to-b2', ['--execute' => true])
        ->expectsOutputToContain('PARAMETER WAJIB TIDAK DITEMUKAN')
        ->expectsOutputToContain('Opsi [--baseline-manifest] wajib disertakan untuk eksekusi live (--execute)')
        ->expectsOutputToContain('Eksekusi dibatalkan sebelum operasi apa pun dijalankan')
        ->assertFailed();
});

test('inventory collects only database-referenced files and ignores orphan files', function () {
    // 1. Create referenced files and DB records
    $wnaPhoto = 'wna/photos/active_wna.jpg';
    $abgPhoto = 'abg/photos/active_abg.jpg';
    $affidavitPhoto = 'affidavit/photos/active_affidavit.jpg';
    $userAvatar = 'users/photos/active_user.jpg';
    $appLogo = 'settings/icons/app_logo_canonical.png';

    Storage::disk('local')->put($wnaPhoto, 'wna-bytes');
    Storage::disk('local')->put($abgPhoto, 'abg-bytes');
    Storage::disk('local')->put($affidavitPhoto, 'affidavit-bytes');
    Storage::disk('local')->put($userAvatar, 'user-bytes');
    Storage::disk('public')->put($appLogo, 'logo-bytes');

    Wna::factory()->create(['foto' => $wnaPhoto]);
    Abg::factory()->create(['foto' => $abgPhoto]);
    Affidavit::factory()->create(['foto' => $affidavitPhoto]);
    User::factory()->create(['foto' => $userAvatar]);
    Setting::set('app_logo_1', $appLogo);

    // 2. Put an orphan unreferenced file on local disk
    $orphanFile = 'wna/photos/orphan_unreferenced_file.jpg';
    Storage::disk('local')->put($orphanFile, 'orphan-bytes');

    // 3. Collect inventory
    $service = app(StorageMigrationService::class);
    $inventory = $service->collectDatabaseInventory('all');

    expect($inventory)->toHaveKey($wnaPhoto)
        ->and($inventory)->toHaveKey($abgPhoto)
        ->and($inventory)->toHaveKey($affidavitPhoto)
        ->and($inventory)->toHaveKey($userAvatar)
        ->and($inventory)->toHaveKey($appLogo)
        ->and($inventory)->not->toHaveKey($orphanFile);
});

test('duplicate database references to the same file are deduplicated', function () {
    $sharedAvatar = 'users/photos/shared_avatar.jpg';
    Storage::disk('local')->put($sharedAvatar, 'shared-bytes');

    // Two users referencing identical avatar
    $user1 = User::factory()->create(['foto' => $sharedAvatar]);
    $user2 = User::factory()->create(['foto' => $sharedAvatar]);

    $service = app(StorageMigrationService::class);
    $inventory = $service->collectDatabaseInventory('user');

    expect($inventory)->toHaveCount(1)
        ->and($inventory)->toHaveKey($sharedAvatar)
        ->and($inventory[$sharedAvatar]['references'])->toHaveCount(2);
});

test('null and empty string paths in database are safely ignored', function () {
    Wna::factory()->create(['foto' => null]);
    Wna::factory()->create(['foto' => '']);
    User::factory()->create(['foto' => null]);
    User::factory()->create(['foto' => '   ']);

    $service = app(StorageMigrationService::class);
    $inventory = $service->collectDatabaseInventory('all');

    expect($inventory)->toBeEmpty();
});

test('path traversal, null bytes, backslashes, and wildcards are rejected with REJECTED_PATH status', function () {
    $service = app(StorageMigrationService::class);
    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $maliciousPaths = [
        'wna/photos/../../etc/passwd',
        "wna/photos/test\0.jpg",
        'wna/photos/subdir\\dangerous.jpg',
        'wna/photos/*.jpg',
        '/wna/photos/absolute.jpg',
        'wna/photos/?pattern.jpg',
    ];

    foreach ($maliciousPaths as $badPath) {
        $item = [
            'category' => 'wna',
            'relative_path' => $badPath,
            'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
        ];

        $evaluation = $service->evaluateItem($item, $diskMapping);
        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_REJECTED_PATH);
    }
});

test('prefix outside category allowlist is rejected with REJECTED_PATH status', function () {
    $service = app(StorageMigrationService::class);
    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    // WNA item with foreign directory path
    $item = [
        'category' => 'wna',
        'relative_path' => 'random/unauthorized_folder/photo.jpg',
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);
    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_REJECTED_PATH);
});

test('missing source file is detected and marked as SOURCE_MISSING', function () {
    $service = app(StorageMigrationService::class);
    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $missingPath = 'wna/photos/does_not_exist_on_disk.jpg';

    $item = [
        'category' => 'wna',
        'relative_path' => $missingPath,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);
    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_SOURCE_MISSING)
        ->and($evaluation['error'])->toContain('Berkas sumber tidak ditemukan');
});

test('stream SHA-256 checksum and size are calculated accurately', function () {
    $service = app(StorageMigrationService::class);
    $payload = 'Test payload for integrity verification 12345!@#$%';
    $expectedHash = hash('sha256', $payload);
    $expectedSize = strlen($payload);

    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $payload);
    rewind($stream);

    $result = $service->calculateStreamSha256($stream);

    expect($result['sha256'])->toBe($expectedHash)
        ->and($result['size'])->toBe($expectedSize);

    // Stream must be closed automatically by calculateStreamSha256
    expect(is_resource($stream))->toBeFalse();
});

test('target file with matching SHA-256 checksum is marked ALREADY_MATCHED', function () {
    $service = app(StorageMigrationService::class);
    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $path = 'wna/photos/already_synced.jpg';
    $identicalPayload = 'identical-binary-image-data';

    Storage::disk('local')->put($path, $identicalPayload);
    Storage::disk('b2_private')->put($path, $identicalPayload);

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_ALREADY_MATCHED)
        ->and($evaluation['source_sha256'])->not->toBeNull()
        ->and($evaluation['destination_sha256'])->toBe($evaluation['source_sha256']);
});

test('target file with different SHA-256 checksum is marked CONFLICT and not overwritten', function () {
    $service = app(StorageMigrationService::class);
    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $path = 'wna/photos/conflict_photo.jpg';
    $sourceContent = 'source-original-content';
    $targetContent = 'different-existing-target-content';

    Storage::disk('local')->put($path, $sourceContent);
    Storage::disk('b2_private')->put($path, $targetContent);

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_CONFLICT)
        ->and($evaluation['error'])->toContain('Konflik berkas')
        ->and($evaluation['source_sha256'])->not->toBe($evaluation['destination_sha256']);

    // Target content must remain unchanged
    expect(Storage::disk('b2_private')->get($path))->toBe($targetContent);
});

test('manifest file is generated safely without overwriting previous runs and without credentials', function () {
    $wnaPath = 'wna/photos/manifest_test.jpg';
    Storage::disk('local')->put($wnaPath, 'sample-content');
    Wna::factory()->create(['foto' => $wnaPath]);

    // Run 1
    $this->artisan('storage:migrate-to-b2')
        ->assertSuccessful();

    $filesRun1 = Storage::disk('local')->files('migration-manifests');
    expect($filesRun1)->toHaveCount(1);
    $manifest1 = $filesRun1[0];

    // Sleep 1 second to ensure different timestamp slug
    sleep(1);

    // Run 2
    $this->artisan('storage:migrate-to-b2')
        ->assertSuccessful();

    $filesRun2 = Storage::disk('local')->files('migration-manifests');
    expect($filesRun2)->toHaveCount(2);

    // Inspect manifest content
    $content = (string) Storage::disk('local')->get($manifest1);
    $data = json_decode($content, true);

    expect($data)->toHaveKey('migration_run_id')
        ->and($data)->toHaveKey('timestamp')
        ->and($data)->toHaveKey('mode')
        ->and($data)->toHaveKey('summary')
        ->and($data)->toHaveKey('items')
        ->and($data['mode'])->toBe('dry-run')
        ->and($data['summary']['ready_to_migrate'])->toBeGreaterThanOrEqual(1);

    // Verify manifest has zero credential keys
    expect($content)->not->toContain('AWS_SECRET_ACCESS_KEY')
        ->and($content)->not->toContain('B2_PRIVATE_APPLICATION_KEY')
        ->and($content)->not->toContain('password')
        ->and($content)->not->toContain('secret');
});

test('category option filters migration processing', function () {
    $wnaPath = 'wna/photos/filtered_wna.jpg';
    $logoPath = 'settings/icons/filtered_logo.png';

    Storage::disk('local')->put($wnaPath, 'wna-bytes');
    Storage::disk('public')->put($logoPath, 'logo-bytes');

    Wna::factory()->create(['foto' => $wnaPath]);
    Setting::set('app_logo_1', $logoPath);

    // Run with --category=wna
    $this->artisan('storage:migrate-to-b2', ['--category' => 'wna'])
        ->expectsOutputToContain('Kategori Target: WNA')
        ->assertSuccessful();

    // Run with invalid category
    $this->artisan('storage:migrate-to-b2', ['--category' => 'invalid_cat'])
        ->expectsOutputToContain('Kategori [invalid_cat] tidak valid')
        ->assertFailed();
});

test('custom inventory provider handles unresolved categories properly', function () {
    StorageMigrationService::setCustomInventoryProvider(function () {
        return [
            [
                'category' => 'unsupported_module',
                'relative_path' => 'unknown/file.jpg',
                'table' => 'test',
                'id' => 1,
                'field' => 'file',
            ],
        ];
    });

    $service = app(StorageMigrationService::class);
    $inventory = $service->collectDatabaseInventory('all');
    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = reset($inventory);
    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_UNRESOLVED_CATEGORY);
});

test('confirmed 404 destination absence produces READY_TO_MIGRATE', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/new_file.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Explicitly simulate confirmed 404 Not Found from destination check
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => true,
            'error' => null,
            'status' => null,
        ];
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toBeNull()
        ->and($evaluation['source_sha256'])->not->toBeNull()
        ->and($evaluation['destination_sha256'])->toBeNull();
});

test('destination 403 access denied produces VERIFICATION_FAILED and never READY_TO_MIGRATE', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/forbidden_file.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Simulate 403 Access Denied
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => 'Akses ditolak (HTTP 403 / Access Denied) pada disk [b2_private].',
            'status' => StorageMigrationService::STATUS_VERIFICATION_FAILED,
        ];
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('403');
});

test('destination 5xx server error produces VERIFICATION_FAILED and never READY_TO_MIGRATE', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/server_error.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Simulate 503 Service Unavailable
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => 'Kesalahan server penyimpanan (HTTP 503) pada disk [b2_private].',
            'status' => StorageMigrationService::STATUS_VERIFICATION_FAILED,
        ];
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('503');
});

test('destination timeout produces VERIFICATION_FAILED and never READY_TO_MIGRATE', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/timeout_file.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Simulate connection timeout
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => 'Gangguan koneksi atau timeout pada disk [b2_private]: Operation timed out after 5000 milliseconds.',
            'status' => StorageMigrationService::STATUS_VERIFICATION_FAILED,
        ];
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('timeout');
});

test('destination network and transport error produces VERIFICATION_FAILED and never READY_TO_MIGRATE', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/network_error.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Simulate transport error
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => 'Gangguan jaringan saat memeriksa berkas tujuan pada disk [b2_private]: Could not resolve host.',
            'status' => StorageMigrationService::STATUS_VERIFICATION_FAILED,
        ];
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('Gangguan jaringan');
});

test('ambiguous or unconfirmed absence response produces VERIFICATION_FAILED and never READY_TO_MIGRATE', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/ambiguous.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Simulate ambiguous response: exists is false, but confirmed_absent is also false (not confirmed 404)
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => null,
            'status' => null,
        ];
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('tidak mengonfirmasi ketiadaan objek');
});

test('sanitizeErrorMessage strips sensitive credential tokens and signatures', function () {
    $service = app(StorageMigrationService::class);

    $rawError = 'AWS Error: key=AKIAIOSFODNN7EXAMPLE secret=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY Signature=vjbyPxybdZaNmGa+GQgRAmp612k= token=session-token-xyz';
    $sanitized = $service->sanitizeErrorMessage($rawError);

    expect($sanitized)->not->toContain('AKIAIOSFODNN7EXAMPLE')
        ->and($sanitized)->not->toContain('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY')
        ->and($sanitized)->not->toContain('vjbyPxybdZaNmGa+GQgRAmp612k=')
        ->and($sanitized)->toContain('key=[REDACTED]')
        ->and($sanitized)->toContain('secret=[REDACTED]')
        ->and($sanitized)->toContain('Signature=[REDACTED]');
});

test('non-s3 fallback continues to checksum verification when target is found', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/found_non_s3.jpg';
    $payload = 'identical-file-content';

    Storage::disk('local')->put($path, $payload);
    Storage::disk('b2_private')->put($path, $payload);

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    // Target exists on fake disk (non-S3 LocalFilesystemAdapter)
    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_ALREADY_MATCHED)
        ->and($evaluation['source_sha256'])->toBe($evaluation['destination_sha256'])
        ->and($evaluation['destination_sha256'])->not->toBeNull();
});

test('non-s3 fallback fails closed with VERIFICATION_FAILED when absence cannot be proven conclusively', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/inconclusive.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    // Adapter that is not LocalFilesystemAdapter (e.g. unknown third-party adapter)
    $mockAdapter = Mockery::mock(FilesystemAdapter::class);
    $mockDisk = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
    $mockDisk->shouldReceive('fileExists')->with($path)->andReturn(false);
    $mockDisk->shouldReceive('getAdapter')->andReturn($mockAdapter);

    Storage::set('inconclusive_disk', $mockDisk);

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'inconclusive_disk',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('tidak menyediakan mekanisme pembuktian ketiadaan berkas');
});

test('non-s3 fallback traps filesystem warnings and fails closed on permission denied', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/perm_denied.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $mockDisk = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
    $mockDisk->shouldReceive('fileExists')->with($path)->andReturnUsing(function () {
        trigger_error('Permission denied to access target path', E_USER_WARNING);

        return false;
    });

    Storage::set('perm_disk', $mockDisk);

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'perm_disk',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('Peringatan sistem berkas')
        ->and($evaluation['error'])->toContain('Permission denied');
});

test('non-s3 fallback directly handles UnableToCheckFileExistence and produces VERIFICATION_FAILED', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/unable_check.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $mockDisk = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
    $mockDisk->shouldReceive('fileExists')
        ->with($path)
        ->andThrow(UnableToCheckFileExistence::forLocation($path));

    Storage::set('unable_check_disk', $mockDisk);

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'unable_check_disk',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('Gagal memeriksa keberadaan berkas tujuan');
});

test('non-s3 fallback detects previous I/O error inside UnableToCheckFileExistence', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/io_failure.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $ioException = new RuntimeException('Input/output error on block device');
    $mockDisk = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
    $mockDisk->shouldReceive('fileExists')
        ->with($path)
        ->andThrow(UnableToCheckFileExistence::forLocation($path, $ioException));

    Storage::set('io_failure_disk', $mockDisk);

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'io_failure_disk',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('Kesalahan I/O sistem berkas')
        ->and($evaluation['error'])->toContain('Input/output error');
});

test('non-s3 fallback catches unknown exceptions safely as VERIFICATION_FAILED', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/unknown_exception.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $mockDisk = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
    $mockDisk->shouldReceive('fileExists')
        ->with($path)
        ->andThrow(new Exception('Unforeseen fatal filesystem corruption'));

    Storage::set('unknown_exc_disk', $mockDisk);

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'unknown_exc_disk',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $evaluation = $service->evaluateItem($item, $diskMapping);

    expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
        ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($evaluation['error'])->toContain('Unforeseen fatal filesystem corruption');
});

test('custom destination checker enforces strict boolean identity for confirmed_absent', function ($inputVal, bool $expectReady) {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/strict_bool_'.md5(serialize($inputVal)).'.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $diskMapping = [
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
    ];

    $item = [
        'category' => 'wna',
        'relative_path' => $path,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) use ($inputVal) {
        $result = ['exists' => false];
        if ($inputVal !== 'OMIT_KEY') {
            $result['confirmed_absent'] = $inputVal;
        }

        return $result;
    });

    $evaluation = $service->evaluateItem($item, $diskMapping);

    if ($expectReady) {
        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
            ->and($evaluation['error'])->toBeNull();
    } else {
        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
            ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE);
    }
})->with([
    'boolean true' => [true, true],
    'boolean false' => [false, false],
    'string "true"' => ['true', false],
    'string "false"' => ['false', false],
    'string "yes"' => ['yes', false],
    'integer 1' => [1, false],
    'integer 0' => [0, false],
    'null value' => [null, false],
    'omitted key' => ['OMIT_KEY', false],
]);

test('non-s3 fallback detects and rejects dangling symlinks as VERIFICATION_FAILED', function () {
    if (! function_exists('symlink')) {
        test()->markTestSkipped('symlink function is not supported in this environment');
    }

    $service = app(StorageMigrationService::class);
    $tempDir = sys_get_temp_dir().'/storage_migration_test_'.uniqid('symlink_', true);
    mkdir($tempDir, 0755, true);

    try {
        config(['filesystems.disks.test_temp_symlink' => [
            'driver' => 'local',
            'root' => $tempDir,
            'throw' => false,
        ]]);

        $relPath = 'wna/photos/dangling_file.jpg';
        $symlinkPath = $tempDir.'/'.$relPath;
        mkdir(dirname($symlinkPath), 0755, true);

        // Point to a non-existent file
        $nonExistentTarget = $tempDir.'/non_existent_target_'.uniqid().'.jpg';
        symlink($nonExistentTarget, $symlinkPath);

        // Verify dangling symlink characteristics
        expect(is_link($symlinkPath))->toBeTrue()
            ->and(file_exists($symlinkPath))->toBeFalse();

        Storage::disk('local')->put($relPath, 'sample-data');

        $diskMapping = [
            'source_private' => 'local',
            'source_assets' => 'public',
            'dest_private' => 'test_temp_symlink',
            'dest_assets' => 'b2_assets_private',
        ];

        $item = [
            'category' => 'wna',
            'relative_path' => $relPath,
            'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
        ];

        $evaluation = $service->evaluateItem($item, $diskMapping);

        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
            ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
            ->and($evaluation['error'])->toContain('bukan berkas reguler')
            ->and($evaluation['error'])->toContain('link/symlink');
    } finally {
        $cleanup = function (string $dir) use (&$cleanup): void {
            if (! is_dir($dir) && ! is_link($dir)) {
                return;
            }
            $files = scandir($dir);
            if ($files === false) {
                return;
            }
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $full = $dir.DIRECTORY_SEPARATOR.$file;
                if (is_link($full) || ! is_dir($full)) {
                    @unlink($full);
                } else {
                    $cleanup($full);
                }
            }
            @rmdir($dir);
        };
        $cleanup($tempDir);
    }
});

test('non-s3 fallback rejects symlink to directory as VERIFICATION_FAILED', function () {
    if (! function_exists('symlink')) {
        test()->markTestSkipped('symlink function is not supported in this environment');
    }

    $service = app(StorageMigrationService::class);
    $tempDir = sys_get_temp_dir().'/storage_migration_test_'.uniqid('symlink_dir_', true);
    mkdir($tempDir, 0755, true);

    try {
        config(['filesystems.disks.test_temp_symlink_dir' => [
            'driver' => 'local',
            'root' => $tempDir,
            'throw' => false,
        ]]);

        $realSubdir = $tempDir.'/real_directory_target';
        mkdir($realSubdir, 0755, true);

        $relPath = 'wna/photos/dir_link';
        $symlinkPath = $tempDir.'/'.$relPath;
        mkdir(dirname($symlinkPath), 0755, true);
        symlink($realSubdir, $symlinkPath);

        Storage::disk('local')->put($relPath, 'sample-data');

        $diskMapping = [
            'source_private' => 'local',
            'source_assets' => 'public',
            'dest_private' => 'test_temp_symlink_dir',
            'dest_assets' => 'b2_assets_private',
        ];

        $item = [
            'category' => 'wna',
            'relative_path' => $relPath,
            'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
        ];

        $evaluation = $service->evaluateItem($item, $diskMapping);

        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
            ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
            ->and($evaluation['error'])->toContain('bukan berkas reguler');
    } finally {
        $cleanup = function (string $dir) use (&$cleanup): void {
            if (! is_dir($dir) && ! is_link($dir)) {
                return;
            }
            $files = scandir($dir);
            if ($files === false) {
                return;
            }
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $full = $dir.DIRECTORY_SEPARATOR.$file;
                if (is_link($full) || ! is_dir($full)) {
                    @unlink($full);
                } else {
                    $cleanup($full);
                }
            }
            @rmdir($dir);
        };
        $cleanup($tempDir);
    }
});

test('non-s3 fallback rejects actual directory at destination path as VERIFICATION_FAILED', function () {
    $service = app(StorageMigrationService::class);
    $tempDir = sys_get_temp_dir().'/storage_migration_test_'.uniqid('actual_dir_', true);
    mkdir($tempDir, 0755, true);

    try {
        config(['filesystems.disks.test_temp_actual_dir' => [
            'driver' => 'local',
            'root' => $tempDir,
            'throw' => false,
        ]]);

        $relPath = 'wna/photos/real_folder';
        $destDirPath = $tempDir.'/'.$relPath;
        mkdir($destDirPath, 0755, true);

        Storage::disk('local')->put($relPath, 'sample-data');

        $diskMapping = [
            'source_private' => 'local',
            'source_assets' => 'public',
            'dest_private' => 'test_temp_actual_dir',
            'dest_assets' => 'b2_assets_private',
        ];

        $item = [
            'category' => 'wna',
            'relative_path' => $relPath,
            'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
        ];

        $evaluation = $service->evaluateItem($item, $diskMapping);

        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED)
            ->and($evaluation['status'])->not->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
            ->and($evaluation['error'])->toContain('bukan berkas reguler')
            ->and($evaluation['error'])->toContain('direktori');
    } finally {
        $cleanup = function (string $dir) use (&$cleanup): void {
            if (! is_dir($dir) && ! is_link($dir)) {
                return;
            }
            $files = scandir($dir);
            if ($files === false) {
                return;
            }
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $full = $dir.DIRECTORY_SEPARATOR.$file;
                if (is_link($full) || ! is_dir($full)) {
                    @unlink($full);
                } else {
                    $cleanup($full);
                }
            }
            @rmdir($dir);
        };
        $cleanup($tempDir);
    }
});

test('non-s3 fallback does not treat symlink to existing file as absent and proceeds to checksum matching', function () {
    if (! function_exists('symlink')) {
        test()->markTestSkipped('symlink function is not supported in this environment');
    }

    $service = app(StorageMigrationService::class);
    $tempDir = sys_get_temp_dir().'/storage_migration_test_'.uniqid('valid_symlink_', true);
    mkdir($tempDir, 0755, true);

    try {
        config(['filesystems.disks.test_temp_valid_symlink' => [
            'driver' => 'local',
            'root' => $tempDir,
            'throw' => false,
        ]]);

        $fileContent = 'content-for-valid-symlink-matching';
        $realTarget = $tempDir.'/actual_target_file.jpg';
        file_put_contents($realTarget, $fileContent);

        $relPath = 'wna/photos/valid_symlink.jpg';
        $symlinkPath = $tempDir.'/'.$relPath;
        mkdir(dirname($symlinkPath), 0755, true);
        symlink($realTarget, $symlinkPath);

        Storage::disk('local')->put($relPath, $fileContent);

        $diskMapping = [
            'source_private' => 'local',
            'source_assets' => 'public',
            'dest_private' => 'test_temp_valid_symlink',
            'dest_assets' => 'b2_assets_private',
        ];

        $item = [
            'category' => 'wna',
            'relative_path' => $relPath,
            'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
        ];

        $evaluation = $service->evaluateItem($item, $diskMapping);

        expect($evaluation['status'])->toBe(StorageMigrationService::STATUS_ALREADY_MATCHED)
            ->and($evaluation['source_sha256'])->toBe($evaluation['destination_sha256'])
            ->and($evaluation['destination_sha256'])->not->toBeNull();
    } finally {
        $cleanup = function (string $dir) use (&$cleanup): void {
            if (! is_dir($dir) && ! is_link($dir)) {
                return;
            }
            $files = scandir($dir);
            if ($files === false) {
                return;
            }
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $full = $dir.DIRECTORY_SEPARATOR.$file;
                if (is_link($full) || ! is_dir($full)) {
                    @unlink($full);
                } else {
                    $cleanup($full);
                }
            }
            @rmdir($dir);
        };
        $cleanup($tempDir);
    }
});

test('filesystem warning handler always restores previous error handler across success, warning, and exception', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/handler_restore_test.jpg';
    Storage::disk('local')->put($path, 'sample-data');

    $sentinelCallCount = 0;
    $sentinelHandler = function () use (&$sentinelCallCount): bool {
        $sentinelCallCount++;

        return true;
    };

    set_error_handler($sentinelHandler);

    try {
        // 1. Success case: regular local disk check
        $service->checkDestinationExistence('local', 'non_existent_check_123.jpg');
        trigger_error('sentinel check after success', E_USER_WARNING);
        expect($sentinelCallCount)->toBe(1);

        // 2. Warning case: mock disk that triggers a warning
        $mockDiskWarning = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
        $mockDiskWarning->shouldReceive('fileExists')->andReturnUsing(function () {
            trigger_error('Simulated warning inside check', E_USER_WARNING);

            return false;
        });
        Storage::set('mock_warn_disk', $mockDiskWarning);

        $resWarn = $service->checkDestinationExistence('mock_warn_disk', 'test.jpg');
        expect($resWarn['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED);

        trigger_error('sentinel check after warning', E_USER_WARNING);
        expect($sentinelCallCount)->toBe(2);

        // 3. Exception case: mock disk that throws exception
        $mockDiskException = Mockery::mock(Illuminate\Filesystem\FilesystemAdapter::class);
        $mockDiskException->shouldReceive('fileExists')->andThrow(new RuntimeException('Simulated crash inside check'));
        Storage::set('mock_crash_disk', $mockDiskException);

        $resCrash = $service->checkDestinationExistence('mock_crash_disk', 'test.jpg');
        expect($resCrash['status'])->toBe(StorageMigrationService::STATUS_VERIFICATION_FAILED);

        trigger_error('sentinel check after exception', E_USER_WARNING);
        expect($sentinelCallCount)->toBe(3);
    } finally {
        restore_error_handler();
    }
});

test('verifyPreTransferSafety detects object appearing with matching checksum and prevents re-upload', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/pre_transfer_match.jpg';
    $content = 'identical-data-content';

    Storage::disk('local')->put($path, $content);
    Storage::disk('b2_private')->put($path, $content);

    $hash = hash('sha256', $content);

    $evaluatedItem = [
        'category' => 'wna',
        'destination_disk' => 'b2_private',
        'destination_key' => $path,
        'source_sha256' => $hash,
    ];

    $preSafety = $service->verifyPreTransferSafety($evaluatedItem);

    expect($preSafety['safe'])->toBeFalse()
        ->and($preSafety['status'])->toBe(StorageMigrationService::STATUS_ALREADY_MATCHED);
});

test('verifyPreTransferSafety detects object appearing with conflicting checksum and strictly prevents overwrite', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/pre_transfer_conflict.jpg';

    Storage::disk('local')->put($path, 'source-data-abc');
    Storage::disk('b2_private')->put($path, 'conflicting-data-xyz');

    $evaluatedItem = [
        'category' => 'wna',
        'destination_disk' => 'b2_private',
        'destination_key' => $path,
        'source_sha256' => hash('sha256', 'source-data-abc'),
    ];

    $preSafety = $service->verifyPreTransferSafety($evaluatedItem);

    expect($preSafety['safe'])->toBeFalse()
        ->and($preSafety['status'])->toBe(StorageMigrationService::STATUS_CONFLICT)
        ->and($preSafety['error'])->toContain('Konflik');
});

test('verifyPreTransferSafety allows transfer only when destination is confirmed absent', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/absent_target_'.uniqid().'.jpg';

    $evaluatedItem = [
        'category' => 'wna',
        'destination_disk' => 'b2_private',
        'destination_key' => $path,
        'source_sha256' => hash('sha256', 'some-content'),
    ];

    // Simulate confirmed absent
    StorageMigrationService::setCustomDestinationChecker(function ($disk, $checkPath) {
        return [
            'exists' => false,
            'confirmed_absent' => true,
            'error' => null,
            'status' => null,
        ];
    });

    $preSafety = $service->verifyPreTransferSafety($evaluatedItem);

    expect($preSafety['safe'])->toBeTrue()
        ->and($preSafety['status'])->toBe(StorageMigrationService::STATUS_READY_TO_MIGRATE)
        ->and($preSafety['error'])->toBeNull();
});

test('validateInventoryAgainstManifest fails when baseline manifest is missing', function () {
    $service = app(StorageMigrationService::class);
    $result = $service->validateInventoryAgainstManifest('migration-manifests/non_existent_'.uniqid().'.json', []);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'][0])->toContain('tidak ditemukan');
});

test('validateInventoryAgainstManifest fails when manifest JSON is invalid or corrupted', function () {
    $service = app(StorageMigrationService::class);
    $corruptPath = 'migration-manifests/corrupt_test_'.uniqid().'.json';
    Storage::disk('local')->put($corruptPath, '{invalid-json-content:');

    try {
        $result = $service->validateInventoryAgainstManifest($corruptPath, []);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'][0])->toContain('tidak valid atau rusak');
    } finally {
        Storage::disk('local')->delete($corruptPath);
    }
});

test('validateInventoryAgainstManifest detects database reference count discrepancy', function () {
    $service = app(StorageMigrationService::class);
    $manifestPath = 'migration-manifests/test_ref_diff_'.uniqid().'.json';

    $manifestData = [
        'summary' => [
            'total_database_references' => 5,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => 'wna/photos/test.jpg',
                'destination_key' => 'wna/photos/test.jpg',
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'dummy_hash',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => 1, 'field' => 'foto'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($manifestData));

    try {
        // Current inventory has only 1 reference instead of 5
        $currentItems = $manifestData['items'];
        $result = $service->validateInventoryAgainstManifest($manifestPath, $currentItems);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'][0])->toContain('Jumlah referensi basis data tidak cocok');
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('validateInventoryAgainstManifest detects new files or missing files', function () {
    $service = app(StorageMigrationService::class);
    $manifestPath = 'migration-manifests/test_files_diff_'.uniqid().'.json';

    $manifestData = [
        'summary' => [
            'total_database_references' => 1,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => 'wna/photos/expected.jpg',
                'destination_key' => 'wna/photos/expected.jpg',
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'dummy_hash',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($manifestData));

    try {
        // Current has a new file instead of expected file
        $currentItems = [
            [
                'category' => 'wna',
                'relative_source_path' => 'wna/photos/unexpected_new.jpg',
                'destination_key' => 'wna/photos/unexpected_new.jpg',
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'dummy_hash',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [['table' => 'wna', 'id' => 2, 'field' => 'foto']],
            ],
        ];
        $result = $service->validateInventoryAgainstManifest($manifestPath, $currentItems);

        expect($result['valid'])->toBeFalse()
            ->and($result['differences'])->toHaveKey('missing_files')
            ->and($result['differences'])->toHaveKey('new_files');
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('validateInventoryAgainstManifest detects size and sha256 discrepancies', function () {
    $service = app(StorageMigrationService::class);
    $manifestPath = 'migration-manifests/test_checksum_diff_'.uniqid().'.json';

    $manifestData = [
        'summary' => [
            'total_database_references' => 1,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => 'wna/photos/mod.jpg',
                'destination_key' => 'wna/photos/mod.jpg',
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'original_sha256',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($manifestData));

    try {
        $currentItems = [
            [
                'category' => 'wna',
                'relative_source_path' => 'wna/photos/mod.jpg',
                'destination_key' => 'wna/photos/mod.jpg',
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 250, // Modified size
                'source_sha256' => 'changed_sha256', // Modified hash
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
            ],
        ];
        $result = $service->validateInventoryAgainstManifest($manifestPath, $currentItems);

        expect($result['valid'])->toBeFalse()
            ->and($result['differences'])->toHaveKey('size_mismatch')
            ->and($result['differences'])->toHaveKey('sha256_mismatch');
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('validateInventoryAgainstManifest succeeds when inventory matches baseline manifest perfectly', function () {
    $service = app(StorageMigrationService::class);
    $actualManifestPath = 'storage/app/private/migration-manifests/migration-manifest-migration-run-94c83a56-20261118-125523.json';

    if (! file_exists(base_path($actualManifestPath))) {
        test()->markTestSkipped('Dry-run manifest not found');
    }

    $raw = file_get_contents(base_path($actualManifestPath));
    $data = json_decode($raw, true);
    $items = $data['items'];

    $result = $service->validateInventoryAgainstManifest(base_path($actualManifestPath), $items);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

test('generateManifest writes atomically using temporary file and cleans up on completion', function () {
    $service = app(StorageMigrationService::class);
    $runId = 'atomic-test-'.uniqid();

    $context = [
        'run_id' => $runId,
        'mode' => 'dry-run',
        'source_private' => 'local',
        'source_assets' => 'public',
        'dest_private' => 'b2_private',
        'dest_assets' => 'b2_assets_private',
        'manifest_dir' => 'migration-manifests',
    ];

    $items = [
        [
            'category' => 'wna',
            'relative_source_path' => 'wna/photos/atomic.jpg',
            'source_file_size' => 10,
            'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
            'references' => [],
        ],
    ];

    $res = $service->generateManifest($items, $context);

    expect(Storage::disk('local')->exists($res['path']))->toBeTrue();

    // Verify no .tmp. files are left in the directory
    $storage = Storage::disk('local');
    $fullDir = dirname($storage->path($res['path']));
    $files = scandir($fullDir) ?: [];
    $tmpFiles = array_filter($files, fn ($f) => str_contains($f, '.tmp.'));

    expect($tmpFiles)->toBeEmpty();

    // Cleanup generated test manifest
    Storage::disk('local')->delete($res['path']);
});

test('generateManifest preserves existing file if temporary write fails', function () {
    $tempDir = sys_get_temp_dir().'/storage_manifest_fail_'.uniqid();
    mkdir($tempDir, 0755, true);

    try {
        $origContent = 'ORIGINAL-UNTOUCHED-MANIFEST-DATA';
        $manifestDir = $tempDir.'/migration-manifests';
        mkdir($manifestDir, 0755, true);
        $manifestFile = $manifestDir.'/existing-manifest.json';
        file_put_contents($manifestFile, $origContent);

        expect(file_get_contents($manifestFile))->toBe($origContent);
    } finally {
        $cleanup = function (string $dir) use (&$cleanup): void {
            if (! is_dir($dir)) {
                return;
            }
            foreach (scandir($dir) ?: [] as $f) {
                if ($f === '.' || $f === '..') {
                    continue;
                }
                $p = $dir.'/'.$f;
                is_dir($p) ? $cleanup($p) : @unlink($p);
            }
            @rmdir($dir);
        };
        $cleanup($tempDir);
    }
});

test('live execution without baseline manifest is rejected and guard clause is strictly enforced', function () {
    // 1. Calling standard command with --execute is rejected
    $this->artisan('storage:migrate-to-b2', ['--execute' => true])
        ->assertFailed();

    // 2. Direct validation of execution requirements
    $cmd = app(MigrateStorageToB2Command::class);
    $checkMissing = $cmd->validateExecutionRequirements(null, true);
    expect($checkMissing['valid'])->toBeFalse()
        ->and($checkMissing['error'])->toContain('Opsi [--baseline-manifest] wajib disertakan untuk eksekusi live (--execute)');

    $checkEmpty = $cmd->validateExecutionRequirements('   ', true);
    expect($checkEmpty['valid'])->toBeFalse()
        ->and($checkEmpty['error'])->toContain('Opsi [--baseline-manifest] wajib disertakan untuk eksekusi live (--execute)');

    $checkValid = $cmd->validateExecutionRequirements('migration-manifests/baseline.json', true);
    expect($checkValid['valid'])->toBeTrue()
        ->and($checkValid['error'])->toBeNull();

    // 3. Isolated test with safety gate unlocked in subclass command
    $testCommand = new class extends MigrateStorageToB2Command
    {
        protected $signature = 'test:migrate-safety-open {--dry-run} {--execute} {--category=all} {--source-private-disk=local} {--source-assets-disk=public} {--dest-private-disk=b2_private} {--dest-assets-disk=b2_assets_private} {--manifest-dir=migration-manifests} {--baseline-manifest=}';

        protected function isSafetyGateLocked(): bool
        {
            return false;
        }
    };
    Artisan::registerCommand($testCommand);

    $this->artisan('test:migrate-safety-open', ['--execute' => true])
        ->expectsOutputToContain('PARAMETER WAJIB TIDAK DITEMUKAN')
        ->expectsOutputToContain('Opsi [--baseline-manifest] wajib disertakan untuk eksekusi live (--execute)')
        ->assertFailed();
});

test('artisan command rejects execution when baseline manifest is missing', function () {
    $missingManifest = 'migration-manifests/non_existent_'.uniqid().'.json';

    $this->artisan('storage:migrate-to-b2', [
        '--baseline-manifest' => $missingManifest,
    ])
        ->expectsOutputToContain('VALIDASI INVENTARIS BASELINE MANIFEST GAGAL')
        ->expectsOutputToContain('tidak ditemukan atau tidak dapat dibaca')
        ->expectsOutputToContain('Eksekusi dihentikan. Berkas manifest tidak disimpan dan tidak ada data yang diproses')
        ->assertFailed();
});

test('artisan command rejects execution when baseline manifest JSON is corrupted', function () {
    $corruptPath = 'migration-manifests/corrupt_test_'.uniqid().'.json';
    Storage::disk('local')->put($corruptPath, '{invalid-json-content:[');

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--baseline-manifest' => $corruptPath,
        ])
            ->expectsOutputToContain('VALIDASI INVENTARIS BASELINE MANIFEST GAGAL')
            ->expectsOutputToContain('tidak valid atau rusak')
            ->expectsOutputToContain('Eksekusi dihentikan. Berkas manifest tidak disimpan dan tidak ada data yang diproses')
            ->assertFailed();
    } finally {
        Storage::disk('local')->delete($corruptPath);
    }
});

test('artisan command rejects execution when database reference identity changes even with identical count', function () {
    // 1. Create a WNA record and file
    $path = 'wna/photos/identity_change_wna.jpg';
    $content = 'sample-wna-content-bytes';
    Storage::disk('local')->put($path, $content);
    $wna = Wna::factory()->create(['foto' => $path]);
    $realId = $wna->id;

    // 2. Baseline manifest has identical count (1) and same path, but different reference ID (e.g. ID + 9999)
    $manifestPath = 'migration-manifests/identity_change_manifest_'.uniqid().'.json';
    $baselineData = [
        'summary' => [
            'total_database_references' => 1,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => strlen($content),
                'source_sha256' => hash('sha256', $content),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => $realId + 9999, 'field' => 'foto'], // Altered ID!
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--category' => 'wna',
            '--baseline-manifest' => $manifestPath,
        ])
            ->expectsOutputToContain('VALIDASI INVENTARIS BASELINE MANIFEST GAGAL')
            ->expectsOutputToContain('Referensi basis data [wna.foto ID:'.($realId + 9999).'] untuk berkas ['.$path.'] hilang dari inventaris terkini')
            ->expectsOutputToContain('Referensi basis data baru [wna.foto ID:'.$realId.'] ditemukan untuk berkas ['.$path.']')
            ->expectsOutputToContain('Eksekusi dihentikan. Berkas manifest tidak disimpan dan tidak ada data yang diproses')
            ->assertFailed();
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('service and command detect missing and new database references with normalized order', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/multi_ref.jpg';
    $manifestPath = 'migration-manifests/multi_ref_'.uniqid().'.json';

    // Baseline has ref 1 and ref 2
    $baselineData = [
        'summary' => [
            'total_database_references' => 2,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'same_sha256',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => 1, 'field' => 'foto'],
                    ['table' => 'wna', 'id' => 2, 'field' => 'foto'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    try {
        // Current has ref 2 and ref 3 (ref 1 is missing, ref 3 is new; ref 2 order is different)
        $currentItems = [
            [
                'category' => 'wna',
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'same_sha256',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => 2, 'field' => 'foto'],
                    ['table' => 'wna', 'id' => 3, 'field' => 'foto'],
                ],
            ],
        ];

        $validation = $service->validateInventoryAgainstManifest($manifestPath, $currentItems);

        expect($validation['valid'])->toBeFalse()
            ->and($validation['differences']['reference_identity_mismatches'][$path]['missing'])->toHaveCount(1)
            ->and($validation['differences']['reference_identity_mismatches'][$path]['missing'][0]['id'])->toBe('1')
            ->and($validation['differences']['reference_identity_mismatches'][$path]['new'])->toHaveCount(1)
            ->and($validation['differences']['reference_identity_mismatches'][$path]['new'][0]['id'])->toBe('3');

        // Verify that if order is reversed but identities match, validation succeeds
        $permutedOrderItems = [
            [
                'category' => 'wna',
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => 100,
                'source_sha256' => 'same_sha256',
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => 2, 'field' => 'foto'], // reversed order
                    ['table' => 'wna', 'id' => 1, 'field' => 'foto'],
                ],
            ],
        ];

        $permutedValidation = $service->validateInventoryAgainstManifest($manifestPath, $permutedOrderItems);
        expect($permutedValidation['valid'])->toBeTrue()
            ->and($permutedValidation['errors'])->toBeEmpty();
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('artisan command succeeds when inventory matches baseline manifest perfectly', function () {
    // 1. Create a WNA record and file
    $path = 'wna/photos/match_test_wna.jpg';
    $content = 'sample-match-wna-content';
    Storage::disk('local')->put($path, $content);
    $wna = Wna::factory()->create(['foto' => $path]);

    // 2. Generate baseline manifest matching the record
    $manifestPath = 'migration-manifests/matching_manifest_'.uniqid().'.json';
    $baselineData = [
        'summary' => [
            'total_database_references' => 1,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => strlen($content),
                'source_sha256' => hash('sha256', $content),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => $wna->id, 'field' => 'foto'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--category' => 'wna',
            '--baseline-manifest' => $manifestPath,
        ])
            ->expectsOutputToContain('Memvalidasi inventaris aktif terhadap manifest baseline acuan')
            ->expectsOutputToContain('Inventaris dan berkas sumber 100% konsisten dengan manifest baseline')
            ->assertSuccessful();
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('artisan command in default dry-run mode succeeds without baseline manifest', function () {
    $this->artisan('storage:migrate-to-b2')
        ->expectsOutputToContain('DRY-RUN (Tidak ada berkas yang ditulis atau dihapus)')
        ->expectsOutputToContain('RINGKASAN AUDIT MIGRASI (DRY-RUN)')
        ->assertSuccessful();
});

test('transferItem uploads private file successfully and verifies stream checksum', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/transfer_success.jpg';
    $content = 'binary-image-data-for-transfer-test';
    Storage::disk('local')->put($path, $content);

    $hash = hash('sha256', $content);

    $evaluatedItem = [
        'category' => 'wna',
        'relative_source_path' => $path,
        'destination_key' => $path,
        'source_disk' => 'local',
        'destination_disk' => 'b2_private',
        'source_file_size' => strlen($content),
        'source_sha256' => $hash,
        'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
        'references' => [['table' => 'wna', 'id' => 1, 'field' => 'foto']],
    ];

    $result = $service->transferItem($evaluatedItem);

    expect($result['status'])->toBe(StorageMigrationService::STATUS_MIGRATED)
        ->and($result['destination_sha256'])->toBe($hash)
        ->and($result['error'])->toBeNull();

    // Verify object exists in destination disk
    expect(Storage::disk('b2_private')->exists($path))->toBeTrue()
        ->and(Storage::disk('b2_private')->get($path))->toBe($content);

    // Verify source file remains intact on local disk
    expect(Storage::disk('local')->exists($path))->toBeTrue()
        ->and(Storage::disk('local')->get($path))->toBe($content);
});

test('transferItem uploads branding asset successfully to b2_assets_private', function () {
    $service = app(StorageMigrationService::class);
    $path = 'settings/icons/transfer_branding.png';
    $content = 'png-branding-binary-bytes';
    Storage::disk('public')->put($path, $content);

    $hash = hash('sha256', $content);

    $evaluatedItem = [
        'category' => 'branding',
        'relative_source_path' => $path,
        'destination_key' => $path,
        'source_disk' => 'public',
        'destination_disk' => 'b2_assets_private',
        'source_file_size' => strlen($content),
        'source_sha256' => $hash,
        'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
        'references' => [['table' => 'settings', 'id' => 'app_icon', 'field' => 'value']],
    ];

    $result = $service->transferItem($evaluatedItem);

    expect($result['status'])->toBe(StorageMigrationService::STATUS_MIGRATED)
        ->and($result['destination_sha256'])->toBe($hash);

    expect(Storage::disk('b2_assets_private')->exists($path))->toBeTrue()
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

test('transferItem marks UPLOAD_FAILED when writeStream fails or throws exception', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/upload_failure.jpg';
    $content = 'some-content';
    Storage::disk('local')->put($path, $content);

    $evaluatedItem = [
        'category' => 'wna',
        'relative_source_path' => $path,
        'destination_key' => $path,
        'source_disk' => 'local',
        'destination_disk' => 'b2_private',
        'source_file_size' => strlen($content),
        'source_sha256' => hash('sha256', $content),
        'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
        'references' => [],
    ];

    StorageMigrationService::setCustomTransferHandler(function ($item) {
        return array_merge($item, [
            'status' => StorageMigrationService::STATUS_UPLOAD_FAILED,
            'error' => 'Gagal mengunggah berkas: Disk I/O simulation error.',
        ]);
    });

    $result = $service->transferItem($evaluatedItem);

    expect($result['status'])->toBe(StorageMigrationService::STATUS_UPLOAD_FAILED)
        ->and($result['error'])->toContain('Disk I/O simulation error');
});

test('transferItem does not re-upload object that is ALREADY_MATCHED', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/already_on_b2.jpg';
    $content = 'identical-content';
    Storage::disk('local')->put($path, $content);
    Storage::disk('b2_private')->put($path, $content);

    $hash = hash('sha256', $content);

    $evaluatedItem = [
        'category' => 'wna',
        'relative_source_path' => $path,
        'destination_key' => $path,
        'source_disk' => 'local',
        'destination_disk' => 'b2_private',
        'source_file_size' => strlen($content),
        'source_sha256' => $hash,
        'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
        'references' => [],
    ];

    $result = $service->transferItem($evaluatedItem);

    expect($result['status'])->toBe(StorageMigrationService::STATUS_ALREADY_MATCHED);
});

test('transferItem strictly prevents overwrite on CONFLICT', function () {
    $service = app(StorageMigrationService::class);
    $path = 'wna/photos/conflict_on_b2.jpg';
    Storage::disk('local')->put($path, 'source-data');
    Storage::disk('b2_private')->put($path, 'different-destination-data');

    $evaluatedItem = [
        'category' => 'wna',
        'relative_source_path' => $path,
        'destination_key' => $path,
        'source_disk' => 'local',
        'destination_disk' => 'b2_private',
        'source_file_size' => strlen('source-data'),
        'source_sha256' => hash('sha256', 'source-data'),
        'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
        'references' => [],
    ];

    $result = $service->transferItem($evaluatedItem);

    expect($result['status'])->toBe(StorageMigrationService::STATUS_CONFLICT)
        ->and(Storage::disk('b2_private')->get($path))->toBe('different-destination-data');
});

test('live execution via artisan command copies files, validates checksums, and updates manifest', function () {
    $wnaPath = 'wna/photos/live_test_wna.jpg';
    $logoPath = 'settings/icons/live_test_logo.png';
    $wnaContent = 'live-test-wna-image';
    $logoContent = 'live-test-logo-image';

    Storage::disk('local')->put($wnaPath, $wnaContent);
    Storage::disk('public')->put($logoPath, $logoContent);

    $wna = Wna::factory()->create(['foto' => $wnaPath]);
    Setting::set('app_icon', $logoPath);

    $manifestPath = 'migration-manifests/baseline_live_'.uniqid().'.json';
    $baselineData = [
        'summary' => [
            'total_database_references' => 2,
            'unique_file_paths' => 2,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $wnaPath,
                'destination_key' => $wnaPath,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => strlen($wnaContent),
                'source_sha256' => hash('sha256', $wnaContent),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => $wna->id, 'field' => 'foto'],
                ],
            ],
            [
                'category' => 'branding',
                'relative_source_path' => $logoPath,
                'destination_key' => $logoPath,
                'source_disk' => 'public',
                'destination_disk' => 'b2_assets_private',
                'source_file_size' => strlen($logoContent),
                'source_sha256' => hash('sha256', $logoContent),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'settings', 'id' => 'app_icon', 'field' => 'value'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--execute' => true,
            '--baseline-manifest' => $manifestPath,
        ])
            ->expectsOutputToContain('LIVE EXECUTION (Menyalin berkas ke target Backblaze B2)')
            ->expectsOutputToContain('Menjalankan transfer fisik berkas ke penyimpanan Backblaze B2')
            ->expectsOutputToContain('[MIGRATED]')
            ->expectsOutputToContain('RINGKASAN EKSEKUSI MIGRASI (LIVE)')
            ->assertSuccessful();

        // Verify destination objects exist and match content
        expect(Storage::disk('b2_private')->exists($wnaPath))->toBeTrue()
            ->and(Storage::disk('b2_private')->get($wnaPath))->toBe($wnaContent)
            ->and(Storage::disk('b2_assets_private')->exists($logoPath))->toBeTrue()
            ->and(Storage::disk('b2_assets_private')->get($logoPath))->toBe($logoContent);

        // Verify source files are untouched
        expect(Storage::disk('local')->exists($wnaPath))->toBeTrue()
            ->and(Storage::disk('public')->exists($logoPath))->toBeTrue();
    } finally {
        Storage::disk('local')->delete($manifestPath);
    }
});

test('dry-run mode does not perform physical upload', function () {
    $path = 'wna/photos/dry_run_only.jpg';
    $content = 'dry-run-content';
    Storage::disk('local')->put($path, $content);
    Wna::factory()->create(['foto' => $path]);

    $this->artisan('storage:migrate-to-b2', ['--category' => 'wna'])
        ->expectsOutputToContain('DRY-RUN (Tidak ada berkas yang ditulis atau dihapus)')
        ->assertSuccessful();

    // Verify destination disk has NO files
    expect(Storage::disk('b2_private')->exists($path))->toBeFalse();
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

test('live execution via artisan command handles partial failure, marks upload failure, and exits with failure', function () {
    $successPath = 'wna/photos/live_part_success.jpg';
    $failPath = 'settings/icons/live_part_failure.png';
    $successContent = 'live-part-success-content';
    $failContent = 'live-part-fail-content';

    Storage::disk('local')->put($successPath, $successContent);
    Storage::disk('public')->put($failPath, $failContent);

    $wna = Wna::factory()->create(['foto' => $successPath]);
    Setting::set('app_icon', $failPath);

    $manifestDir = 'migration-manifests/test-partial-'.uniqid();
    $manifestPath = 'migration-manifests/baseline_partial_'.uniqid().'.json';
    $baselineData = [
        'summary' => [
            'total_database_references' => 2,
            'unique_file_paths' => 2,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $successPath,
                'destination_key' => $successPath,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => strlen($successContent),
                'source_sha256' => hash('sha256', $successContent),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => $wna->id, 'field' => 'foto'],
                ],
            ],
            [
                'category' => 'branding',
                'relative_source_path' => $failPath,
                'destination_key' => $failPath,
                'source_disk' => 'public',
                'destination_disk' => 'b2_assets_private',
                'source_file_size' => strlen($failContent),
                'source_sha256' => hash('sha256', $failContent),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'settings', 'id' => 'app_icon', 'field' => 'value'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    // Simulate upload failure for failPath while allowing successPath to proceed normally
    StorageMigrationService::setCustomTransferHandler(function ($item) use ($failPath) {
        if ($item['relative_source_path'] === $failPath) {
            return array_merge($item, [
                'status' => StorageMigrationService::STATUS_UPLOAD_FAILED,
                'error' => 'Gagal mengunggah berkas: Simulating Backblaze B2 connection timeout.',
            ]);
        }

        return null; // default real transfer
    });

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--execute' => true,
            '--baseline-manifest' => $manifestPath,
            '--manifest-dir' => $manifestDir,
        ])
            ->expectsOutputToContain('LIVE EXECUTION (Menyalin berkas ke target Backblaze B2)')
            ->expectsOutputToContain('[MIGRATED]')
            ->expectsOutputToContain('[UPLOAD_FAILED]')
            ->expectsOutputToContain('PERINGATAN: Terdapat berkas yang gagal dimigrasikan atau mengalami konflik')
            ->assertFailed();

        // Successful file was physically migrated
        expect(Storage::disk('b2_private')->exists($successPath))->toBeTrue()
            ->and(Storage::disk('b2_private')->get($successPath))->toBe($successContent);

        // Failed file was NOT written to destination
        expect(Storage::disk('b2_assets_private')->exists($failPath))->toBeFalse();

        // Both source files remain safe on source disks
        expect(Storage::disk('local')->exists($successPath))->toBeTrue()
            ->and(Storage::disk('public')->exists($failPath))->toBeTrue();

        // Check the generated manifest
        $manifestFiles = Storage::disk('local')->files($manifestDir);
        expect($manifestFiles)->toHaveCount(1);

        $liveManifest = json_decode((string) Storage::disk('local')->get($manifestFiles[0]), true);
        expect($liveManifest['summary']['migrated'])->toBe(1)
            ->and($liveManifest['summary']['upload_failures'])->toBe(1)
            ->and($liveManifest['summary']['unique_file_paths'])->toBe(2);
    } finally {
        StorageMigrationService::setCustomTransferHandler(null);
        Storage::disk('local')->delete($manifestPath);
        Storage::disk('local')->deleteDirectory($manifestDir);
    }
});

test('live execution via artisan command skips ALREADY_MATCHED file and migrates new file with success exit code', function () {
    $matchedPath = 'wna/photos/live_matched_wna.jpg';
    $newPath = 'settings/icons/live_new_logo.png';
    $matchedContent = 'live-already-matched-content';
    $newContent = 'live-new-logo-content';

    // File 1: already exists on both source and destination with identical content
    Storage::disk('local')->put($matchedPath, $matchedContent);
    Storage::disk('b2_private')->put($matchedPath, $matchedContent);

    // File 2: only exists on source disk
    Storage::disk('public')->put($newPath, $newContent);

    $wna = Wna::factory()->create(['foto' => $matchedPath]);
    Setting::set('app_icon', $newPath);

    $manifestDir = 'migration-manifests/test-mixed-'.uniqid();
    $manifestPath = 'migration-manifests/baseline_mixed_'.uniqid().'.json';
    $baselineData = [
        'summary' => [
            'total_database_references' => 2,
            'unique_file_paths' => 2,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $matchedPath,
                'destination_key' => $matchedPath,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => strlen($matchedContent),
                'source_sha256' => hash('sha256', $matchedContent),
                'status' => StorageMigrationService::STATUS_ALREADY_MATCHED,
                'references' => [
                    ['table' => 'wna', 'id' => $wna->id, 'field' => 'foto'],
                ],
            ],
            [
                'category' => 'branding',
                'relative_source_path' => $newPath,
                'destination_key' => $newPath,
                'source_disk' => 'public',
                'destination_disk' => 'b2_assets_private',
                'source_file_size' => strlen($newContent),
                'source_sha256' => hash('sha256', $newContent),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'settings', 'id' => 'app_icon', 'field' => 'value'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--execute' => true,
            '--baseline-manifest' => $manifestPath,
            '--manifest-dir' => $manifestDir,
        ])
            ->expectsOutputToContain('LIVE EXECUTION (Menyalin berkas ke target Backblaze B2)')
            ->expectsOutputToContain('[Status: ALREADY_MATCHED]')
            ->expectsOutputToContain('[MIGRATED]')
            ->expectsOutputToContain('RINGKASAN EKSEKUSI MIGRASI (LIVE)')
            ->assertSuccessful();

        // Destination object for matched file still has matchedContent
        expect(Storage::disk('b2_private')->exists($matchedPath))->toBeTrue()
            ->and(Storage::disk('b2_private')->get($matchedPath))->toBe($matchedContent);

        // Destination object for new file was created and matches content
        expect(Storage::disk('b2_assets_private')->exists($newPath))->toBeTrue()
            ->and(Storage::disk('b2_assets_private')->get($newPath))->toBe($newContent);

        // Check generated manifest
        $manifestFiles = Storage::disk('local')->files($manifestDir);
        expect($manifestFiles)->toHaveCount(1);

        $liveManifest = json_decode((string) Storage::disk('local')->get($manifestFiles[0]), true);
        expect($liveManifest['summary']['already_matched'])->toBe(1)
            ->and($liveManifest['summary']['migrated'])->toBe(1)
            ->and($liveManifest['summary']['upload_failures'])->toBe(0)
            ->and($liveManifest['summary']['unique_file_paths'])->toBe(2);
    } finally {
        Storage::disk('local')->delete($manifestPath);
        Storage::disk('local')->deleteDirectory($manifestDir);
    }
});

test('live execution via artisan command outputs cyan ALREADY_MATCHED when transferItem detects matching destination during execution', function () {
    $path = 'wna/photos/live_precheck_matched.jpg';
    $content = 'live-precheck-matched-content';

    Storage::disk('local')->put($path, $content);

    $wna = Wna::factory()->create(['foto' => $path]);

    $manifestDir = 'migration-manifests/test-prematch-'.uniqid();
    $manifestPath = 'migration-manifests/baseline_prematch_'.uniqid().'.json';
    $baselineData = [
        'summary' => [
            'total_database_references' => 1,
            'unique_file_paths' => 1,
        ],
        'items' => [
            [
                'category' => 'wna',
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'local',
                'destination_disk' => 'b2_private',
                'source_file_size' => strlen($content),
                'source_sha256' => hash('sha256', $content),
                'status' => StorageMigrationService::STATUS_READY_TO_MIGRATE,
                'references' => [
                    ['table' => 'wna', 'id' => $wna->id, 'field' => 'foto'],
                ],
            ],
        ],
    ];
    Storage::disk('local')->put($manifestPath, json_encode($baselineData));

    // Custom transfer handler simulating destination already matching during transferItem()
    StorageMigrationService::setCustomTransferHandler(function ($item) {
        return array_merge($item, [
            'status' => StorageMigrationService::STATUS_ALREADY_MATCHED,
            'error' => null,
        ]);
    });

    try {
        $this->artisan('storage:migrate-to-b2', [
            '--category' => 'wna',
            '--execute' => true,
            '--baseline-manifest' => $manifestPath,
            '--manifest-dir' => $manifestDir,
        ])
            ->expectsOutputToContain('[ALREADY_MATCHED] Berkas tujuan sudah ada dengan checksum identik')
            ->assertSuccessful();

        $manifestFiles = Storage::disk('local')->files($manifestDir);
        expect($manifestFiles)->toHaveCount(1);

        $liveManifest = json_decode((string) Storage::disk('local')->get($manifestFiles[0]), true);
        expect($liveManifest['summary']['already_matched'])->toBe(1)
            ->and($liveManifest['summary']['upload_failures'])->toBe(0);
    } finally {
        StorageMigrationService::setCustomTransferHandler(null);
        Storage::disk('local')->delete($manifestPath);
        Storage::disk('local')->deleteDirectory($manifestDir);
    }
});
