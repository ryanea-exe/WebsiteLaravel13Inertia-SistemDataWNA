<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

test('default filesystem disk remains local for MAMP preservation', function () {
    expect(config('filesystems.default'))->toBe('local');
});

test('local and public disks configuration remain intact for MAMP preservation', function () {
    $disks = config('filesystems.disks');

    expect($disks)->toHaveKey('local')
        ->and($disks)->toHaveKey('public')
        ->and($disks['local']['driver'])->toBe('local')
        ->and($disks['local']['root'])->toBe(storage_path('app/private'))
        ->and($disks['public']['driver'])->toBe('local')
        ->and($disks['public']['root'])->toBe(storage_path('app/public'))
        ->and($disks['public']['visibility'])->toBe('public');
});

test('both b2_private and b2_assets_private disks are registered in filesystems configuration', function () {
    $disks = config('filesystems.disks');

    expect($disks)->toHaveKey('b2_private')
        ->and($disks)->toHaveKey('b2_assets_private');
});

test('both b2 disks use s3 driver and strictly private visibility', function () {
    $b2Private = config('filesystems.disks.b2_private');
    $b2Assets = config('filesystems.disks.b2_assets_private');

    expect($b2Private['driver'])->toBe('s3')
        ->and($b2Private['visibility'])->toBe('private')
        ->and($b2Private)->not->toHaveKey('url')
        ->and($b2Assets['driver'])->toBe('s3')
        ->and($b2Assets['visibility'])->toBe('private')
        ->and($b2Assets)->not->toHaveKey('url');
});

test('b2_private and b2_assets_private use independent environment configurations', function () {
    config([
        'filesystems.disks.b2_private.key' => 'private-test-key-id',
        'filesystems.disks.b2_private.secret' => 'private-test-app-key',
        'filesystems.disks.b2_private.region' => 'us-east-005',
        'filesystems.disks.b2_private.bucket' => 'kanim-ponorogo-private-bucket',
        'filesystems.disks.b2_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'filesystems.disks.b2_private.use_path_style_endpoint' => false,

        'filesystems.disks.b2_assets_private.key' => 'assets-test-key-id',
        'filesystems.disks.b2_assets_private.secret' => 'assets-test-app-key',
        'filesystems.disks.b2_assets_private.region' => 'us-west-004',
        'filesystems.disks.b2_assets_private.bucket' => 'kanim-ponorogo-assets-bucket',
        'filesystems.disks.b2_assets_private.endpoint' => 'https://s3.us-west-004.backblazeb2.com',
        'filesystems.disks.b2_assets_private.use_path_style_endpoint' => true,
    ]);

    $private = config('filesystems.disks.b2_private');
    $assets = config('filesystems.disks.b2_assets_private');

    expect($private['key'])->toBe('private-test-key-id')
        ->and($private['bucket'])->toBe('kanim-ponorogo-private-bucket')
        ->and($private['endpoint'])->toBe('https://s3.us-east-005.backblazeb2.com')
        ->and($private['use_path_style_endpoint'])->toBeFalse()
        ->and($assets['key'])->toBe('assets-test-key-id')
        ->and($assets['bucket'])->toBe('kanim-ponorogo-assets-bucket')
        ->and($assets['endpoint'])->toBe('https://s3.us-west-004.backblazeb2.com')
        ->and($assets['use_path_style_endpoint'])->toBeTrue()
        ->and($private['key'])->not->toBe($assets['key'])
        ->and($private['bucket'])->not->toBe($assets['bucket']);
});

test('flysystem s3 driver can instantiate b2 disk adapters with dummy configuration without network calls', function () {
    config([
        'filesystems.disks.b2_private.key' => 'dummy-key',
        'filesystems.disks.b2_private.secret' => 'dummy-secret',
        'filesystems.disks.b2_private.region' => 'us-east-005',
        'filesystems.disks.b2_private.bucket' => 'dummy-private-bucket',
        'filesystems.disks.b2_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',

        'filesystems.disks.b2_assets_private.key' => 'dummy-key',
        'filesystems.disks.b2_assets_private.secret' => 'dummy-secret',
        'filesystems.disks.b2_assets_private.region' => 'us-east-005',
        'filesystems.disks.b2_assets_private.bucket' => 'dummy-assets-bucket',
        'filesystems.disks.b2_assets_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);

    // Verifies that league/flysystem-aws-s3-v3 is installed and resolves the S3 driver correctly
    $privateDisk = Storage::disk('b2_private');
    $assetsDisk = Storage::disk('b2_assets_private');

    expect($privateDisk)->toBeInstanceOf(FilesystemAdapter::class)
        ->and($assetsDisk)->toBeInstanceOf(FilesystemAdapter::class);
});
