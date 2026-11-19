<?php

namespace App\Console\Commands;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Closure;
use Illuminate\Console\Command;

class CheckB2ConnectivityCommand extends Command
{
    /**
     * Dedicated prefixes for connectivity verification.
     */
    public const PRIVATE_CHECK_PREFIX = '__stage_4e6_connectivity_check__/';

    public const ASSETS_CHECK_PREFIX = 'settings/icons/__stage_4e6_connectivity_check__/';

    /**
     * Optional custom S3Client factory for automated testing.
     *
     * @var (Closure(array<string, mixed>): S3ClientInterface)|null
     */
    protected static ?Closure $s3ClientFactory = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'b2:connectivity-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform secure read-only connectivity and permission verification on Backblaze B2 buckets using ListObjectsV2';

    /**
     * Set a custom S3 client factory for testing.
     *
     * @param  (Closure(array<string, mixed>): S3ClientInterface)|null  $factory
     */
    public static function setS3ClientFactory(?Closure $factory): void
    {
        self::$s3ClientFactory = $factory;
    }

    /**
     * Create an S3 client instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function createS3Client(array $config): S3ClientInterface
    {
        if (self::$s3ClientFactory !== null) {
            return (self::$s3ClientFactory)($config);
        }

        return new S3Client($config);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  STAGE 4E.6 — BACKBLAZE B2 SECURE CONNECTIVITY VERIFICATION   ');
        $this->info('================================================================');
        $this->line('Metode Pemeriksaan: <fg=cyan>S3 ListObjectsV2 (MaxKeys: 1)</>');
        $this->line('Prinsip Keamanan:   <fg=yellow>Zero Object I/O, No HeadBucket, Redacted Secrets</>');
        $this->newLine();

        // 1. Check Private Bucket (b2_private)
        $this->line('1. Memeriksa Private Bucket [b2_private]...');
        $privateResult = $this->verifyBucket(
            diskName: 'b2_private',
            prefix: self::PRIVATE_CHECK_PREFIX
        );

        $this->renderBucketResult('Private Bucket', $privateResult);
        $this->newLine();

        // 2. Check Assets Bucket (b2_assets_private)
        $this->line('2. Memeriksa Assets Bucket [b2_assets_private]...');
        $assetsResult = $this->verifyBucket(
            diskName: 'b2_assets_private',
            prefix: self::ASSETS_CHECK_PREFIX
        );

        $this->renderBucketResult('Assets Bucket', $assetsResult);
        $this->newLine();

        // 3. Render Summary Table
        $this->info('================================================================');
        $this->info('                     RINGKASAN HASIL                            ');
        $this->info('================================================================');

        $rows = [
            [
                'Private Bucket (b2_private)',
                $privateResult['bucket'],
                $privateResult['passed'] ? '<fg=green;options=bold>PASS</>' : '<fg=red;options=bold>FAIL</>',
                $privateResult['status_category'],
            ],
            [
                'Assets Bucket (b2_assets_private)',
                $assetsResult['bucket'],
                $assetsResult['passed'] ? '<fg=green;options=bold>PASS</>' : '<fg=red;options=bold>FAIL</>',
                $assetsResult['status_category'],
            ],
        ];

        $this->table(['Target Disk', 'Bucket Name', 'Status', 'Keterangan'], $rows);
        $this->newLine();

        if ($privateResult['passed'] && $assetsResult['passed']) {
            $this->info('Konektivitas dan izin listFiles pada kedua bucket Backblaze B2 berhasil diverifikasi.');

            return self::SUCCESS;
        }

        $this->error('Pemeriksaan konektivitas gagal pada satu atau lebih bucket. Periksa keterangan di atas.');

        return self::FAILURE;
    }

    /**
     * Verify a specific bucket via isolated ListObjectsV2.
     *
     * @return array{
     *     passed: bool,
     *     bucket: string,
     *     prefix: string,
     *     status_category: string,
     *     error_message: string|null
     * }
     */
    protected function verifyBucket(string $diskName, string $prefix): array
    {
        $config = config("filesystems.disks.{$diskName}");

        if (! is_array($config)) {
            return [
                'passed' => false,
                'bucket' => '-',
                'prefix' => $prefix,
                'status_category' => 'MISSING_CONFIGURATION',
                'error_message' => "Konfigurasi disk [{$diskName}] tidak ditemukan pada filesystems.",
            ];
        }

        // Validate required configuration fields
        $required = ['key', 'secret', 'bucket', 'region', 'endpoint'];
        $missing = [];
        foreach ($required as $field) {
            $val = $config[$field] ?? null;
            if ($val === null || trim((string) $val) === '') {
                $missing[] = $field;
            }
        }

        $bucket = (string) ($config['bucket'] ?? '-');

        if (! empty($missing)) {
            return [
                'passed' => false,
                'bucket' => $bucket,
                'prefix' => $prefix,
                'status_category' => 'MISSING_CONFIGURATION',
                'error_message' => 'Parameter konfigurasi belum lengkap: '.implode(', ', $missing),
            ];
        }

        $clientConfig = [
            'version' => 'latest',
            'region' => (string) $config['region'],
            'endpoint' => (string) $config['endpoint'],
            'use_path_style_endpoint' => (bool) ($config['use_path_style_endpoint'] ?? false),
            'credentials' => [
                'key' => (string) $config['key'],
                'secret' => (string) $config['secret'],
            ],
        ];

        try {
            $client = $this->createS3Client($clientConfig);

            // Execute strictly ListObjectsV2 with MaxKeys 1
            $client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => $prefix,
                'MaxKeys' => 1,
            ]);

            return [
                'passed' => true,
                'bucket' => $bucket,
                'prefix' => $prefix,
                'status_category' => 'CONNECTED_LIST_ALLOWED',
                'error_message' => null,
            ];
        } catch (S3Exception $e) {
            $awsCode = $e->getAwsErrorCode();
            $statusCode = $e->getStatusCode();

            if ($awsCode === 'InvalidAccessKeyId' || $awsCode === 'SignatureDoesNotMatch' || $statusCode === 401) {
                $category = 'AUTHENTICATION_FAILURE';
                $message = 'Autentikasi gagal: Key ID atau Application Key Secret tidak valid.';
            } elseif ($awsCode === 'AccessDenied' || $statusCode === 403) {
                $category = 'ACCESS_DENIED';
                $message = "Akses ditolak: Kredensial tidak memiliki hak akses 'listFiles' pada prefix [{$prefix}].";
            } elseif ($awsCode === 'NoSuchBucket' || $statusCode === 404) {
                $category = 'ENDPOINT_REGION_MISMATCH';
                $message = "Bucket [{$bucket}] tidak ditemukan pada region/endpoint yang dikonfigurasi.";
            } elseif ($e->isConnectionError()) {
                $category = 'NETWORK_TIMEOUT';
                $message = 'Koneksi jaringan gagal atau timeout saat menghubungi endpoint Backblaze B2.';
            } else {
                $category = 'SERVICE_ERROR';
                $message = 'Terjadi kesalahan pada layanan S3: '.($awsCode ?: 'S3_ERROR');
            }

            return [
                'passed' => false,
                'bucket' => $bucket,
                'prefix' => $prefix,
                'status_category' => $category,
                'error_message' => $message,
            ];
        } catch (\Throwable) {
            return [
                'passed' => false,
                'bucket' => $bucket,
                'prefix' => $prefix,
                'status_category' => 'UNEXPECTED_ERROR',
                'error_message' => 'Terjadi kesalahan internal yang tidak terduga saat inisialisasi client.',
            ];
        }
    }

    /**
     * Render the result of a single bucket check.
     *
     * @param  array{
     *     passed: bool,
     *     bucket: string,
     *     prefix: string,
     *     status_category: string,
     *     error_message: string|null
     * }  $result
     */
    protected function renderBucketResult(string $label, array $result): void
    {
        $this->line("   Bucket: <fg=cyan>{$result['bucket']}</>");
        $this->line("   Prefix: <fg=cyan>{$result['prefix']}</>");

        if ($result['passed']) {
            $this->line('   Status: <fg=green;options=bold>PASS</> (Koneksi & izin listFiles berhasil)');
        } else {
            $this->line("   Status: <fg=red;options=bold>FAIL [{$result['status_category']}]</>");
            if ($result['error_message'] !== null) {
                $this->line("   Pesan:  <fg=yellow>{$result['error_message']}</>");
            }
        }
    }
}
