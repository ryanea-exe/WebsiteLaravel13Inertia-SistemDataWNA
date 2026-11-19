<?php

use App\Console\Commands\CheckB2ConnectivityCommand;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3ClientInterface;

beforeEach(function () {
    CheckB2ConnectivityCommand::setS3ClientFactory(null);

    // Setup safe dummy testing config in memory
    config([
        'filesystems.disks.b2_private' => [
            'driver' => 's3',
            'key' => 'dummy-private-key-id-12345',
            'secret' => 'dummy-private-secret-67890',
            'bucket' => 'sistem-data-wna-private',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'use_path_style_endpoint' => false,
        ],
        'filesystems.disks.b2_assets_private' => [
            'driver' => 's3',
            'key' => 'dummy-assets-key-id-abcde',
            'secret' => 'dummy-assets-secret-fghij',
            'bucket' => 'sistem-data-wna-assets',
            'region' => 'us-east-005',
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'use_path_style_endpoint' => false,
        ],
    ]);
});

afterEach(function () {
    CheckB2ConnectivityCommand::setS3ClientFactory(null);
});

test('both buckets pass connectivity check using mocked s3 clients', function () {
    $calls = [];

    CheckB2ConnectivityCommand::setS3ClientFactory(function (array $config) use (&$calls) {
        $mock = Mockery::mock(S3ClientInterface::class);
        $mock->shouldReceive('listObjectsV2')
            ->once()
            ->andReturnUsing(function (array $args) use (&$calls, $config) {
                $calls[] = [
                    'config' => $config,
                    'args' => $args,
                ];

                return new Result(['KeyCount' => 0]);
            });

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->expectsOutputToContain('Private Bucket (b2_private)')
        ->expectsOutputToContain('Assets Bucket (b2_assets_private)')
        ->expectsOutputToContain('PASS')
        ->assertExitCode(0);

    expect($calls)->toHaveCount(2);

    // 1. Private bucket assertions
    expect($calls[0]['args']['Bucket'])->toBe('sistem-data-wna-private')
        ->and($calls[0]['args']['Prefix'])->toBe('__stage_4e6_connectivity_check__/')
        ->and($calls[0]['args']['MaxKeys'])->toBe(1);

    // 2. Assets bucket assertions
    expect($calls[1]['args']['Bucket'])->toBe('sistem-data-wna-assets')
        ->and($calls[1]['args']['Prefix'])->toBe('settings/icons/__stage_4e6_connectivity_check__/')
        ->and($calls[1]['args']['MaxKeys'])->toBe(1);
});

test('verifies command strictly calls listObjectsV2 and never calls headBucket, putObject, getObject or deleteObject', function () {
    CheckB2ConnectivityCommand::setS3ClientFactory(function () {
        $mock = Mockery::mock(S3ClientInterface::class);

        // Expect ONLY listObjectsV2
        $mock->shouldReceive('listObjectsV2')->once()->andReturn(new Result(['KeyCount' => 0]));

        // Prohibited methods must never be called
        $mock->shouldNotReceive('headBucket');
        $mock->shouldNotReceive('putObject');
        $mock->shouldNotReceive('getObject');
        $mock->shouldNotReceive('headObject');
        $mock->shouldNotReceive('deleteObject');
        $mock->shouldNotReceive('deleteObjects');
        $mock->shouldNotReceive('copyObject');
        $mock->shouldNotReceive('listBuckets');

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->assertExitCode(0);
});

test('private bucket failure does not abort assets bucket verification and assets failure does not erase private pass', function () {
    $callIndex = 0;

    CheckB2ConnectivityCommand::setS3ClientFactory(function () use (&$callIndex) {
        $callIndex++;
        $mock = Mockery::mock(S3ClientInterface::class);

        if ($callIndex === 1) {
            // Private bucket fails with AccessDenied
            $mockCommand = Mockery::mock(CommandInterface::class);
            $s3Exception = new S3Exception('Access Denied', $mockCommand, [
                'code' => 'AccessDenied',
                'statusCode' => 403,
            ]);

            $mock->shouldReceive('listObjectsV2')->once()->andThrow($s3Exception);
        } else {
            // Assets bucket succeeds
            $mock->shouldReceive('listObjectsV2')->once()->andReturn(new Result(['KeyCount' => 0]));
        }

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->expectsOutputToContain('FAIL [ACCESS_DENIED]')
        ->expectsOutputToContain('Assets Bucket')
        ->expectsOutputToContain('PASS')
        ->assertExitCode(1);

    expect($callIndex)->toBe(2);
});

test('assets bucket failure preserves private bucket pass in summary table', function () {
    $callIndex = 0;

    CheckB2ConnectivityCommand::setS3ClientFactory(function () use (&$callIndex) {
        $callIndex++;
        $mock = Mockery::mock(S3ClientInterface::class);

        if ($callIndex === 1) {
            // Private bucket succeeds
            $mock->shouldReceive('listObjectsV2')->once()->andReturn(new Result(['KeyCount' => 0]));
        } else {
            // Assets bucket fails with Authentication failure
            $mockCommand = Mockery::mock(CommandInterface::class);
            $s3Exception = new S3Exception('Invalid Key', $mockCommand, [
                'code' => 'InvalidAccessKeyId',
                'statusCode' => 401,
            ]);

            $mock->shouldReceive('listObjectsV2')->once()->andThrow($s3Exception);
        }

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->expectsOutputToContain('FAIL [AUTHENTICATION_FAILURE]')
        ->assertExitCode(1);

    expect($callIndex)->toBe(2);
});

test('missing configuration is handled gracefully without exception and marked as MISSING_CONFIGURATION', function () {
    // Clear key and secret in private disk
    config([
        'filesystems.disks.b2_private.key' => '',
        'filesystems.disks.b2_private.secret' => null,
    ]);

    CheckB2ConnectivityCommand::setS3ClientFactory(function () {
        $mock = Mockery::mock(S3ClientInterface::class);
        $mock->shouldReceive('listObjectsV2')->once()->andReturn(new Result(['KeyCount' => 0]));

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->expectsOutputToContain('FAIL [MISSING_CONFIGURATION]')
        ->expectsOutputToContain('Parameter konfigurasi belum lengkap: key, secret')
        ->assertExitCode(1);
});

test('access denied error message is sanitized and does not leak raw exception, key ID or secret', function () {
    CheckB2ConnectivityCommand::setS3ClientFactory(function () {
        $mock = Mockery::mock(S3ClientInterface::class);
        $mockCommand = Mockery::mock(CommandInterface::class);
        $s3Exception = new S3Exception('AWS Error: AccessDenied for Key dummy-private-key-id-12345 and secret dummy-private-secret-67890 in Authorization header Bearer xyz', $mockCommand, [
            'code' => 'AccessDenied',
            'statusCode' => 403,
        ]);

        $mock->shouldReceive('listObjectsV2')->andThrow($s3Exception);

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->doesntExpectOutputToContain('dummy-private-key-id-12345')
        ->doesntExpectOutputToContain('dummy-private-secret-67890')
        ->doesntExpectOutputToContain('Authorization header')
        ->expectsOutputToContain('Akses ditolak: Kredensial tidak memiliki hak akses \'listFiles\'')
        ->assertExitCode(1);
});

test('endpoint or region mismatch (NoSuchBucket) is properly classified', function () {
    CheckB2ConnectivityCommand::setS3ClientFactory(function () {
        $mock = Mockery::mock(S3ClientInterface::class);
        $mockCommand = Mockery::mock(CommandInterface::class);
        $s3Exception = new S3Exception('Bucket not found', $mockCommand, [
            'code' => 'NoSuchBucket',
            'statusCode' => 404,
        ]);

        $mock->shouldReceive('listObjectsV2')->andThrow($s3Exception);

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->expectsOutputToContain('FAIL [ENDPOINT_REGION_MISMATCH]')
        ->expectsOutputToContain('tidak ditemukan pada region/endpoint')
        ->assertExitCode(1);
});

test('network or connection timeout is properly classified as NETWORK_TIMEOUT', function () {
    CheckB2ConnectivityCommand::setS3ClientFactory(function () {
        $mock = Mockery::mock(S3ClientInterface::class);
        $mockCommand = Mockery::mock(CommandInterface::class);
        $s3Exception = new S3Exception('Connection timed out', $mockCommand, [
            'connection_error' => true,
        ]);

        $mock->shouldReceive('listObjectsV2')->andThrow($s3Exception);

        return $mock;
    });

    $this->artisan('b2:connectivity-check')
        ->expectsOutputToContain('FAIL [NETWORK_TIMEOUT]')
        ->expectsOutputToContain('Koneksi jaringan gagal atau timeout')
        ->assertExitCode(1);
});
