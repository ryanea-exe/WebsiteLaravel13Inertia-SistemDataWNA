<?php

use App\Services\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    // Ensure clean default state for each test
    config(['filesystems.backend' => 'local']);
});

/*
|--------------------------------------------------------------------------
| A. Backend Local Tests (MAMP / Offline Environment)
|--------------------------------------------------------------------------
*/

test('storage backend defaults to local when not configured', function () {
    config(['filesystems.backend' => null]);
    $service = new DocumentStorageService;

    // config('filesystems.backend', 'local') falls back to 'local'
    expect($service->getBackend())->toBe('local')
        ->and($service->getPrivateDisk())->toBe('local')
        ->and($service->getAssetsDisk())->toBe('public')
        ->and($service->getPublicDisk())->toBe('public');
});

test('local backend routes private files to local disk and assets to public disk', function () {
    config(['filesystems.backend' => 'local']);
    $service = new DocumentStorageService;

    expect($service->getPrivateDisk())->toBe('local')
        ->and($service->getAssetsDisk())->toBe('public');
});

test('local backend uploads private photo to local disk with consistent relative path', function () {
    Storage::fake('local');
    Storage::fake('public');

    config(['filesystems.backend' => 'local']);
    $service = new DocumentStorageService;

    $photo = UploadedFile::fake()->image('wna_sample.jpg', 300, 300)->size(500);
    $path = $service->storePrivatePhoto($photo, 'wna/photos');

    // Consistent relative path format
    expect(str_starts_with($path, 'wna/photos/'))->toBeTrue()
        ->and(str_ends_with($path, '.jpg'))->toBeTrue();

    // Must exist on local disk, not public
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
});

test('local backend uploads asset photo to public disk with consistent relative path', function () {
    Storage::fake('local');
    Storage::fake('public');

    config(['filesystems.backend' => 'local']);
    $service = new DocumentStorageService;

    $logo = UploadedFile::fake()->image('logo.png', 200, 200)->size(200);
    $path = $service->storePublicPhoto($logo, 'settings/icons');

    // Consistent relative path format
    expect(str_starts_with($path, 'settings/icons/'))->toBeTrue()
        ->and(str_ends_with($path, '.png'))->toBeTrue();

    // Must exist on public disk, not local
    Storage::disk('public')->assertExists($path);
    Storage::disk('local')->assertMissing($path);
});

test('local backend delete operations and exists checks function correctly', function () {
    Storage::fake('local');
    Storage::fake('public');

    config(['filesystems.backend' => 'local']);
    $service = new DocumentStorageService;

    $photo = UploadedFile::fake()->image('doc.jpg', 300, 300)->size(300);
    $path = $service->storePrivatePhoto($photo, 'wna/photos');

    expect($service->exists($path))->toBeTrue()
        ->and($service->exists($path, 'local'))->toBeTrue()
        ->and($service->exists($path, 'public'))->toBeFalse();

    // Delete using deletePrivate
    expect($service->deletePrivate($path))->toBeTrue();
    Storage::disk('local')->assertMissing($path);
    expect($service->exists($path))->toBeFalse();
});

test('local backend does not require Backblaze B2 credentials', function () {
    // Completely wipe B2 configuration
    config([
        'filesystems.backend' => 'local',
        'filesystems.disks.b2_private.key' => null,
        'filesystems.disks.b2_private.secret' => null,
        'filesystems.disks.b2_private.bucket' => null,
        'filesystems.disks.b2_assets_private.key' => null,
        'filesystems.disks.b2_assets_private.secret' => null,
        'filesystems.disks.b2_assets_private.bucket' => null,
    ]);

    $service = new DocumentStorageService;

    expect($service->getPrivateDisk())->toBe('local')
        ->and($service->getAssetsDisk())->toBe('public');
});

/*
|--------------------------------------------------------------------------
| B. Backend Backblaze Tests (Remote Storage Simulation via Fakes)
|--------------------------------------------------------------------------
*/

test('backblaze backend routes private files to b2_private and assets to b2_assets_private', function () {
    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'test-priv-key',
            'secret' => 'test-priv-secret',
            'bucket' => 'priv-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
        'filesystems.disks.b2_assets_private' => [
            'driver' => 's3',
            'key' => 'test-assets-key',
            'secret' => 'test-assets-secret',
            'bucket' => 'assets-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
    ]);

    $service = new DocumentStorageService;

    expect($service->getBackend())->toBe('backblaze')
        ->and($service->getPrivateDisk())->toBe('b2_private')
        ->and($service->getAssetsDisk())->toBe('b2_assets_private');
});

test('backblaze backend uploads private files to b2_private disk without falling back to local', function () {
    Storage::fake('local');
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');

    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'test-priv-key',
            'secret' => 'test-priv-secret',
            'bucket' => 'priv-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
        'filesystems.disks.b2_assets_private' => [
            'driver' => 's3',
            'key' => 'test-assets-key',
            'secret' => 'test-assets-secret',
            'bucket' => 'assets-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
    ]);

    $service = new DocumentStorageService;

    $photo = UploadedFile::fake()->image('private_wna.jpg', 300, 300)->size(500);
    $path = $service->storePrivatePhoto($photo, 'wna/photos');

    // Must be in b2_private, NOT in local or b2_assets_private
    Storage::disk('b2_private')->assertExists($path);
    Storage::disk('local')->assertMissing($path);
    Storage::disk('b2_assets_private')->assertMissing($path);

    // Relative path format remains unchanged
    expect(str_starts_with($path, 'wna/photos/'))->toBeTrue();
});

test('backblaze backend uploads asset photos to b2_assets_private disk', function () {
    Storage::fake('public');
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');

    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'test-priv-key',
            'secret' => 'test-priv-secret',
            'bucket' => 'priv-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
        'filesystems.disks.b2_assets_private' => [
            'driver' => 's3',
            'key' => 'test-assets-key',
            'secret' => 'test-assets-secret',
            'bucket' => 'assets-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
    ]);

    $service = new DocumentStorageService;

    $logo = UploadedFile::fake()->image('app_icon.png', 200, 200)->size(200);
    $path = $service->storePublicPhoto($logo, 'settings/icons');

    Storage::disk('b2_assets_private')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
    Storage::disk('b2_private')->assertMissing($path);
});

test('backblaze backend delete operations delete from the correct remote disks', function () {
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');

    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'test-priv-key',
            'secret' => 'test-priv-secret',
            'bucket' => 'priv-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
        'filesystems.disks.b2_assets_private' => [
            'driver' => 's3',
            'key' => 'test-assets-key',
            'secret' => 'test-assets-secret',
            'bucket' => 'assets-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
    ]);

    $service = new DocumentStorageService;

    // 1. Private delete
    $photo = UploadedFile::fake()->image('test_abg.jpg', 300, 300)->size(400);
    $privPath = $service->storePrivatePhoto($photo, 'abg/photos');
    expect($service->exists($privPath))->toBeTrue();
    expect($service->deletePrivate($privPath))->toBeTrue();
    Storage::disk('b2_private')->assertMissing($privPath);

    // 2. Assets delete
    $icon = UploadedFile::fake()->image('test_icon.png', 200, 200)->size(100);
    $assetPath = $service->storePublicPhoto($icon, 'settings/icons');
    expect($service->exists($assetPath))->toBeTrue();
    expect($service->deletePublic($assetPath))->toBeTrue();
    Storage::disk('b2_assets_private')->assertMissing($assetPath);
});

test('invalid storage backend throws clear InvalidArgumentException and does not silently fallback', function () {
    config(['filesystems.backend' => 'invalid_storage_provider']);
    $service = new DocumentStorageService;

    expect(fn () => $service->getPrivateDisk())
        ->toThrow(InvalidArgumentException::class, "Invalid storage backend [invalid_storage_provider]. Supported backends: 'local', 'backblaze'.");
});

test('missing backblaze configuration when backend is backblaze throws clear RuntimeException', function () {
    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => '',
            'secret' => '',
            'bucket' => '',
            'region' => '',
            'endpoint' => '',
        ],
    ]);

    $service = new DocumentStorageService;

    expect(fn () => $service->getPrivateDisk())
        ->toThrow(RuntimeException::class, 'Backblaze B2 configuration for disk [b2_private] is incomplete.');
});

test('b2_private and b2_assets_private configurations remain strictly isolated and do not cross credentials', function () {
    config([
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'key-priv-unique-123',
            'secret' => 'sec-priv-unique-456',
            'bucket' => 'bucket-private-kanim',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
        'filesystems.disks.b2_assets_private' => [
            'driver' => 's3',
            'key' => 'key-assets-unique-789',
            'secret' => 'sec-assets-unique-012',
            'bucket' => 'bucket-assets-kanim',
            'region' => 'us-west-004',
            'endpoint' => 'https://s3.us-west-004.backblazeb2.com',
        ],
    ]);

    $privConfig = config('filesystems.disks.b2_private');
    $assetsConfig = config('filesystems.disks.b2_assets_private');

    expect($privConfig['key'])->not->toBe($assetsConfig['key'])
        ->and($privConfig['bucket'])->not->toBe($assetsConfig['bucket'])
        ->and($privConfig['region'])->not->toBe($assetsConfig['region']);
});

/*
|--------------------------------------------------------------------------
| C. Isolated streamFile() Verification (Stage 4C.3C)
|--------------------------------------------------------------------------
*/

test('streamFile successfully streams synthetic file from fake local storage with 100% payload integrity', function () {
    Storage::fake('local');
    config(['filesystems.backend' => 'local']);

    $service = new DocumentStorageService;
    $uniqueId = 'stage_4c3c_'.bin2hex(random_bytes(6));
    $relativePath = "wna/photos/__stage_4c3c_probe_{$uniqueId}.jpg";

    // Synthetic binary JPEG header payload
    $syntheticPayload = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00\x60\x00\x60\x00\x00\xFF\xDB\x00\x43"
        ."STAGE-4C3C-SYNTHETIC-JPEG-PAYLOAD-PROBE-{$uniqueId}";

    Storage::disk('local')->put($relativePath, $syntheticPayload);

    $response = $service->streamFile(
        disk: 'local',
        path: $relativePath,
        mimeType: 'image/jpeg',
        headers: [
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ],
        disposition: 'inline'
    );

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->getStatusCode())->toBe(200);

    // Verify headers
    expect($response->headers->get('Content-Type'))->toBe('image/jpeg')
        ->and($response->headers->get('Content-Disposition'))->toBe('inline')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Pragma'))->toBe('no-cache')
        ->and($response->headers->get('Expires'))->toBe('0');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('no-cache')
        ->and($cacheControl)->toContain('must-revalidate')
        ->and($cacheControl)->not->toContain('public');

    // Execute stream callback and verify exact byte content
    ob_start();
    $response->sendContent();
    $streamedOutput = ob_get_clean();

    expect($streamedOutput)->toBe($syntheticPayload)
        ->and(strlen($streamedOutput))->toBe(strlen($syntheticPayload));
});

test('streamFile successfully streams synthetic file from fake b2_private storage when backend is backblaze', function () {
    Storage::fake('b2_private');
    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'test-priv-key',
            'secret' => 'test-priv-secret',
            'bucket' => 'priv-bucket',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        ],
    ]);

    $service = new DocumentStorageService;
    $uniqueId = 'stage_4c3c_b2_'.bin2hex(random_bytes(6));
    $relativePath = "users/photos/__stage_4c3c_probe_{$uniqueId}.png";

    // Synthetic PNG header payload
    $syntheticPayload = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR"
        ."STAGE-4C3C-SYNTHETIC-PNG-PAYLOAD-PROBE-{$uniqueId}";

    Storage::disk('b2_private')->put($relativePath, $syntheticPayload);

    $response = $service->streamFile(
        disk: 'b2_private',
        path: $relativePath,
        mimeType: 'image/png',
        headers: [
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ]
    );

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->getStatusCode())->toBe(200);

    expect($response->headers->get('Content-Type'))->toBe('image/png')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');

    ob_start();
    $response->sendContent();
    $streamedOutput = ob_get_clean();

    expect($streamedOutput)->toBe($syntheticPayload)
        ->and(strlen($streamedOutput))->toBe(strlen($syntheticPayload));
});

test('streamFile rejects disallowed directories, path traversal, nonexistent files, and unsupported mime types', function () {
    Storage::fake('local');
    $service = new DocumentStorageService;

    // 1. Disallowed directory (not in ALLOWED_STORAGE_DIRECTORIES)
    Storage::disk('local')->put('unauthorized_dir/test.jpg', 'fake-image-bytes');
    try {
        $service->streamFile('local', 'unauthorized_dir/test.jpg', 'image/jpeg');
        test()->fail('Expected 404 for disallowed directory');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(404);
    }

    // 2. Path traversal
    try {
        $service->streamFile('local', 'wna/photos/../../etc/passwd', 'image/jpeg');
        test()->fail('Expected 404 for path traversal');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(404);
    }

    // 3. Nonexistent file
    try {
        $service->streamFile('local', 'wna/photos/nonexistent_file.jpg', 'image/jpeg');
        test()->fail('Expected 404 for missing file');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(404);
    }

    // 4. Unsupported MIME type (e.g. text/plain or application/javascript)
    Storage::disk('local')->put('wna/photos/valid_path.jpg', 'valid-bytes');
    try {
        $service->streamFile('local', 'wna/photos/valid_path.jpg', 'text/plain');
        test()->fail('Expected 415 for unsupported MIME');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(415);
    }

    try {
        $service->streamFile('local', 'wna/photos/valid_path.jpg', 'application/javascript');
        test()->fail('Expected 415 for javascript MIME');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(415);
    }
});

/*
|--------------------------------------------------------------------------
| E. Observability, Sanitization & Exception Handling Tests
|--------------------------------------------------------------------------
*/

test('sanitizeErrorMessage strips credentials, tokens, signatures, and system base path', function () {
    $service = new DocumentStorageService;

    $raw = 'Error connecting to S3 with AWSAccessKeyId=AKIAIOSFODNN7EXAMPLE&Signature=vjbyPxybdZaNmGa+GQIdEosKpXc=&token=secret_tok_12345 at '.base_path('app/Http').' Authorization: AWS4-HMAC-SHA256 Credential=mock/123';
    $sanitized = $service->sanitizeErrorMessage($raw);

    expect($sanitized)->not->toContain('AKIAIOSFODNN7EXAMPLE')
        ->and($sanitized)->not->toContain('vjbyPxybdZaNmGa+GQIdEosKpXc=')
        ->and($sanitized)->not->toContain('secret_tok_12345')
        ->and($sanitized)->not->toContain(base_path())
        ->and($sanitized)->toContain('[APP_ROOT]')
        ->and($sanitized)->toContain('[REDACTED]');
});

test('storePrivatePhoto wraps storage exceptions with sanitized message and prevents raw exception chain leakage', function () {
    $service = new DocumentStorageService;

    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('isValid')->andReturn(true);
    $file->shouldReceive('getSize')->andReturn(1024);
    $file->shouldReceive('getMimeType')->andReturn('image/jpeg');
    $file->shouldReceive('getClientOriginalExtension')->andReturn('jpg');

    $originalException = new RuntimeException('S3 failure: AWSAccessKeyId=AKIAIOSFODNN7EXAMPLE access denied');
    $file->shouldReceive('storeAs')->andThrow($originalException);

    try {
        $service->storePrivatePhoto($file, 'wna/photos');
        test()->fail('Expected RuntimeException was not thrown');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('Failed to store private photo')
            ->and($e->getMessage())->toContain('[REDACTED]')
            ->and($e->getMessage())->not->toContain('AKIAIOSFODNN7EXAMPLE')
            // Crucial security guarantee: raw exception is NOT chained to prevent recursive log leakage
            ->and($e->getPrevious())->toBeNull();
    }
});

test('extractDiagnosticDetails extracts exception class, code, and provider details without leaking raw exception', function () {
    $service = new DocumentStorageService;

    $rootException = new class('Root AWS S3 Error', 403) extends RuntimeException
    {
        public function getStatusCode(): int
        {
            return 403;
        }

        public function getAwsErrorCode(): string
        {
            return 'AccessDenied';
        }
    };

    $wrapperException = new RuntimeException('Flysystem UnableToWriteFile', 0, $rootException);

    $diagnostics = $service->extractDiagnosticDetails($wrapperException);

    expect($diagnostics['exception_class'])->toBe(get_class($wrapperException))
        ->and($diagnostics['root_cause_class'])->toBe(get_class($rootException))
        ->and($diagnostics['provider_status'])->toBe(403)
        ->and($diagnostics['provider_error_code'])->toBe('AccessDenied')
        ->and($diagnostics['error_code'])->toBe(403);
});

test('storePrivatePhoto throws RuntimeException when storeAs returns false', function () {
    $service = new DocumentStorageService;

    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('isValid')->andReturn(true);
    $file->shouldReceive('getSize')->andReturn(1024);
    $file->shouldReceive('getMimeType')->andReturn('image/jpeg');
    $file->shouldReceive('getClientOriginalExtension')->andReturn('jpg');
    $file->shouldReceive('storeAs')->andReturn(false);

    expect(fn () => $service->storePrivatePhoto($file, 'wna/photos'))
        ->toThrow(RuntimeException::class, 'Failed to store private photo.');
});
