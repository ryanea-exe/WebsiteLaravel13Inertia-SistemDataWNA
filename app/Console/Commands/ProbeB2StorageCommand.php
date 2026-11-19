<?php

namespace App\Console\Commands;

use App\Services\B2ProbeManifest;
use App\Services\DocumentStorageService;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Closure;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use RuntimeException;

class ProbeB2StorageCommand extends Command
{
    /**
     * S3 Client Factory callback for dependency injection and automated testing.
     *
     * @var (Closure(array<string, mixed>): S3ClientInterface)|null
     */
    protected static ?Closure $s3ClientFactory = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:probe-b2
                            {--prepare : Prepare test environment and validate manifest without remote B2 operations (default)}
                            {--credential-check : Securely collect and validate credentials in memory without remote B2 operations}
                            {--connectivity-check : Securely perform read-only HeadBucket connectivity check to Backblaze B2}
                            {--io-check : Securely test isolated upload, read, streaming, and cleanup of temporary probe objects}
                            {--live : Execute live network probe to Backblaze B2 (strictly blocked in Stage 4D/4E.1)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely audit, prepare, and execute isolated Backblaze B2 live integration testing';

    /**
     * Stage 4D.3 / 4E.1 Safety Gate constant.
     */
    public const STAGE_4E1_SAFETY_GATE_LOCKED = true;

    /**
     * Execute the console command.
     */
    public function handle(DocumentStorageService $storageService): int
    {
        // 1. Enforce strict database isolation
        $this->enforceDatabaseIsolation();

        $this->info('================================================================');
        $this->info('  STAGE 4E.1 — ISOLATED BACKBLAZE B2 TEST RUNNER INFRASTRUCTURE ');
        $this->info('================================================================');
        $this->line('Process PID: <fg=cyan>'.getmypid().'</>');
        $this->line('Database Connection: <fg=yellow>sqlite :memory: (isolated)</>');
        $this->newLine();

        // 2. Check Stage 4E.1 Safety Gate for --live
        if ($this->option('live')) {
            $this->error('================================================================');
            $this->error('STAGE 4E.1 SAFETY GATE ACTIVE:');
            $this->error('Live network operations to Backblaze B2 are strictly locked.');
            $this->error('Stage 4E.1 only permits secure credential preparation and static verification.');
            $this->error('Live integration will be unlocked in Stage 4E.2 upon user approval.');
            $this->error('================================================================');

            return self::FAILURE;
        }

        // 3. Handle --credential-check
        if ($this->option('credential-check')) {
            return $this->handleCredentialCheck();
        }

        // 4. Handle --connectivity-check (Stage 4E.2 Secure Read-Only HeadBucket Check)
        if ($this->option('connectivity-check')) {
            return $this->handleConnectivityCheck();
        }

        // 5. Handle --io-check (Stage 4E.3A Secure Isolated Upload, Read, Stream & Cleanup Test)
        if ($this->option('io-check')) {
            return $this->handleIoCheck($storageService);
        }

        // 6. Default --prepare mode: Execute Preparation & Dry-Run Manifest Verification
        $this->info('Menjalankan Mode Persiapan Runner (Tanpa Operasi Remote B2)...');
        $sessionId = 'probe-session-'.Str::uuid();
        $manifest = new B2ProbeManifest($sessionId);

        $this->line('Session ID Manifest: <fg=cyan>'.$sessionId.'</>');

        // Generate synthetic probe keys
        $privUuid = (string) Str::uuid();
        $assetUuid = (string) Str::uuid();

        $privateProbeKey = $manifest->registerProbe('b2_private', 'private', $privUuid, 'jpg');
        $assetProbeKey = $manifest->registerProbe('b2_assets_private', 'assets', $assetUuid, 'png');

        $this->line('Private Probe Key: <fg=green>'.$privateProbeKey.'</>');
        $this->line('Assets Probe Key:  <fg=green>'.$assetProbeKey.'</>');

        // Validate security boundary checks
        if (! $manifest->validateProbeKey($privateProbeKey) || ! $manifest->validateProbeKey($assetProbeKey)) {
            $this->error('Gagal memvalidasi pola kunci probe.');

            return self::FAILURE;
        }

        // Validate path traversal rejection
        $traversalAttempt = 'wna/photos/__stage_4e_probe__/../../etc/passwd';
        if ($manifest->validateProbeKey($traversalAttempt)) {
            $this->error('CRITICAL: Manifest gagal menolak path traversal!');

            return self::FAILURE;
        }

        // Validate outside prefix rejection
        $outsideAttempt = 'wna/photos/regular_user_file.jpg';
        if ($manifest->validateProbeKey($outsideAttempt)) {
            $this->error('CRITICAL: Manifest gagal menolak file di luar prefix probe!');

            return self::FAILURE;
        }

        $this->info('Verifikasi Keamanan Manifest: <fg=green>PASS</> (Path traversal & prefix terisolasi).');
        $this->info('Runner siap untuk konfigurasi in-memory Stage 4E.');
        $this->newLine();

        $this->comment('Catatan Isolasi: Tidak ada operasi remote Backblaze B2 yang dieksekusi (diverifikasi melalui code path & automated tests; bukan melalui packet capture).');

        return self::SUCCESS;
    }

    /**
     * Handle the credential collection and in-memory injection validation flow without remote B2 operations.
     */
    protected function handleCredentialCheck(): int
    {
        $this->info('Memulai Verifikasi Kredensial Interaktif Aman (In-Memory Preparation)...');

        $inputs = $this->promptCredentials();
        if ($inputs === null) {
            $this->error('Pengumpulan kredensial dibatalkan atau terminal tidak interaktif.');

            return self::FAILURE;
        }

        $errors = $this->validateCredentialInputs($inputs);
        if (! empty($errors)) {
            $this->error('Validasi Kredensial Gagal:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }

            return self::FAILURE;
        }

        $this->info('Validasi Kredensial & Konfigurasi: PASS');
        $this->line('  Private Key ID:   <fg=cyan>'.$this->maskKeyId((string) $inputs['private_key_id']).'</>');
        $this->line('  Private Bucket:   <fg=cyan>'.(string) $inputs['private_bucket'].'</>');
        $this->line('  Private Region:   <fg=cyan>'.(string) $inputs['private_region'].'</>');
        $this->line('  Private Endpoint: <fg=cyan>'.(string) $inputs['private_endpoint'].'</>');
        $this->line('  Assets Key ID:    <fg=cyan>'.$this->maskKeyId((string) $inputs['assets_key_id']).'</>');
        $this->line('  Assets Bucket:    <fg=cyan>'.(string) $inputs['assets_bucket'].'</>');
        $this->line('  Assets Region:    <fg=cyan>'.(string) $inputs['assets_region'].'</>');
        $this->line('  Assets Endpoint:  <fg=cyan>'.(string) $inputs['assets_endpoint'].'</>');
        $this->line('  Secret Status:    <fg=green>[HELD IN RUNTIME PROCESS MEMORY ONLY - NOT PERSISTED]</>');
        $this->line('  Peringatan Izin:  <fg=yellow>Penggunaan Application Key tidak membuktikan izin bucket di sisi provider Backblaze B2.</>');

        // Inject into runtime configuration memory
        $this->injectInMemoryConfiguration($inputs);
        $this->info('In-Memory Injection: PASS (Backend dialihkan ke backblaze dalam memori proses CLI).');
        $this->comment('Catatan Isolasi: Tidak ada S3 client yang dibuat untuk akses remote bucket dalam flow ini (diverifikasi melalui code path & automated tests; bukan melalui packet capture).');

        // Release local variable references when no longer required.
        // Note: Credentials are not intentionally written to persistent files.
        // PHP unset() releases zval symbol table references and marks memory for garbage collection,
        // but PHP does not guarantee secure zeroization of every string copy in physical RAM.
        // Process termination ends the process lifetime and allows the OS to reclaim process resources,
        // but process termination is not a guarantee of secure physical RAM zeroization.
        unset($inputs);

        return self::SUCCESS;
    }

    /**
     * Interactive prompt to securely collect Backblaze B2 credentials.
     *
     * @return array{
     *     private_key_id: string,
     *     private_application_key: string,
     *     private_bucket: string,
     *     private_region: string,
     *     private_endpoint: string,
     *     private_use_path_style: bool,
     *     assets_key_id: string,
     *     assets_application_key: string,
     *     assets_bucket: string,
     *     assets_region: string,
     *     assets_endpoint: string,
     *     assets_use_path_style: bool
     * }|null
     */
    public function promptCredentials(): ?array
    {
        if (! $this->input->isInteractive()) {
            return null;
        }

        $privateKeyId = (string) $this->ask('Masukkan B2 Private Application Key ID:');
        $privateSecret = (string) $this->secret('Masukkan B2 Private Application Key (Secret):', false);
        $privateBucket = (string) $this->ask('Masukkan Nama Private Bucket (harus: sistem-data-wna-private):');
        $privateRegion = (string) $this->ask('Masukkan Private Region (contoh: us-east-005):');
        $privateEndpoint = (string) $this->ask('Masukkan Private Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):');

        $this->line('Peringatan: Penggunaan Application Key yang sama tidak membuktikan kredensial memiliki izin pada kedua bucket.');
        $sameKeyForAssets = $this->confirm('Gunakan Application Key yang sama untuk Assets bucket?', false);

        if ($sameKeyForAssets) {
            $assetsKeyId = $privateKeyId;
            $assetsSecret = $privateSecret;
        } else {
            $assetsKeyId = (string) $this->ask('Masukkan B2 Assets Application Key ID:');
            $assetsSecret = (string) $this->secret('Masukkan B2 Assets Application Key (Secret):', false);
        }

        $assetsBucket = (string) $this->ask('Masukkan Nama Assets Bucket (harus: sistem-data-wna-assets):');
        $assetsRegion = (string) $this->ask('Masukkan Assets Region (contoh: us-east-005):');
        $assetsEndpoint = (string) $this->ask('Masukkan Assets Endpoint URL (contoh: https://s3.us-east-005.backblazeb2.com):');

        return [
            'private_key_id' => $privateKeyId,
            'private_application_key' => $privateSecret,
            'private_bucket' => $privateBucket,
            'private_region' => $privateRegion,
            'private_endpoint' => $privateEndpoint,
            'private_use_path_style' => false,
            'assets_key_id' => $assetsKeyId,
            'assets_application_key' => $assetsSecret,
            'assets_bucket' => $assetsBucket,
            'assets_region' => $assetsRegion,
            'assets_endpoint' => $assetsEndpoint,
            'assets_use_path_style' => false,
        ];
    }

    /**
     * Validate collected credential inputs without remote B2 operations.
     *
     * @param  array<string, mixed>  $inputs
     * @return array<int, string>
     */
    public function validateCredentialInputs(array $inputs): array
    {
        $errors = [];

        // 1. Private Key ID
        $privKey = is_string($inputs['private_key_id'] ?? null) ? trim($inputs['private_key_id']) : '';
        if ($privKey === '') {
            $errors[] = 'Private Key ID tidak boleh kosong.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $privKey)) {
            $errors[] = 'Private Key ID memuat karakter kontrol ilegal.';
        }

        // 2. Private Secret
        $privSecret = is_string($inputs['private_application_key'] ?? null) ? trim($inputs['private_application_key']) : '';
        if ($privSecret === '') {
            $errors[] = 'Private Application Key tidak boleh kosong.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $privSecret)) {
            $errors[] = 'Private Application Key memuat karakter kontrol ilegal.';
        }

        // 3. Private Bucket
        $privBucket = is_string($inputs['private_bucket'] ?? null) ? trim($inputs['private_bucket']) : '';
        if ($privBucket === '') {
            $errors[] = 'Nama Private Bucket tidak boleh kosong.';
        } elseif ($privBucket !== 'sistem-data-wna-private') {
            $errors[] = 'Nama Private Bucket tidak valid (harus sistem-data-wna-private).';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $privBucket)) {
            $errors[] = 'Nama Private Bucket memuat karakter kontrol ilegal.';
        }

        // 4. Private Region
        $privRegion = is_string($inputs['private_region'] ?? null) ? trim($inputs['private_region']) : '';
        if ($privRegion === '') {
            $errors[] = 'Private Region tidak boleh kosong.';
        } elseif (! preg_match('/^[a-z0-9-]+$/i', $privRegion)) {
            $errors[] = 'Format Private Region tidak valid.';
        }

        // 5. Private Endpoint
        $privEndpoint = is_string($inputs['private_endpoint'] ?? null) ? trim($inputs['private_endpoint']) : '';
        if ($privEndpoint === '') {
            $errors[] = 'Private Endpoint URL tidak boleh kosong.';
        } elseif (! filter_var($privEndpoint, FILTER_VALIDATE_URL) || ! str_starts_with(strtolower($privEndpoint), 'https://')) {
            $errors[] = 'Private Endpoint harus berupa URL HTTPS yang valid.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $privEndpoint)) {
            $errors[] = 'Private Endpoint memuat karakter kontrol ilegal.';
        }

        // 6. Assets Key ID
        $assetsKey = is_string($inputs['assets_key_id'] ?? null) ? trim($inputs['assets_key_id']) : '';
        if ($assetsKey === '') {
            $errors[] = 'Assets Key ID tidak boleh kosong.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $assetsKey)) {
            $errors[] = 'Assets Key ID memuat karakter kontrol ilegal.';
        }

        // 7. Assets Secret
        $assetsSecret = is_string($inputs['assets_application_key'] ?? null) ? trim($inputs['assets_application_key']) : '';
        if ($assetsSecret === '') {
            $errors[] = 'Assets Application Key tidak boleh kosong.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $assetsSecret)) {
            $errors[] = 'Assets Application Key memuat karakter kontrol ilegal.';
        }

        // 8. Assets Bucket
        $assetsBucket = is_string($inputs['assets_bucket'] ?? null) ? trim($inputs['assets_bucket']) : '';
        if ($assetsBucket === '') {
            $errors[] = 'Nama Assets Bucket tidak boleh kosong.';
        } elseif ($assetsBucket !== 'sistem-data-wna-assets') {
            $errors[] = 'Nama Assets Bucket tidak valid (harus sistem-data-wna-assets).';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $assetsBucket)) {
            $errors[] = 'Nama Assets Bucket memuat karakter kontrol ilegal.';
        }

        // 9. Assets Region
        $assetsRegion = is_string($inputs['assets_region'] ?? null) ? trim($inputs['assets_region']) : '';
        if ($assetsRegion === '') {
            $errors[] = 'Assets Region tidak boleh kosong.';
        } elseif (! preg_match('/^[a-z0-9-]+$/i', $assetsRegion)) {
            $errors[] = 'Format Assets Region tidak valid.';
        }

        // 10. Assets Endpoint
        $assetsEndpoint = is_string($inputs['assets_endpoint'] ?? null) ? trim($inputs['assets_endpoint']) : '';
        if ($assetsEndpoint === '') {
            $errors[] = 'Assets Endpoint URL tidak boleh kosong.';
        } elseif (! filter_var($assetsEndpoint, FILTER_VALIDATE_URL) || ! str_starts_with(strtolower($assetsEndpoint), 'https://')) {
            $errors[] = 'Assets Endpoint harus berupa URL HTTPS yang valid.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $assetsEndpoint)) {
            $errors[] = 'Assets Endpoint memuat karakter kontrol ilegal.';
        }

        return $errors;
    }

    /**
     * Enforce strict database isolation by redirecting default connection to in-memory SQLite.
     */
    public function enforceDatabaseIsolation(): void
    {
        $currentDefault = config('database.default');
        $currentDatabase = config("database.connections.{$currentDefault}.database");

        if ($currentDefault !== 'sqlite' || $currentDatabase !== ':memory:') {
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => ':memory:',
            ]);

            try {
                DB::disconnect();
            } catch (\Throwable) {
                // Ignore if no connection was open
            }
        }
    }

    /**
     * Inject Backblaze B2 storage configuration directly into memory for this CLI process.
     *
     * @param  array{
     *     private_key_id: string,
     *     private_application_key: string,
     *     private_bucket: string,
     *     private_region: string,
     *     private_endpoint: string,
     *     private_use_path_style?: bool,
     *     assets_key_id: string,
     *     assets_application_key: string,
     *     assets_bucket: string,
     *     assets_region: string,
     *     assets_endpoint: string,
     *     assets_use_path_style?: bool
     * }  $config
     */
    public function injectInMemoryConfiguration(array $config): void
    {
        config([
            'filesystems.backend' => 'backblaze',
            'filesystems.disks.b2_private' => [
                'driver' => 's3',
                'key' => $config['private_key_id'],
                'secret' => $config['private_application_key'],
                'region' => $config['private_region'],
                'bucket' => $config['private_bucket'],
                'endpoint' => $config['private_endpoint'],
                'use_path_style_endpoint' => $config['private_use_path_style'] ?? false,
                'visibility' => 'private',
                'throw' => true,
                'report' => false,
            ],
            'filesystems.disks.b2_assets_private' => [
                'driver' => 's3',
                'key' => $config['assets_key_id'],
                'secret' => $config['assets_application_key'],
                'region' => $config['assets_region'],
                'bucket' => $config['assets_bucket'],
                'endpoint' => $config['assets_endpoint'],
                'use_path_style_endpoint' => $config['assets_use_path_style'] ?? false,
                'visibility' => 'private',
                'throw' => true,
                'report' => false,
            ],
        ]);

        // Purge any previously cached disk instances unless already using a fake disk for tests
        if (! $this->isFakeDisk('b2_private')) {
            Storage::forgetDisk('b2_private');
        }
        if (! $this->isFakeDisk('b2_assets_private')) {
            Storage::forgetDisk('b2_assets_private');
        }
    }

    /**
     * Check if a storage disk has been faked for automated tests.
     */
    protected function isFakeDisk(string $disk): bool
    {
        try {
            $root = Storage::getFacadeRoot();
            if (! is_object($root)) {
                return false;
            }
            $disksProp = (new \ReflectionClass($root))->getProperty('disks');
            $disks = $disksProp->getValue($root);
            if (! is_array($disks) || ! isset($disks[$disk])) {
                return false;
            }
            $adapter = $disks[$disk]->getAdapter();

            return ! ($adapter instanceof AwsS3V3Adapter);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Mask Key ID to safely display in terminal logs without exposing full key.
     */
    public function maskKeyId(string $keyId): string
    {
        $len = strlen($keyId);
        if ($len <= 6) {
            return str_repeat('*', $len);
        }

        return substr($keyId, 0, 4).str_repeat('*', max(2, $len - 8)).substr($keyId, -4);
    }

    /**
     * Set a custom S3 Client Factory for testing.
     *
     * @param  (Closure(array<string, mixed>): S3ClientInterface)|null  $factory
     */
    public static function setS3ClientFactory(?Closure $factory): void
    {
        self::$s3ClientFactory = $factory;
    }

    /**
     * Create an S3 Client instance.
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
     * Handle secure read-only Backblaze B2 connectivity and authentication check using HeadBucket.
     */
    protected function handleConnectivityCheck(): int
    {
        $this->info('================================================================');
        $this->info('  STAGE 4E.2 — SECURE B2 CONNECTIVITY & AUTHENTICATION TEST     ');
        $this->info('================================================================');

        if (! $this->input->isInteractive()) {
            $this->error('Pemeriksaan konektivitas memerlukan terminal interaktif. Dibatalkan secara aman.');

            return self::FAILURE;
        }

        $inputs = $this->promptCredentials();
        if ($inputs === null) {
            $this->error('Pengumpulan kredensial dibatalkan.');

            return self::FAILURE;
        }

        $errors = $this->validateCredentialInputs($inputs);
        if (! empty($errors)) {
            $this->error('Validasi Kredensial Gagal:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }

            return self::FAILURE;
        }

        // Display non-secret configuration summary
        $this->info('Ringkasan Konfigurasi (Non-Rahasia):');
        $this->line('  Private Key ID:   <fg=cyan>'.$this->maskKeyId((string) $inputs['private_key_id']).'</>');
        $this->line('  Private Bucket:   <fg=cyan>'.(string) $inputs['private_bucket'].'</>');
        $this->line('  Private Region:   <fg=cyan>'.(string) $inputs['private_region'].'</>');
        $this->line('  Private Endpoint: <fg=cyan>'.(string) $inputs['private_endpoint'].'</>');
        $this->line('  Assets Key ID:    <fg=cyan>'.$this->maskKeyId((string) $inputs['assets_key_id']).'</>');
        $this->line('  Assets Bucket:    <fg=cyan>'.(string) $inputs['assets_bucket'].'</>');
        $this->line('  Assets Region:    <fg=cyan>'.(string) $inputs['assets_region'].'</>');
        $this->line('  Assets Endpoint:  <fg=cyan>'.(string) $inputs['assets_endpoint'].'</>');
        $this->line('  Secret Status:    <fg=green>[HELD IN RUNTIME PROCESS MEMORY ONLY - NOT PERSISTED]</>');
        $this->newLine();

        // Security Notice & Backblaze B2 Permission Requirements
        $this->warn('PERINGATAN OPERASI JARINGAN:');
        $this->line('Perintah ini akan melakukan request HTTPS read-only (HeadBucket) langsung ke Backblaze B2.');
        $this->line('Operasi ini TIDAK melakukan upload, download, list, atau delete data apa pun.');
        $this->newLine();
        $this->comment('PERSYARATAN IZIN BACKBLAZE B2:');
        $this->line('- HeadBucket pada S3-Compatible API memeriksa keberadaan bucket dan hak akses kredensial.');
        $this->line('- Pada Backblaze B2, Application Key yang dibatasi ke bucket tertentu mungkin memerlukan izin');
        $this->line('  "listAllBucketNames" atau izin akses bucket untuk operasi HeadBucket.');
        $this->line('- Tidak diperlukan (dan jangan berikan) izin write atau delete untuk tahap ini.');
        $this->newLine();

        // Require explicit exact confirmation phrase
        $confirmation = $this->ask('Ketik frasa konfirmasi persis [CONNECT TO B2 READ-ONLY] untuk melanjutkan:');
        if ($confirmation !== 'CONNECT TO B2 READ-ONLY') {
            $this->warn('Konfirmasi dibatalkan atau frasa tidak cocok. Tidak ada koneksi jaringan yang dibuat ke Backblaze B2.');

            return self::FAILURE;
        }

        $this->info('Frasa konfirmasi valid. Memulai pemeriksaan HeadBucket...');
        $this->newLine();

        // 1. Perform HeadBucket on Private Bucket
        $privateResult = $this->executeHeadBucketCheck(
            keyId: (string) $inputs['private_key_id'],
            secret: (string) $inputs['private_application_key'],
            bucket: (string) $inputs['private_bucket'],
            region: (string) $inputs['private_region'],
            endpoint: (string) $inputs['private_endpoint'],
            usePathStyle: (bool) $inputs['private_use_path_style']
        );

        // 2. Perform HeadBucket on Assets Bucket (independent execution)
        $assetsResult = $this->executeHeadBucketCheck(
            keyId: (string) $inputs['assets_key_id'],
            secret: (string) $inputs['assets_application_key'],
            bucket: (string) $inputs['assets_bucket'],
            region: (string) $inputs['assets_region'],
            endpoint: (string) $inputs['assets_endpoint'],
            usePathStyle: (bool) $inputs['assets_use_path_style']
        );

        // Display results
        $this->displayBucketCheckResult('Private Bucket', $privateResult);
        $this->newLine();
        $this->displayBucketCheckResult('Assets Bucket', $assetsResult);
        $this->newLine();

        // Release references from memory
        $isSuccess = ($privateResult['status'] === 'SUCCESS' && $assetsResult['status'] === 'SUCCESS');
        unset($inputs, $privateResult, $assetsResult);

        return $isSuccess ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Execute isolated HeadBucket check on a single bucket.
     *
     * @return array{
     *     status: 'SUCCESS'|'FAILED',
     *     bucket: string,
     *     region: string,
     *     endpoint: string,
     *     operation: string,
     *     authentication: 'SUCCESS'|'FAILED'|'INCONCLUSIVE',
     *     access: 'SUCCESS'|'DENIED'|'INCONCLUSIVE',
     *     category: string,
     *     detail: string
     * }
     */
    public function executeHeadBucketCheck(
        string $keyId,
        string $secret,
        string $bucket,
        string $region,
        string $endpoint,
        bool $usePathStyle
    ): array {
        $clientConfig = [
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $keyId,
                'secret' => $secret,
            ],
            'signature_version' => 'v4',
            'use_path_style_endpoint' => $usePathStyle,
            'http' => [
                'connect_timeout' => 5,
                'timeout' => 10,
            ],
            'retries' => 1,
        ];

        try {
            $client = $this->createS3Client($clientConfig);
            $client->headBucket(['Bucket' => $bucket]);

            return [
                'status' => 'SUCCESS',
                'bucket' => $bucket,
                'region' => $region,
                'endpoint' => $endpoint,
                'operation' => 'HeadBucket',
                'authentication' => 'SUCCESS',
                'access' => 'SUCCESS',
                'category' => 'HTTP 200 OK',
                'detail' => 'Koneksi, autentikasi, dan akses ke bucket berhasil diverifikasi.',
            ];
        } catch (S3Exception $e) {
            $statusCode = $e->getStatusCode();
            $awsCode = (string) $e->getAwsErrorCode();
            $rawMsg = $e->getAwsErrorMessage() ?: $e->getMessage();

            if ($statusCode === 401 || in_array($awsCode, ['InvalidAccessKeyId', 'SignatureDoesNotMatch'], true) || (str_contains(strtolower($rawMsg), 'signature') && $statusCode === 403)) {
                return [
                    'status' => 'FAILED',
                    'bucket' => $bucket,
                    'region' => $region,
                    'endpoint' => $endpoint,
                    'operation' => 'HeadBucket',
                    'authentication' => 'FAILED',
                    'access' => 'INCONCLUSIVE',
                    'category' => 'Authentication Failure (HTTP '.($statusCode ?: '401').')',
                    'detail' => 'Key ID atau Application Key Secret tidak valid atau tanda tangan signature v4 ditolak provider.',
                ];
            }

            if ($statusCode === 403) {
                return [
                    'status' => 'FAILED',
                    'bucket' => $bucket,
                    'region' => $region,
                    'endpoint' => $endpoint,
                    'operation' => 'HeadBucket',
                    'authentication' => 'SUCCESS',
                    'access' => 'DENIED',
                    'category' => 'Access Denied (HTTP 403)',
                    'detail' => 'Kredensial berhasil diautentikasi tetapi izin Application Key tidak mencukupi untuk HeadBucket pada bucket ini. Catatan: Backblaze B2 memerlukan izin listBuckets atau listAllBucketNames pada Application Key.',
                ];
            }

            if ($statusCode === 404 || $awsCode === 'NoSuchBucket' || $awsCode === 'NotFound') {
                return [
                    'status' => 'FAILED',
                    'bucket' => $bucket,
                    'region' => $region,
                    'endpoint' => $endpoint,
                    'operation' => 'HeadBucket',
                    'authentication' => 'SUCCESS',
                    'access' => 'INCONCLUSIVE',
                    'category' => 'Bucket Not Found (HTTP 404)',
                    'detail' => 'Bucket tidak ditemukan pada region dan endpoint yang ditentukan.',
                ];
            }

            if ($statusCode === 301 || $awsCode === 'PermanentRedirect' || $awsCode === 'IllegalLocationConstraintException') {
                return [
                    'status' => 'FAILED',
                    'bucket' => $bucket,
                    'region' => $region,
                    'endpoint' => $endpoint,
                    'operation' => 'HeadBucket',
                    'authentication' => 'INCONCLUSIVE',
                    'access' => 'INCONCLUSIVE',
                    'category' => 'Endpoint / Region Mismatch (HTTP '.($statusCode ?: '301').')',
                    'detail' => 'Region atau endpoint URL tidak sesuai dengan lokasi klaster fisik bucket.',
                ];
            }

            return [
                'status' => 'FAILED',
                'bucket' => $bucket,
                'region' => $region,
                'endpoint' => $endpoint,
                'operation' => 'HeadBucket',
                'authentication' => 'INCONCLUSIVE',
                'access' => 'INCONCLUSIVE',
                'category' => 'SDK / API Error (HTTP '.($statusCode ?: 'N/A').')',
                'detail' => 'Error API: '.($awsCode ?: 'S3Exception').'. (Exception mentah disembunyikan untuk keamanan).',
            ];
        } catch (ConnectException) {
            return [
                'status' => 'FAILED',
                'bucket' => $bucket,
                'region' => $region,
                'endpoint' => $endpoint,
                'operation' => 'HeadBucket',
                'authentication' => 'INCONCLUSIVE',
                'access' => 'INCONCLUSIVE',
                'category' => 'Connection / DNS / TLS Failure',
                'detail' => 'Gagal menghubungkan ke endpoint remote (DNS resolusi gagal, connection timeout, atau kegagalan TLS handshake).',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'FAILED',
                'bucket' => $bucket,
                'region' => $region,
                'endpoint' => $endpoint,
                'operation' => 'HeadBucket',
                'authentication' => 'INCONCLUSIVE',
                'access' => 'INCONCLUSIVE',
                'category' => 'Transport / Client Error: '.class_basename($e),
                'detail' => 'Kegagalan transportasi jaringan tak terduga. (Exception mentah disembunyikan untuk keamanan).',
            ];
        }
    }

    /**
     * Display formatted check result for a single bucket.
     *
     * @param  array{
     *     status: 'SUCCESS'|'FAILED',
     *     bucket: string,
     *     region: string,
     *     endpoint: string,
     *     operation: string,
     *     authentication: 'SUCCESS'|'FAILED'|'INCONCLUSIVE',
     *     access: 'SUCCESS'|'DENIED'|'INCONCLUSIVE',
     *     category: string,
     *     detail: string
     * }  $res
     */
    protected function displayBucketCheckResult(string $label, array $res): void
    {
        $statusColor = $res['status'] === 'SUCCESS' ? 'green' : 'red';
        $authColor = $res['authentication'] === 'SUCCESS' ? 'green' : ($res['authentication'] === 'FAILED' ? 'red' : 'yellow');
        $accessColor = $res['access'] === 'SUCCESS' ? 'green' : ($res['access'] === 'DENIED' ? 'red' : 'yellow');

        $this->line("--- {$label} ---");
        $this->line("  Result:         <fg={$statusColor};options=bold>{$res['status']}</>");
        $this->line("  Operation:      {$res['operation']}");
        $this->line("  Bucket:         {$res['bucket']}");
        $this->line("  Region:         {$res['region']}");
        $this->line("  Endpoint:       {$res['endpoint']}");
        $this->line("  Authentication: <fg={$authColor}>{$res['authentication']}</>");
        $this->line("  Access:         <fg={$accessColor}>{$res['access']}</>");
        $this->line("  Category:       {$res['category']}");
        $this->line("  Detail:         {$res['detail']}");
    }

    /**
     * Handle isolated upload, read, streaming, and cleanup testing across both Backblaze B2 buckets.
     */
    protected function handleIoCheck(DocumentStorageService $storageService): int
    {
        $this->info('================================================================');
        $this->info('  STAGE 4E.3A — ISOLATED BACKBLAZE B2 I/O & STREAMING RUNNER     ');
        $this->info('================================================================');

        if (! $this->input->isInteractive()) {
            $this->error('Pengujian I/O memerlukan terminal interaktif. Dibatalkan secara aman.');

            return self::FAILURE;
        }

        $inputs = $this->promptCredentials();
        if ($inputs === null) {
            $this->error('Pengumpulan kredensial dibatalkan.');

            return self::FAILURE;
        }

        $errors = $this->validateCredentialInputs($inputs);
        if (! empty($errors)) {
            $this->error('Validasi Kredensial Gagal:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }

            return self::FAILURE;
        }

        // Inject configuration into process memory
        $this->injectInMemoryConfiguration($inputs);

        // Display non-secret configuration summary
        $this->info('Ringkasan Konfigurasi (Non-Rahasia):');
        $this->line('  Private Key ID:   <fg=cyan>'.$this->maskKeyId((string) $inputs['private_key_id']).'</>');
        $this->line('  Private Bucket:   <fg=cyan>'.(string) $inputs['private_bucket'].'</>');
        $this->line('  Private Region:   <fg=cyan>'.(string) $inputs['private_region'].'</>');
        $this->line('  Private Endpoint: <fg=cyan>'.(string) $inputs['private_endpoint'].'</>');
        $this->line('  Assets Key ID:    <fg=cyan>'.$this->maskKeyId((string) $inputs['assets_key_id']).'</>');
        $this->line('  Assets Bucket:    <fg=cyan>'.(string) $inputs['assets_bucket'].'</>');
        $this->line('  Assets Region:    <fg=cyan>'.(string) $inputs['assets_region'].'</>');
        $this->line('  Assets Endpoint:  <fg=cyan>'.(string) $inputs['assets_endpoint'].'</>');
        $this->line('  Secret Status:    <fg=green>[HELD IN RUNTIME PROCESS MEMORY ONLY - NOT PERSISTED]</>');
        $this->newLine();

        $this->warn('PERINGATAN OPERASI I/O PADA BACKBLAZE B2:');
        $this->line('Perintah ini akan menjalankan alur:');
        $this->line('  1. HeadBucket (konektivitas)');
        $this->line('  2. Upload file probe 1x1 PNG sementara');
        $this->line('  3. Read & verifikasi integritas byte');
        $this->line('  4. Streaming via DocumentStorageService::streamFile() & verifikasi header');
        $this->line('  5. Delete via DocumentStorageService & verifikasi bahwa objek sudah tidak ada');
        $this->line('Seluruh operasi DIBATASI KETAT hanya pada prefix [__stage_4e_probe__/].');
        $this->line('TIDAK ADA foto pengguna, berkas produksi, atau tabel database yang disentuh.');
        $this->newLine();

        $confirmation = $this->ask('Ketik frasa konfirmasi persis [UPLOAD READ STREAM DELETE B2 PROBES] untuk melanjutkan:');
        if ($confirmation !== 'UPLOAD READ STREAM DELETE B2 PROBES') {
            $this->warn('Konfirmasi dibatalkan atau frasa tidak cocok. Tidak ada operasi objek yang dijalankan.');

            return self::FAILURE;
        }

        $this->info('Frasa konfirmasi valid. Memulai pengujian I/O terisolasi...');
        $this->newLine();

        $sessionId = 'probe-session-'.Str::uuid();
        $manifest = new B2ProbeManifest($sessionId);

        // 1x1 valid binary PNG (transparent, 70 bytes)
        $pngPayload = (string) base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            true
        );

        // 1. Private Bucket IO Probe
        $privateResult = $this->executeBucketIoProbe(
            params: [
                'disk' => $storageService->getPrivateDisk(),
                'module' => 'private',
                'bucket' => (string) $inputs['private_bucket'],
                'region' => (string) $inputs['private_region'],
                'endpoint' => (string) $inputs['private_endpoint'],
                'key_id' => (string) $inputs['private_key_id'],
                'secret' => (string) $inputs['private_application_key'],
                'use_path_style' => (bool) $inputs['private_use_path_style'],
            ],
            payload: $pngPayload,
            manifest: $manifest,
            storageService: $storageService
        );

        // 2. Assets Bucket IO Probe (Independent execution)
        $assetsResult = $this->executeBucketIoProbe(
            params: [
                'disk' => $storageService->getAssetsDisk(),
                'module' => 'assets',
                'bucket' => (string) $inputs['assets_bucket'],
                'region' => (string) $inputs['assets_region'],
                'endpoint' => (string) $inputs['assets_endpoint'],
                'key_id' => (string) $inputs['assets_key_id'],
                'secret' => (string) $inputs['assets_application_key'],
                'use_path_style' => (bool) $inputs['assets_use_path_style'],
            ],
            payload: $pngPayload,
            manifest: $manifest,
            storageService: $storageService
        );

        // Display results
        $this->displayIoCheckResult('Private Bucket Probe', $privateResult);
        $this->newLine();
        $this->displayIoCheckResult('Assets Bucket Probe', $assetsResult);
        $this->newLine();

        // Check for any residual uncleaned objects
        if ($manifest->hasResidual()) {
            $this->error('PERINGATAN RESIDUAL: Terdapat objek probe yang belum terhapus pada sesi ini:');
            foreach ($manifest->getPendingCleanup() as $pendingKey) {
                $status = $manifest->getCleanupStatus($pendingKey);
                $this->error("  - [{$pendingKey}] (Status: {$status})");
            }
            $this->line('Silakan lakukan penghapusan manual pada Backblaze B2 Console untuk objek-objek tersebut.');
        }

        $allPassed = ($privateResult['overall'] === 'PASS' && $assetsResult['overall'] === 'PASS');
        unset($inputs, $pngPayload, $privateResult, $assetsResult);

        return $allPassed ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Execute isolated IO probe on a single bucket (HeadBucket, Upload, Read, Stream, Delete, Existence Verification).
     *
     * @param  array{
     *     disk: string,
     *     module: string,
     *     bucket: string,
     *     region: string,
     *     endpoint: string,
     *     key_id: string,
     *     secret: string,
     *     use_path_style: bool
     * }  $params
     * @return array{
     *     bucket: string,
     *     disk: string,
     *     probe_key: string,
     *     connectivity: 'PASS'|'FAIL',
     *     upload: 'PASS'|'FAIL',
     *     read: 'PASS'|'FAIL',
     *     stream: 'PASS'|'FAIL',
     *     cleanup: 'PASS'|'FAIL',
     *     cleanup_status: 'DELETED_CONFIRMED'|'RESIDUAL_CONFIRMED'|'CLEANUP_UNVERIFIED'|'NOT_ATTEMPTED',
     *     overall: 'PASS'|'FAIL',
     *     error: string|null,
     *     residual_key: string|null
     * }
     */
    public function executeBucketIoProbe(
        array $params,
        string $payload,
        B2ProbeManifest $manifest,
        DocumentStorageService $storageService
    ): array {
        $disk = $params['disk'];
        $module = $params['module'];
        $bucket = $params['bucket'];
        $region = $params['region'];
        $endpoint = $params['endpoint'];
        $keyId = $params['key_id'];
        $secret = $params['secret'];
        $usePathStyle = $params['use_path_style'];

        $result = [
            'bucket' => $bucket,
            'disk' => $disk,
            'probe_key' => '',
            'connectivity' => 'FAIL',
            'upload' => 'FAIL',
            'read' => 'FAIL',
            'stream' => 'FAIL',
            'cleanup' => 'FAIL',
            'cleanup_status' => 'NOT_ATTEMPTED',
            'overall' => 'FAIL',
            'error' => null,
            'residual_key' => null,
        ];

        // 1. Connectivity Check (HeadBucket)
        $headCheck = $this->executeHeadBucketCheck($keyId, $secret, $bucket, $region, $endpoint, $usePathStyle);
        if ($headCheck['status'] !== 'SUCCESS') {
            $result['error'] = 'Konektivitas HeadBucket gagal: '.$headCheck['category'];

            return $result;
        }
        $result['connectivity'] = 'PASS';

        // 2. Generate cryptographically secure unique probe key
        $probeUuid = (string) Str::uuid();
        $probeKey = $manifest->registerProbe($disk, $module, $probeUuid, 'png');
        $result['probe_key'] = $probeKey;

        // Verify path allowlist before writing
        if (! $storageService->isPathAllowed($probeKey)) {
            $result['error'] = 'Kunci probe ditolak oleh allowlist managed directory DocumentStorageService.';

            return $result;
        }

        $objectNeedsCleanup = false;

        // 3. Complete I/O Lifecycle wrapped in robust try/finally
        try {
            // A. Upload payload to disk via DocumentStorageService::storeProbeFile()
            try {
                $uploaded = $storageService->storeProbeFile($disk, $probeKey, $payload);
                if (! $uploaded) {
                    $result['error'] = 'Gagal menyimpan objek probe via DocumentStorageService::storeProbeFile().';

                    return $result;
                }
                $manifest->markCreated($probeKey);
                $objectNeedsCleanup = true;
                $result['upload'] = 'PASS';
            } catch (\Throwable $uploadException) {
                $result['error'] = 'Upload exception: '.class_basename($uploadException).' - '.$uploadException->getMessage();

                // Defensively verify whether the object was actually written to disk
                try {
                    $existsAfterException = $storageService->exists($probeKey, $disk);
                    if ($existsAfterException) {
                        $manifest->markCreated($probeKey);
                        $objectNeedsCleanup = true;
                    }
                } catch (\Throwable $existsEx) {
                    // Existence check also threw an exception. Do NOT assume object does not exist!
                    $manifest->markCreated($probeKey);
                    $manifest->markCleanupStatus($probeKey, B2ProbeManifest::STATUS_CLEANUP_UNVERIFIED, $disk);
                    $objectNeedsCleanup = true;
                    $result['cleanup_status'] = 'CLEANUP_UNVERIFIED';
                    $result['residual_key'] = $probeKey;
                    $result['error'] .= ' | Status keberadaan objek tidak dapat diverifikasi: '.class_basename($existsEx).' - '.$existsEx->getMessage();
                }

                // If object was not detected and exists did not throw, it was a clean upload failure without residual
                if (! $objectNeedsCleanup) {
                    return $result;
                }
            }

            // Only proceed to Read and Stream if upload succeeded cleanly
            if ($result['upload'] === 'PASS') {
                // B. Read & byte integrity verification
                $stream = $storageService->readStream($probeKey, $disk);
                if (! is_resource($stream)) {
                    throw new RuntimeException('Gagal membaca stream berkas probe dari storage service.');
                }
                $readBytes = stream_get_contents($stream);
                fclose($stream);

                if ($readBytes !== $payload) {
                    throw new RuntimeException('Ketidaksesuaian integritas byte: konten yang dibaca berbeda dengan payload asli.');
                }
                $result['read'] = 'PASS';

                // C. Streaming via DocumentStorageService::streamFile()
                $response = $storageService->streamFile($disk, $probeKey, 'image/png');
                ob_start();
                try {
                    $response->sendContent();
                    $streamedBytes = ob_get_clean();
                } catch (\Throwable $streamSendEx) {
                    ob_end_clean();
                    throw new RuntimeException('Kegagalan eksekusi callback response streaming: '.$streamSendEx->getMessage(), 0, $streamSendEx);
                }

                if ($streamedBytes !== $payload) {
                    throw new RuntimeException('Ketidaksesuaian integritas byte streaming: output response berbeda dengan payload asli.');
                }

                $contentType = $response->headers->get('Content-Type');
                $disposition = $response->headers->get('Content-Disposition');
                $nosniff = $response->headers->get('X-Content-Type-Options');

                if ($contentType !== 'image/png' || $disposition !== 'inline' || $nosniff !== 'nosniff') {
                    throw new RuntimeException('Header keamanan streaming tidak memenuhi standar wajib (Content-Type/Disposition/X-Content-Type-Options).');
                }
                $result['stream'] = 'PASS';
            }
        } catch (\Throwable $e) {
            $result['error'] = ($result['error'] !== null ? $result['error'].' | ' : '').$e->getMessage();
        } finally {
            // D. Cleanup: MUST delete exact registered object key and verify post-deletion non-existence
            if ($objectNeedsCleanup && in_array($probeKey, $manifest->getPendingCleanup(), true)) {
                $deleteSuccess = false;
                $deleteException = null;

                try {
                    $deleteSuccess = ($disk === $storageService->getPrivateDisk())
                        ? $storageService->deletePrivate($probeKey)
                        : $storageService->deletePublic($probeKey);
                } catch (\Throwable $delEx) {
                    $deleteException = $delEx;
                }

                // Verify whether object still exists on disk post-delete
                try {
                    $stillExists = $storageService->exists($probeKey, $disk);

                    if ($deleteException !== null) {
                        if ($stillExists) {
                            $result['cleanup_status'] = 'RESIDUAL_CONFIRMED';
                            $manifest->markCleanupStatus($probeKey, B2ProbeManifest::STATUS_RESIDUAL_CONFIRMED, $disk);
                            $result['residual_key'] = $probeKey;
                            $result['error'] = ($result['error'] !== null ? $result['error'].' | ' : '').'Delete exception: '.$deleteException->getMessage();
                        } else {
                            $result['cleanup_status'] = 'CLEANUP_UNVERIFIED';
                            $manifest->markCleanupStatus($probeKey, B2ProbeManifest::STATUS_CLEANUP_UNVERIFIED, $disk);
                            $result['residual_key'] = $probeKey;
                            $result['error'] = ($result['error'] !== null ? $result['error'].' | ' : '').'Delete exception: '.$deleteException->getMessage();
                        }
                    } elseif ($deleteSuccess && ! $stillExists) {
                        $result['cleanup_status'] = 'DELETED_CONFIRMED';
                        $manifest->markDeleted($probeKey, $disk);
                    } else {
                        $result['cleanup_status'] = 'RESIDUAL_CONFIRMED';
                        $manifest->markCleanupStatus($probeKey, B2ProbeManifest::STATUS_RESIDUAL_CONFIRMED, $disk);
                        $result['residual_key'] = $probeKey;
                        $result['error'] = ($result['error'] !== null ? $result['error'].' | ' : '').'Penghapusan berkas gagal atau berkas masih terdeteksi ada di storage disk.';
                    }
                } catch (\Throwable $existEx) {
                    $result['cleanup_status'] = 'CLEANUP_UNVERIFIED';
                    $manifest->markCleanupStatus($probeKey, B2ProbeManifest::STATUS_CLEANUP_UNVERIFIED, $disk);
                    $result['residual_key'] = $probeKey;
                    $result['error'] = ($result['error'] !== null ? $result['error'].' | ' : '').'Verifikasi keberadaan pasca-delete gagal: '.$existEx->getMessage();
                }

                $result['cleanup'] = ($result['cleanup_status'] === 'DELETED_CONFIRMED') ? 'PASS' : 'FAIL';
            }
        }

        if (
            $result['upload'] === 'PASS' &&
            $result['read'] === 'PASS' &&
            $result['stream'] === 'PASS' &&
            $result['cleanup_status'] === 'DELETED_CONFIRMED'
        ) {
            $result['overall'] = 'PASS';
        }

        return $result;
    }

    /**
     * Display formatted IO check result for a single bucket.
     *
     * @param  array{
     *     bucket: string,
     *     disk: string,
     *     probe_key: string,
     *     connectivity: 'PASS'|'FAIL',
     *     upload: 'PASS'|'FAIL',
     *     read: 'PASS'|'FAIL',
     *     stream: 'PASS'|'FAIL',
     *     cleanup: 'PASS'|'FAIL',
     *     cleanup_status: 'DELETED_CONFIRMED'|'RESIDUAL_CONFIRMED'|'CLEANUP_UNVERIFIED'|'NOT_ATTEMPTED',
     *     overall: 'PASS'|'FAIL',
     *     error: string|null,
     *     residual_key: string|null
     * }  $res
     */
    protected function displayIoCheckResult(string $label, array $res): void
    {
        $overallColor = $res['overall'] === 'PASS' ? 'green' : 'red';
        $connColor = $res['connectivity'] === 'PASS' ? 'green' : 'red';
        $upColor = $res['upload'] === 'PASS' ? 'green' : 'red';
        $readColor = $res['read'] === 'PASS' ? 'green' : 'red';
        $streamColor = $res['stream'] === 'PASS' ? 'green' : 'red';
        $cleanColor = $res['cleanup'] === 'PASS' ? 'green' : 'red';

        $this->line("=== {$label} [Disk: {$res['disk']}] ===");
        $this->line("  Bucket:         {$res['bucket']}");
        $this->line("  Probe Key:      {$res['probe_key']}");
        $this->line("  1. Connect:     <fg={$connColor}>{$res['connectivity']}</>");
        $this->line("  2. Upload:      <fg={$upColor}>{$res['upload']}</>");
        $this->line("  3. Read:        <fg={$readColor}>{$res['read']}</>");
        $this->line("  4. Stream:      <fg={$streamColor}>{$res['stream']}</>");
        $this->line("  5. Cleanup:     <fg={$cleanColor}>{$res['cleanup']}</> [{$res['cleanup_status']}]");
        $this->line("  Overall Status: <fg={$overallColor};options=bold>{$res['overall']}</>");
        if ($res['error'] !== null) {
            $this->line("  Error Detail:   <fg=yellow>{$res['error']}</>");
        }
        if ($res['residual_key'] !== null) {
            $warnPrefix = ($res['cleanup_status'] === 'CLEANUP_UNVERIFIED') ? 'PERINGATAN UNVERIFIED' : 'PERINGATAN RESIDUAL';
            $this->error("  {$warnPrefix}: Objek [{$res['residual_key']}] status: {$res['cleanup_status']}.");
            $this->line('  Silakan lakukan pemeriksaan/penghapusan manual pada Backblaze B2 Console.');
        }
    }
}
