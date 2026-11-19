<?php

use App\Console\Commands\ProbeB2StorageCommand;
use App\Services\B2ProbeManifest;
use App\Services\DocumentStorageService;
use Aws\Command;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    config(['filesystems.backend' => 'local']);
});

afterEach(function () {
    ProbeB2StorageCommand::setS3ClientFactory(null);
});

test('storage probe-b2 command is registered and recognized by artisan with all options', function () {
    $this->artisan('storage:probe-b2 --help')
        ->assertSuccessful()
        ->expectsOutputToContain('storage:probe-b2')
        ->expectsOutputToContain('--prepare')
        ->expectsOutputToContain('--credential-check')
        ->expectsOutputToContain('--connectivity-check')
        ->expectsOutputToContain('--io-check')
        ->expectsOutputToContain('--live');
});

test('storage probe-b2 in default prepare mode executes non-interactive manifest validation without remote B2 operations', function () {
    $this->artisan('storage:probe-b2')
        ->assertSuccessful()
        ->expectsOutputToContain('STAGE 4E.1 — ISOLATED BACKBLAZE B2 TEST RUNNER INFRASTRUCTURE')
        ->expectsOutputToContain('Menjalankan Mode Persiapan Runner (Tanpa Operasi Remote B2)')
        ->expectsOutputToContain('__stage_4e_probe__')
        ->expectsOutputToContain('Verifikasi Keamanan Manifest: PASS')
        ->expectsOutputToContain('Catatan Isolasi: Tidak ada operasi remote Backblaze B2 yang dieksekusi');
});

test('stage 4e1 safety gate strictly blocks --live mode', function () {
    $this->artisan('storage:probe-b2 --live')
        ->assertFailed()
        ->expectsOutputToContain('STAGE 4E.1 SAFETY GATE ACTIVE:')
        ->expectsOutputToContain('Live network operations to Backblaze B2 are strictly locked.');
});

test('credential-check interactive prompt successfully validates inputs and injects into memory without remote B2 operations', function () {
    $mockSecret = 'mock-secret-9876543210-do-not-leak';

    $this->artisan('storage:probe-b2 --credential-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-key-005123456789')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', $mockSecret)
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'yes')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->assertSuccessful()
        ->expectsOutputToContain('Validasi Kredensial & Konfigurasi: PASS')
        ->expectsOutputToContain('In-Memory Injection: PASS')
        ->expectsOutputToContain('Catatan Isolasi: Tidak ada S3 client yang dibuat untuk akses remote bucket')
        ->expectsOutputToContain('mock*************6789') // Masked Key ID
        ->expectsOutputToContain('Penggunaan Application Key tidak membuktikan izin bucket di sisi provider Backblaze B2.')
        ->doesntExpectOutputToContain($mockSecret); // Secret MUST NOT leak in output

    // Verify memory injection took effect for this process
    $service = new DocumentStorageService;
    expect($service->getBackend())->toBe('backblaze')
        ->and($service->getPrivateDisk())->toBe('b2_private')
        ->and($service->getAssetsDisk())->toBe('b2_assets_private')
        ->and(config('filesystems.disks.b2_private.throw'))->toBeTrue()
        ->and(config('filesystems.disks.b2_assets_private.throw'))->toBeTrue()
        ->and(config('filesystems.disks.b2_private.region'))->toBe('us-east-005')
        ->and(config('filesystems.disks.b2_assets_private.region'))->toBe('us-east-005');
});

test('credential-check interactive prompt supports distinct assets credentials without leaking secrets', function () {
    $privSecret = 'mock-priv-secret-1111';
    $assetsSecret = 'mock-assets-secret-2222';

    $this->artisan('storage:probe-b2 --credential-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-priv-key-1234')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', $privSecret)
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'no')
        ->expectsQuestion('Masukkan B2 Assets Application Key ID:', 'mock-assets-key-9999')
        ->expectsQuestion('Masukkan B2 Assets Application Key (Secret):', $assetsSecret)
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-west-004')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-west-004.backblazeb2.com')
        ->assertSuccessful()
        ->doesntExpectOutputToContain($privSecret)
        ->doesntExpectOutputToContain($assetsSecret);

    expect(config('filesystems.disks.b2_private.region'))->toBe('us-east-005')
        ->and(config('filesystems.disks.b2_private.endpoint'))->toBe('https://s3.us-east-005.backblazeb2.com')
        ->and(config('filesystems.disks.b2_assets_private.region'))->toBe('us-west-004')
        ->and(config('filesystems.disks.b2_assets_private.endpoint'))->toBe('https://s3.us-west-004.backblazeb2.com');
});

test('credential validation rejects empty key id, empty secret, and invalid bucket names', function () {
    $command = new ProbeB2StorageCommand;

    // 1. Empty Key ID
    $errors1 = $command->validateCredentialInputs([
        'private_key_id' => '',
        'private_application_key' => 'valid-secret',
        'private_bucket' => 'sistem-data-wna-private',
        'private_region' => 'us-east-005',
        'private_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'assets_key_id' => 'valid-key',
        'assets_application_key' => 'valid-secret',
        'assets_bucket' => 'sistem-data-wna-assets',
        'assets_region' => 'us-east-005',
        'assets_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);
    expect($errors1)->toContain('Private Key ID tidak boleh kosong.');

    // 2. Empty Secret
    $errors2 = $command->validateCredentialInputs([
        'private_key_id' => 'valid-key',
        'private_application_key' => '',
        'private_bucket' => 'sistem-data-wna-private',
        'private_region' => 'us-east-005',
        'private_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'assets_key_id' => 'valid-key',
        'assets_application_key' => 'valid-secret',
        'assets_bucket' => 'sistem-data-wna-assets',
        'assets_region' => 'us-east-005',
        'assets_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);
    expect($errors2)->toContain('Private Application Key tidak boleh kosong.');

    // 3. Invalid Bucket Names
    $errors3 = $command->validateCredentialInputs([
        'private_key_id' => 'valid-key',
        'private_application_key' => 'valid-secret',
        'private_bucket' => 'wrong-private-bucket',
        'private_region' => 'us-east-005',
        'private_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'assets_key_id' => 'valid-key',
        'assets_application_key' => 'valid-secret',
        'assets_bucket' => 'wrong-assets-bucket',
        'assets_region' => 'us-east-005',
        'assets_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);
    expect($errors3)->toContain('Nama Private Bucket tidak valid (harus sistem-data-wna-private).')
        ->and($errors3)->toContain('Nama Assets Bucket tidak valid (harus sistem-data-wna-assets).');
});

test('credential validation rejects missing region, invalid endpoint, and control characters', function () {
    $command = new ProbeB2StorageCommand;

    // 1. Missing Region & Non-HTTPS Endpoint
    $errors = $command->validateCredentialInputs([
        'private_key_id' => 'valid-key',
        'private_application_key' => 'valid-secret',
        'private_bucket' => 'sistem-data-wna-private',
        'private_region' => '',
        'private_endpoint' => 'http://insecure-endpoint.com', // Must be https
        'assets_key_id' => 'valid-key',
        'assets_application_key' => 'valid-secret',
        'assets_bucket' => 'sistem-data-wna-assets',
        'assets_region' => '',
        'assets_endpoint' => 'not-a-url',
    ]);
    expect($errors)->toContain('Private Region tidak boleh kosong.')
        ->and($errors)->toContain('Private Endpoint harus berupa URL HTTPS yang valid.')
        ->and($errors)->toContain('Assets Region tidak boleh kosong.')
        ->and($errors)->toContain('Assets Endpoint harus berupa URL HTTPS yang valid.');

    // 2. Control characters in Key ID or Secret
    $controlCharErrors = $command->validateCredentialInputs([
        'private_key_id' => "valid\0key",
        'private_application_key' => "valid\nsecret\r",
        'private_bucket' => 'sistem-data-wna-private',
        'private_region' => 'us-east-005',
        'private_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'assets_key_id' => "assets\tkey",
        'assets_application_key' => 'valid-secret',
        'assets_bucket' => 'sistem-data-wna-assets',
        'assets_region' => 'us-east-005',
        'assets_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);
    expect($controlCharErrors)->toContain('Private Key ID memuat karakter kontrol ilegal.')
        ->and($controlCharErrors)->toContain('Private Application Key memuat karakter kontrol ilegal.')
        ->and($controlCharErrors)->toContain('Assets Key ID memuat karakter kontrol ilegal.');
});

test('credential validation accepts distinct valid regions and endpoints and rejects malformed region format', function () {
    $command = new ProbeB2StorageCommand;

    // Distinct valid regions & endpoints
    $validErrors = $command->validateCredentialInputs([
        'private_key_id' => 'valid-priv-key',
        'private_application_key' => 'valid-priv-secret',
        'private_bucket' => 'sistem-data-wna-private',
        'private_region' => 'us-east-005',
        'private_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'assets_key_id' => 'valid-assets-key',
        'assets_application_key' => 'valid-assets-secret',
        'assets_bucket' => 'sistem-data-wna-assets',
        'assets_region' => 'eu-central-003',
        'assets_endpoint' => 'https://s3.eu-central-003.backblazeb2.com',
    ]);
    expect($validErrors)->toBeEmpty();

    // Malformed regions
    $malformedErrors = $command->validateCredentialInputs([
        'private_key_id' => 'valid-priv-key',
        'private_application_key' => 'valid-priv-secret',
        'private_bucket' => 'sistem-data-wna-private',
        'private_region' => 'us east 005',
        'private_endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'assets_key_id' => 'valid-assets-key',
        'assets_application_key' => 'valid-assets-secret',
        'assets_bucket' => 'sistem-data-wna-assets',
        'assets_region' => 'eu_central_003!',
        'assets_endpoint' => 'https://s3.eu-central-003.backblazeb2.com',
    ]);
    expect($malformedErrors)->toContain('Format Private Region tidak valid.')
        ->and($malformedErrors)->toContain('Format Assets Region tidak valid.');
});

test('runner enforces database isolation to sqlite memory and does not connect to mysql', function () {
    $command = new ProbeB2StorageCommand;
    $command->enforceDatabaseIsolation();

    expect(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(config('database.default'))->not->toBe('mysql');
});

test('maskKeyId safely masks keys and never exposes secret', function () {
    $command = new ProbeB2StorageCommand;

    $masked = $command->maskKeyId('005abcdef1234567890');
    expect($masked)->toContain('005a')
        ->and($masked)->toContain('7890')
        ->and($masked)->toContain('***')
        ->and($masked)->not->toBe('005abcdef1234567890');

    expect($command->maskKeyId('short'))->toBe('*****');
});

test('probe manifest generates valid keys and strictly enforces probe prefix', function () {
    $manifest = new B2ProbeManifest('test-session-1');

    $privKey = $manifest->registerProbe('b2_private', 'private', 'uuid-1234', 'jpg');
    $assetKey = $manifest->registerProbe('b2_assets_private', 'assets', 'uuid-5678', 'png');

    expect($privKey)->toBe('wna/photos/__stage_4e_probe__/private-probe-uuid-1234.jpg')
        ->and($assetKey)->toBe('settings/icons/__stage_4e_probe__/asset-probe-uuid-5678.png')
        ->and($manifest->validateProbeKey($privKey))->toBeTrue()
        ->and($manifest->validateProbeKey($assetKey))->toBeTrue();
});

test('probe manifest rejects path traversal, disallowed directories, and wildcard characters', function () {
    $manifest = new B2ProbeManifest('test-session-2');

    // 1. Path traversal attempts
    expect($manifest->validateProbeKey('wna/photos/__stage_4e_probe__/../../etc/passwd'))->toBeFalse()
        ->and($manifest->validateProbeKey("wna/photos/__stage_4e_probe__/probe\0.jpg"))->toBeFalse()
        ->and($manifest->validateProbeKey('wna/photos/__stage_4e_probe__\\probe.jpg'))->toBeFalse();

    // 2. Disallowed base directory
    expect($manifest->validateProbeKey('other/photos/__stage_4e_probe__/probe.jpg'))->toBeFalse()
        ->and($manifest->validateProbeKey('app/photos/__stage_4e_probe__/probe.jpg'))->toBeFalse();

    // 3. Missing probe prefix
    expect($manifest->validateProbeKey('wna/photos/normal_user_photo.jpg'))->toBeFalse()
        ->and($manifest->validateProbeKey('settings/icons/logo.png'))->toBeFalse();

    // 4. Wildcards
    expect($manifest->validateProbeKey('wna/photos/__stage_4e_probe__/*'))->toBeFalse();
});

test('probe manifest tracking correctly records creation and cleanup status', function () {
    $manifest = new B2ProbeManifest('test-session-3');
    $key = $manifest->registerProbe('b2_private', 'private', 'uuid-test', 'jpg');

    // Not created yet -> not pending cleanup
    expect($manifest->getPendingCleanup())->toBeEmpty()
        ->and($manifest->hasResidual())->toBeFalse();

    // Mark created
    $manifest->markCreated($key);
    expect($manifest->getPendingCleanup())->toBe([$key])
        ->and($manifest->hasResidual())->toBeTrue();

    // Mark deleted
    $manifest->markDeleted($key);
    expect($manifest->getPendingCleanup())->toBeEmpty()
        ->and($manifest->hasResidual())->toBeFalse();
});

test('probe manifest refuses to mark created or deleted for unregistered keys', function () {
    $manifest = new B2ProbeManifest('test-session-4');

    expect(fn () => $manifest->markCreated('unregistered/key.jpg'))
        ->toThrow(RuntimeException::class, 'tidak terdaftar dalam sesi manifest ini');

    expect(fn () => $manifest->markDeleted('unregistered/key.jpg'))
        ->toThrow(RuntimeException::class, 'tidak terdaftar dalam sesi manifest ini');
});

test('connectivity-check aborts safely in non-interactive environment', function () {
    $command = new ProbeB2StorageCommand;
    $command->setLaravel(app());
    $input = new ArrayInput(['--connectivity-check' => true]);
    $input->setInteractive(false);
    $output = new BufferedOutput;

    $exitCode = $command->run($input, $output);

    expect($exitCode)->toBe(ProbeB2StorageCommand::FAILURE)
        ->and($output->fetch())->toContain('Pemeriksaan konektivitas memerlukan terminal interaktif. Dibatalkan secara aman.');
});

test('connectivity-check aborts safely when confirmation phrase is invalid or rejected', function () {
    $called = false;
    ProbeB2StorageCommand::setS3ClientFactory(function () use (&$called) {
        $called = true;
        throw new Exception('S3 client factory must not be invoked on wrong confirmation');
    });

    $this->artisan('storage:probe-b2 --connectivity-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-key-005123456789')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', 'secret')
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'yes')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsQuestion('Ketik frasa konfirmasi persis [CONNECT TO B2 READ-ONLY] untuk melanjutkan:', 'WRONG CONFIRMATION')
        ->assertFailed()
        ->expectsOutputToContain('Konfirmasi dibatalkan atau frasa tidak cocok. Tidak ada koneksi jaringan yang dibuat ke Backblaze B2.');

    expect($called)->toBeFalse();
});

test('connectivity-check executes HeadBucket only for both buckets independently and masks secrets', function () {
    $calledOperations = [];
    $mockSecret = 'super-secret-1234567890';

    $mockPrivateS3 = Mockery::mock(S3Client::class);
    $mockPrivateS3->shouldReceive('headBucket')
        ->with(['Bucket' => 'sistem-data-wna-private'])
        ->once()
        ->andReturnUsing(function () use (&$calledOperations) {
            $calledOperations[] = 'headBucket:sistem-data-wna-private';

            return new Result(['@metadata' => ['statusCode' => 200]]);
        });

    $mockAssetsS3 = Mockery::mock(S3Client::class);
    $mockAssetsS3->shouldReceive('headBucket')
        ->with(['Bucket' => 'sistem-data-wna-assets'])
        ->once()
        ->andReturnUsing(function () use (&$calledOperations) {
            $calledOperations[] = 'headBucket:sistem-data-wna-assets';

            return new Result(['@metadata' => ['statusCode' => 200]]);
        });

    $callIndex = 0;
    ProbeB2StorageCommand::setS3ClientFactory(function (array $config) use (&$callIndex, $mockPrivateS3, $mockAssetsS3) {
        expect($config['version'])->toBe('latest')
            ->and($config['signature_version'])->toBe('v4')
            ->and($config['use_path_style_endpoint'])->toBeFalse()
            ->and($config['http']['connect_timeout'])->toBe(5)
            ->and($config['http']['timeout'])->toBe(10)
            ->and($config['retries'])->toBe(1);

        $callIndex++;

        return $callIndex === 1 ? $mockPrivateS3 : $mockAssetsS3;
    });

    $this->artisan('storage:probe-b2 --connectivity-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-key-005123456789')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', $mockSecret)
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'yes')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsQuestion('Ketik frasa konfirmasi persis [CONNECT TO B2 READ-ONLY] untuk melanjutkan:', 'CONNECT TO B2 READ-ONLY')
        ->assertSuccessful()
        ->expectsOutputToContain('Frasa konfirmasi valid. Memulai pemeriksaan HeadBucket...')
        ->expectsOutputToContain('--- Private Bucket ---')
        ->expectsOutputToContain('--- Assets Bucket ---')
        ->expectsOutputToContain('Operation:      HeadBucket')
        ->expectsOutputToContain('Authentication: SUCCESS')
        ->expectsOutputToContain('Access:         SUCCESS')
        ->doesntExpectOutputToContain($mockSecret);

    expect($calledOperations)->toBe([
        'headBucket:sistem-data-wna-private',
        'headBucket:sistem-data-wna-assets',
    ]);
});

test('connectivity-check handles authentication failure and access denied independently without leaking secrets', function () {
    $mockPrivS3 = Mockery::mock(S3Client::class);
    $mockPrivS3->shouldReceive('headBucket')
        ->with(['Bucket' => 'sistem-data-wna-private'])
        ->once()
        ->andThrow(new S3Exception(
            'Forbidden',
            new Command('HeadBucket', ['Bucket' => 'sistem-data-wna-private']),
            ['response' => new Response(403)]
        ));

    $mockAssetsS3 = Mockery::mock(S3Client::class);
    $mockAssetsS3->shouldReceive('headBucket')
        ->with(['Bucket' => 'sistem-data-wna-assets'])
        ->once()
        ->andThrow(new S3Exception(
            'InvalidAccessKeyId',
            new Command('HeadBucket', ['Bucket' => 'sistem-data-wna-assets']),
            ['response' => new Response(401), 'code' => 'InvalidAccessKeyId']
        ));

    ProbeB2StorageCommand::setS3ClientFactory(function (array $config) use ($mockPrivS3, $mockAssetsS3) {
        if ($config['credentials']['key'] === 'mock-priv-key') {
            return $mockPrivS3;
        }

        return $mockAssetsS3;
    });

    $this->artisan('storage:probe-b2 --connectivity-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-priv-key')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', 'priv-secret')
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'no')
        ->expectsQuestion('Masukkan B2 Assets Application Key ID:', 'mock-assets-key')
        ->expectsQuestion('Masukkan B2 Assets Application Key (Secret):', 'assets-secret')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsQuestion('Ketik frasa konfirmasi persis [CONNECT TO B2 READ-ONLY] untuk melanjutkan:', 'CONNECT TO B2 READ-ONLY')
        ->assertFailed()
        ->expectsOutputToContain('Access Denied (HTTP 403)')
        ->expectsOutputToContain('Authentication Failure (HTTP 401)')
        ->doesntExpectOutputToContain('priv-secret')
        ->doesntExpectOutputToContain('assets-secret');
});

test('connectivity-check handles connection failure safely without raw exceptions', function () {
    $mockPrivS3 = Mockery::mock(S3Client::class);
    $mockPrivS3->shouldReceive('headBucket')
        ->with(['Bucket' => 'sistem-data-wna-private'])
        ->once()
        ->andThrow(new ConnectException(
            'cURL error 6: Could not resolve host (see https://curl.haxx.se/libcurl/c/libcurl-errors.html)',
            new Request('HEAD', 'https://s3.us-east-005.backblazeb2.com/sistem-data-wna-private')
        ));

    $mockAssetsS3 = Mockery::mock(S3Client::class);
    $mockAssetsS3->shouldReceive('headBucket')
        ->with(['Bucket' => 'sistem-data-wna-assets'])
        ->once()
        ->andReturn(new Result(['@metadata' => ['statusCode' => 200]]));

    $callIndex = 0;
    ProbeB2StorageCommand::setS3ClientFactory(function (array $config) use (&$callIndex, $mockPrivS3, $mockAssetsS3) {
        $callIndex++;

        return $callIndex === 1 ? $mockPrivS3 : $mockAssetsS3;
    });

    $this->artisan('storage:probe-b2 --connectivity-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-key-005123456789')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', 'secret')
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'yes')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsQuestion('Ketik frasa konfirmasi persis [CONNECT TO B2 READ-ONLY] untuk melanjutkan:', 'CONNECT TO B2 READ-ONLY')
        ->assertFailed()
        ->expectsOutputToContain('Connection / DNS / TLS Failure')
        ->expectsOutputToContain('Result:         SUCCESS') // Assets bucket succeeded
        ->doesntExpectOutputToContain('cURL error 6'); // Raw exception trace/details hidden
});

test('manifest rejects duplicate probe registration and prevents cleanup with mismatched disk', function () {
    $manifest = new B2ProbeManifest('test-session-dup');
    $key = $manifest->registerProbe('b2_private', 'private', 'uuid-dup-test', 'png');

    expect($manifest->getRegisteredDisk($key))->toBe('b2_private');

    // Duplicate registration throws InvalidArgumentException
    expect(fn () => $manifest->registerProbe('b2_private', 'private', 'uuid-dup-test', 'png'))
        ->toThrow(InvalidArgumentException::class, 'sudah pernah didaftarkan');

    $manifest->markCreated($key);

    // Mismatched disk cleanup throws RuntimeException
    expect(fn () => $manifest->markDeleted($key, 'b2_assets_private'))
        ->toThrow(RuntimeException::class, 'tidak sesuai dengan disk pendaftaran');

    // Correct disk cleanup succeeds
    $manifest->markDeleted($key, 'b2_private');
    expect($manifest->getPendingCleanup())->toBeEmpty();
});

test('manifest strictly rejects empty keys and keys with control characters or wildcards', function () {
    $manifest = new B2ProbeManifest('test-session-val');

    expect($manifest->validateProbeKey(''))->toBeFalse()
        ->and($manifest->validateProbeKey('   '))->toBeFalse()
        ->and($manifest->validateProbeKey("wna/photos/__stage_4e_probe__/probe\x00.png"))->toBeFalse()
        ->and($manifest->validateProbeKey("wna/photos/__stage_4e_probe__/probe\r.png"))->toBeFalse()
        ->and($manifest->validateProbeKey('wna/photos/__stage_4e_probe__/probe?.png'))->toBeFalse()
        ->and($manifest->validateProbeKey('wna/photos/__stage_4e_probe__/probe[1].png'))->toBeFalse()
        ->and($manifest->validateProbeKey('wna/photos/__stage_4e_probe__/*'))->toBeFalse();
});

test('io-check aborts safely in non-interactive environment without object operations', function () {
    $command = new ProbeB2StorageCommand;
    $command->setLaravel(app());
    $input = new ArrayInput(['--io-check' => true]);
    $input->setInteractive(false);
    $output = new BufferedOutput;

    $exitCode = $command->run($input, $output);

    expect($exitCode)->toBe(ProbeB2StorageCommand::FAILURE)
        ->and($output->fetch())->toContain('Pengujian I/O memerlukan terminal interaktif. Dibatalkan secara aman.');
});

test('io-check aborts safely when confirmation phrase is invalid without object operations', function () {
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');

    $this->artisan('storage:probe-b2 --io-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-key-005123456789')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', 'secret')
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'yes')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsQuestion('Ketik frasa konfirmasi persis [UPLOAD READ STREAM DELETE B2 PROBES] untuk melanjutkan:', 'WRONG PHRASE')
        ->assertFailed()
        ->expectsOutputToContain('Konfirmasi dibatalkan atau frasa tidak cocok. Tidak ada operasi objek yang dijalankan.');

    expect(Storage::disk('b2_private')->allFiles())->toBeEmpty()
        ->and(Storage::disk('b2_assets_private')->allFiles())->toBeEmpty();
});

test('io-check full workflow succeeds for both private and assets with fake storage without live network', function () {
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');
    $mockSecret = 'mock-secret-io-9876543210';

    // Mock S3ClientFactory for HeadBucket checks
    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')
        ->twice()
        ->andReturn(new Result(['@metadata' => ['statusCode' => 200]]));

    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $this->artisan('storage:probe-b2 --io-check')
        ->expectsQuestion('Masukkan B2 Private Application Key ID:', 'mock-key-005123456789')
        ->expectsQuestion('Masukkan B2 Private Application Key (Secret):', $mockSecret)
        ->expectsQuestion('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):', 'sistem-data-wna-private')
        ->expectsQuestion('Masukkan Private Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsConfirmation('Gunakan Application Key yang sama untuk Assets bucket?', 'yes')
        ->expectsQuestion('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):', 'sistem-data-wna-assets')
        ->expectsQuestion('Masukkan Assets Region (contoh: us-east-005):', 'us-east-005')
        ->expectsQuestion('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):', 'https://s3.us-east-005.backblazeb2.com')
        ->expectsQuestion('Ketik frasa konfirmasi persis [UPLOAD READ STREAM DELETE B2 PROBES] untuk melanjutkan:', 'UPLOAD READ STREAM DELETE B2 PROBES')
        ->assertSuccessful()
        ->expectsOutputToContain('Frasa konfirmasi valid. Memulai pengujian I/O terisolasi...')
        ->expectsOutputToContain('=== Private Bucket Probe [Disk: b2_private] ===')
        ->expectsOutputToContain('=== Assets Bucket Probe [Disk: b2_assets_private] ===')
        ->expectsOutputToContain('1. Connect:     PASS')
        ->expectsOutputToContain('2. Upload:      PASS')
        ->expectsOutputToContain('3. Read:        PASS')
        ->expectsOutputToContain('4. Stream:      PASS')
        ->expectsOutputToContain('5. Cleanup:     PASS')
        ->expectsOutputToContain('Overall Status: PASS')
        ->doesntExpectOutputToContain($mockSecret);

    // Assert that probe objects were cleanly removed post-test
    expect(Storage::disk('b2_private')->allFiles())->toBeEmpty()
        ->and(Storage::disk('b2_assets_private')->allFiles())->toBeEmpty();
});

test('executeBucketIoProbe detects upload failure and does not mark object as created', function () {
    $manifest = new B2ProbeManifest('test-session-up-fail');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(false);
    $mockStorage->shouldReceive('exists')->andReturn(false);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['connectivity'])->toBe('PASS')
        ->and($res['upload'])->toBe('FAIL')
        ->and($res['overall'])->toBe('FAIL')
        ->and($manifest->getPendingCleanup())->toBeEmpty();
});

test('executeBucketIoProbe handles upload exception where object exists and cleans it up to DELETED_CONFIRMED', function () {
    $manifest = new B2ProbeManifest('test-session-up-ex-clean');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andThrow(new RuntimeException('Network timeout during upload write'));
    // 1st exists(): called after upload exception (detects object is present on disk)
    // 2nd exists(): called post-delete in finally block (verifies object is now gone)
    $mockStorage->shouldReceive('exists')->twice()->andReturn(true, false);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(true);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('FAIL')
        ->and($res['cleanup_status'])->toBe('DELETED_CONFIRMED')
        ->and($res['cleanup'])->toBe('PASS')
        ->and($res['overall'])->toBe('FAIL')
        ->and($manifest->hasResidual())->toBeFalse();
});

test('executeBucketIoProbe handles upload exception where existence check also throws exception resulting in CLEANUP_UNVERIFIED', function () {
    $manifest = new B2ProbeManifest('test-session-up-ex-unverified');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andThrow(new RuntimeException('Network timeout during upload write'));
    // 1st exists(): throws exception during defensive check
    // In finally: deletePrivate throws or returns, and 2nd exists() also throws
    $mockStorage->shouldReceive('exists')->twice()->andThrow(new RuntimeException('Network unreachable during existence check'));
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(false);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('FAIL')
        ->and($res['cleanup_status'])->toBe('CLEANUP_UNVERIFIED')
        ->and($res['cleanup'])->toBe('FAIL')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['residual_key'])->not->toBeNull()
        ->and($manifest->hasResidual())->toBeTrue();
});

test('executeBucketIoProbe handles read failure by aborting to cleanup and confirming deletion', function () {
    $manifest = new B2ProbeManifest('test-session-read-fail');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    // readStream returns null / fails to open resource
    $mockStorage->shouldReceive('readStream')->once()->andReturn(null);
    // Cleanup in finally: delete succeeds, post-delete exists is false
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(true);
    $mockStorage->shouldReceive('exists')->once()->andReturn(false);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['read'])->toBe('FAIL')
        ->and($res['stream'])->toBe('FAIL')
        ->and($res['cleanup_status'])->toBe('DELETED_CONFIRMED')
        ->and($res['cleanup'])->toBe('PASS')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['error'])->toContain('Gagal membaca stream berkas probe dari storage service.')
        ->and($manifest->hasResidual())->toBeFalse();
});

test('executeBucketIoProbe handles stream response failure by triggering cleanup to DELETED_CONFIRMED', function () {
    $manifest = new B2ProbeManifest('test-session-stream-fail');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('readStream')->once()->andReturnUsing(function () {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, 'test-png-data');
        rewind($fp);

        return $fp;
    });
    // streamFile throws exception during response callback execution
    $mockStorage->shouldReceive('streamFile')->once()->andReturn(response()->stream(function () {
        throw new RuntimeException('Connection aborted during binary stream transmission');
    }, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'inline', 'X-Content-Type-Options' => 'nosniff']));

    // Cleanup in finally succeeds
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(true);
    $mockStorage->shouldReceive('exists')->once()->andReturn(false);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['read'])->toBe('PASS')
        ->and($res['stream'])->toBe('FAIL')
        ->and($res['cleanup_status'])->toBe('DELETED_CONFIRMED')
        ->and($res['cleanup'])->toBe('PASS')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['error'])->toContain('Kegagalan eksekusi callback response streaming')
        ->and($manifest->hasResidual())->toBeFalse();
});

test('executeBucketIoProbe detects byte corruption mismatch and executes cleanup', function () {
    $manifest = new B2ProbeManifest('test-session-byte-mismatch');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    // readStream returns corrupted data
    $mockStorage->shouldReceive('readStream')->once()->andReturnUsing(function () {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, 'corrupted-payload-different-bytes');
        rewind($fp);

        return $fp;
    });

    // Cleanup in finally succeeds
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(true);
    $mockStorage->shouldReceive('exists')->once()->andReturn(false);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['read'])->toBe('FAIL')
        ->and($res['stream'])->toBe('FAIL')
        ->and($res['cleanup_status'])->toBe('DELETED_CONFIRMED')
        ->and($res['cleanup'])->toBe('PASS')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['error'])->toContain('Ketidaksesuaian integritas byte')
        ->and($manifest->hasResidual())->toBeFalse();
});

test('executeBucketIoProbe marks CLEANUP_UNVERIFIED when post-delete exists check throws exception', function () {
    $manifest = new B2ProbeManifest('test-session-post-del-ex');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('readStream')->once()->andReturnUsing(function () {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, 'test-png-data');
        rewind($fp);

        return $fp;
    });
    $mockStorage->shouldReceive('streamFile')->once()->andReturn(response()->stream(function () {
        echo 'test-png-data';
    }, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'inline', 'X-Content-Type-Options' => 'nosniff']));

    // deletePrivate succeeds, but post-delete exists() throws exception (network glitch)
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(true);
    $mockStorage->shouldReceive('exists')->once()->andThrow(new RuntimeException('Network timeout during HeadObject existence check'));

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['read'])->toBe('PASS')
        ->and($res['stream'])->toBe('PASS')
        ->and($res['cleanup_status'])->toBe('CLEANUP_UNVERIFIED')
        ->and($res['cleanup'])->toBe('FAIL')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['residual_key'])->not->toBeNull()
        ->and($res['error'])->toContain('Verifikasi keberadaan pasca-delete gagal')
        ->and($manifest->hasResidual())->toBeTrue();
});

test('executeBucketIoProbe marks RESIDUAL_CONFIRMED when delete returns false and object still exists', function () {
    $manifest = new B2ProbeManifest('test-session-del-fail-res');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('readStream')->once()->andReturnUsing(function () {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, 'test-png-data');
        rewind($fp);

        return $fp;
    });
    $mockStorage->shouldReceive('streamFile')->once()->andReturn(response()->stream(function () {
        echo 'test-png-data';
    }, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'inline', 'X-Content-Type-Options' => 'nosniff']));

    // deletePrivate returns false, and exists() confirms object is still there
    $mockStorage->shouldReceive('deletePrivate')->once()->andReturn(false);
    $mockStorage->shouldReceive('exists')->once()->andReturn(true);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['read'])->toBe('PASS')
        ->and($res['stream'])->toBe('PASS')
        ->and($res['cleanup_status'])->toBe('RESIDUAL_CONFIRMED')
        ->and($res['cleanup'])->toBe('FAIL')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['residual_key'])->not->toBeNull()
        ->and($manifest->hasResidual())->toBeTrue();
});

test('executeBucketIoProbe marks RESIDUAL_CONFIRMED when delete throws exception but object is confirmed present', function () {
    $manifest = new B2ProbeManifest('test-session-del-ex-res');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('readStream')->once()->andReturnUsing(function () {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, 'test-png-data');
        rewind($fp);

        return $fp;
    });
    $mockStorage->shouldReceive('streamFile')->once()->andReturn(response()->stream(function () {
        echo 'test-png-data';
    }, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'inline', 'X-Content-Type-Options' => 'nosniff']));

    // deletePrivate throws exception, but exists() succeeds and returns true (still present!)
    $mockStorage->shouldReceive('deletePrivate')->once()->andThrow(new RuntimeException('Network connection severed'));
    $mockStorage->shouldReceive('exists')->once()->andReturn(true);

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['cleanup_status'])->toBe('RESIDUAL_CONFIRMED')
        ->and($res['cleanup'])->toBe('FAIL')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['residual_key'])->not->toBeNull()
        ->and($res['error'])->toContain('Delete exception: Network connection severed')
        ->and($manifest->hasResidual())->toBeTrue();
});

test('executeBucketIoProbe marks CLEANUP_UNVERIFIED when delete throws exception and exists check also fails', function () {
    $manifest = new B2ProbeManifest('test-session-del-ex-unverified');

    $mockS3 = Mockery::mock(S3Client::class);
    $mockS3->shouldReceive('headBucket')->once()->andReturn(new Result);
    ProbeB2StorageCommand::setS3ClientFactory(fn () => $mockS3);

    $mockStorage = Mockery::mock(DocumentStorageService::class);
    $mockStorage->shouldReceive('isPathAllowed')->andReturn(true);
    $mockStorage->shouldReceive('storeProbeFile')->once()->andReturn(true);
    $mockStorage->shouldReceive('getPrivateDisk')->andReturn('b2_private');
    $mockStorage->shouldReceive('readStream')->once()->andReturnUsing(function () {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, 'test-png-data');
        rewind($fp);

        return $fp;
    });
    $mockStorage->shouldReceive('streamFile')->once()->andReturn(response()->stream(function () {
        echo 'test-png-data';
    }, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'inline', 'X-Content-Type-Options' => 'nosniff']));

    // deletePrivate throws exception AND exists() throws exception
    $mockStorage->shouldReceive('deletePrivate')->once()->andThrow(new RuntimeException('Network connection severed during delete'));
    $mockStorage->shouldReceive('exists')->once()->andThrow(new RuntimeException('Network connection severed during exists'));

    $command = new ProbeB2StorageCommand;
    $res = $command->executeBucketIoProbe(
        params: [
            'disk' => 'b2_private',
            'module' => 'private',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'key_id' => 'k',
            'secret' => 's',
            'use_path_style' => false,
        ],
        payload: 'test-png-data',
        manifest: $manifest,
        storageService: $mockStorage
    );

    expect($res['upload'])->toBe('PASS')
        ->and($res['cleanup_status'])->toBe('CLEANUP_UNVERIFIED')
        ->and($res['cleanup'])->toBe('FAIL')
        ->and($res['overall'])->toBe('FAIL')
        ->and($res['residual_key'])->not->toBeNull()
        ->and($res['error'])->toContain('Verifikasi keberadaan pasca-delete gagal')
        ->and($manifest->hasResidual())->toBeTrue();
});

test('B2ProbeManifest strictly tracks explicit keys without wildcard or directory deletion', function () {
    $manifest = new B2ProbeManifest('manifest-strict-test');

    $key = $manifest->registerProbe('b2_private', 'private', (string) Str::uuid(), 'png');
    $manifest->markCreated($key);

    expect($manifest->getPendingCleanup())->toBe([$key])
        ->and($manifest->hasResidual())->toBeTrue();

    // Check class methods to ensure no wildcard or listing methods exist
    $refClass = new ReflectionClass($manifest);
    $methodNames = array_map(fn ($m) => $m->getName(), $refClass->getMethods());

    expect($methodNames)->not->toContain('allFiles')
        ->and($methodNames)->not->toContain('deleteDirectory')
        ->and($methodNames)->not->toContain('deleteAll');

    $manifest->markDeleted($key, 'b2_private');
    expect($manifest->getPendingCleanup())->toBeEmpty()
        ->and($manifest->hasResidual())->toBeFalse()
        ->and($manifest->getRecords()[$key]['deleted'])->toBeTrue();
});

describe('DocumentStorageService storeProbeFile validation', function () {
    beforeEach(function () {
        config(['filesystems.backend' => 'backblaze']);
        config([
            'filesystems.disks.b2_private' => [
                'driver' => 's3',
                'bucket' => 'sistem-data-wna-private',
                'region' => 'us-east-005',
                'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
                'key' => 'mock-key',
                'secret' => 'mock-secret',
            ],
            'filesystems.disks.b2_assets_private' => [
                'driver' => 's3',
                'bucket' => 'sistem-data-wna-assets',
                'region' => 'us-east-005',
                'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
                'key' => 'mock-key',
                'secret' => 'mock-secret',
            ],
        ]);
    });

    test('storeProbeFile method exists and rejects unauthorized disk', function () {
        $service = new DocumentStorageService;

        expect(method_exists($service, 'storeProbeFile'))->toBeTrue();

        expect(fn () => $service->storeProbeFile('unauthorized_disk', 'wna/photos/__stage_4e_probe__/test.png', 'data'))
            ->toThrow(InvalidArgumentException::class, 'tidak diizinkan untuk penyimpanan berkas probe');
    });

    test('storeProbeFile rejects path traversal, null-byte, backslash, wildcards, and invalid characters', function (string $invalidPath) {
        $service = new DocumentStorageService;

        expect(fn () => $service->storeProbeFile('b2_private', $invalidPath, 'data'))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'traversal' => 'wna/photos/__stage_4e_probe__/../secret.png',
        'null-byte' => "wna/photos/__stage_4e_probe__/test\0.png",
        'backslash' => 'wna/photos/__stage_4e_probe__\\test.png',
        'wildcard-asterisk' => 'wna/photos/__stage_4e_probe__/*.png',
        'wildcard-question' => 'wna/photos/__stage_4e_probe__/?.png',
        'control-character' => "wna/photos/__stage_4e_probe__/\x07test.png",
    ]);

    test('storeProbeFile rejects path missing probe isolation prefix', function () {
        $service = new DocumentStorageService;

        expect(fn () => $service->storeProbeFile('b2_private', 'wna/photos/regular_upload.png', 'data'))
            ->toThrow(InvalidArgumentException::class, 'prefix isolasi');
    });

    test('storeProbeFile rejects disk swap between private and assets', function () {
        $service = new DocumentStorageService;

        expect(fn () => $service->storeProbeFile('b2_private', 'settings/icons/__stage_4e_probe__/icon.png', 'data'))
            ->toThrow(InvalidArgumentException::class, "Path probe untuk private disk wajib diawali dengan 'wna/photos/__stage_4e_probe__/'.");

        expect(fn () => $service->storeProbeFile('b2_assets_private', 'wna/photos/__stage_4e_probe__/photo.png', 'data'))
            ->toThrow(InvalidArgumentException::class, "Path probe untuk assets disk wajib diawali dengan 'settings/icons/__stage_4e_probe__/'.");
    });

    test('storeProbeFile rejects path outside managed directories allowlist', function () {
        $service = new DocumentStorageService;

        expect(fn () => $service->storeProbeFile('b2_private', 'system/passwords/__stage_4e_probe__/secret.txt', 'data'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('storeProbeFile successfully stores valid probe file to active disk', function () {
        Storage::fake('b2_private');
        $service = new DocumentStorageService;

        $path = 'wna/photos/__stage_4e_probe__/probe-test.png';
        $result = $service->storeProbeFile('b2_private', $path, 'valid-binary-png-data');

        expect($result)->toBeTrue();
        expect(Storage::disk('b2_private')->exists($path))->toBeTrue();
        expect(Storage::disk('b2_private')->get($path))->toBe('valid-binary-png-data');
    });
});
