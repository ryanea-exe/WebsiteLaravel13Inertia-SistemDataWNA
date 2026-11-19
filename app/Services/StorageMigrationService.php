<?php

namespace App\Services;

use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wna;
use Aws\S3\Exception\S3Exception;
use Closure;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;
use RuntimeException;

class StorageMigrationService
{
    /**
     * Item migration evaluation statuses.
     */
    public const STATUS_READY_TO_MIGRATE = 'READY_TO_MIGRATE';

    public const STATUS_ALREADY_MATCHED = 'ALREADY_MATCHED';

    public const STATUS_CONFLICT = 'CONFLICT';

    public const STATUS_SOURCE_MISSING = 'SOURCE_MISSING';

    public const STATUS_REJECTED_PATH = 'REJECTED_PATH';

    public const STATUS_UNRESOLVED_CATEGORY = 'UNRESOLVED_CATEGORY';

    public const STATUS_SOURCE_READ_FAILED = 'SOURCE_READ_FAILED';

    public const STATUS_VERIFICATION_FAILED = 'VERIFICATION_FAILED';

    public const STATUS_MIGRATED = 'MIGRATED';

    public const STATUS_UPLOAD_FAILED = 'UPLOAD_FAILED';

    /**
     * Managed migration categories.
     */
    public const CATEGORY_WNA = 'wna';

    public const CATEGORY_ABG = 'abg';

    public const CATEGORY_AFFIDAVIT = 'affidavit';

    public const CATEGORY_USER = 'user';

    public const CATEGORY_BRANDING = 'branding';

    public const SUPPORTED_CATEGORIES = [
        self::CATEGORY_WNA,
        self::CATEGORY_ABG,
        self::CATEGORY_AFFIDAVIT,
        self::CATEGORY_USER,
        self::CATEGORY_BRANDING,
    ];

    /**
     * Category configurations defining source/destination defaults and path allowlists.
     *
     * @var array<string, array{type: 'private'|'assets', allowed_prefixes: list<string>}>
     */
    protected const CATEGORY_CONFIGS = [
        self::CATEGORY_WNA => [
            'type' => 'private',
            'allowed_prefixes' => ['wna/photos/'],
        ],
        self::CATEGORY_ABG => [
            'type' => 'private',
            'allowed_prefixes' => ['abg/photos/'],
        ],
        self::CATEGORY_AFFIDAVIT => [
            'type' => 'private',
            'allowed_prefixes' => ['affidavit/photos/'],
        ],
        self::CATEGORY_USER => [
            'type' => 'private',
            'allowed_prefixes' => ['users/photos/', 'photos/'],
        ],
        self::CATEGORY_BRANDING => [
            'type' => 'assets',
            'allowed_prefixes' => ['settings/icons/'],
        ],
    ];

    /**
     * Optional custom inventory provider for testing.
     *
     * @var (Closure(): array<int, array<string, mixed>>)|null
     */
    protected static ?Closure $customInventoryProvider = null;

    /**
     * Optional custom destination checker callback for testing.
     *
     * @var (Closure(string, string): array<string, mixed>)|null
     */
    protected static ?Closure $customDestinationChecker = null;

    /**
     * Optional custom transfer handler callback for testing.
     *
     * @var (Closure(array<string, mixed>): (array<string, mixed>|null))|null
     */
    protected static ?Closure $customTransferHandler = null;

    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    /**
     * Set a custom inventory provider callback for testing.
     *
     * @param  (Closure(): array<int, array<string, mixed>>)|null  $provider
     */
    public static function setCustomInventoryProvider(?Closure $provider): void
    {
        self::$customInventoryProvider = $provider;
    }

    /**
     * Set a custom destination existence checker callback for testing.
     *
     * @param  (Closure(string $disk, string $path): array<string, mixed>)|null  $checker
     */
    public static function setCustomDestinationChecker(?Closure $checker): void
    {
        self::$customDestinationChecker = $checker;
    }

    /**
     * Set a custom transfer handler callback for testing.
     *
     * @param  (Closure(array<string, mixed> $item): (array<string, mixed>|null))|null  $handler
     */
    public static function setCustomTransferHandler(?Closure $handler): void
    {
        self::$customTransferHandler = $handler;
    }

    /**
     * Collect all database-referenced file paths across application models.
     *
     * @return array<string, array{
     *     category: string,
     *     relative_path: string,
     *     references: list<array{table: string, id: int|string, field: string}>
     * }>
     */
    public function collectDatabaseInventory(string $categoryFilter = 'all'): array
    {
        if (self::$customInventoryProvider !== null) {
            $rawItems = array_values((self::$customInventoryProvider)());

            return $this->normalizeAndDeduplicateRawItems($rawItems, $categoryFilter);
        }

        $items = [];

        // 1. WNA Photos
        if ($categoryFilter === 'all' || $categoryFilter === self::CATEGORY_WNA) {
            $records = Wna::query()->toBase()->whereNotNull('foto')->select(['id', 'foto'])->get();
            foreach ($records as $row) {
                if ($row->foto !== null && trim((string) $row->foto) !== '') {
                    $items[] = [
                        'category' => self::CATEGORY_WNA,
                        'relative_path' => trim((string) $row->foto),
                        'table' => 'wna',
                        'id' => $row->id,
                        'field' => 'foto',
                    ];
                }
            }
        }

        // 2. ABG Photos
        if ($categoryFilter === 'all' || $categoryFilter === self::CATEGORY_ABG) {
            $records = Abg::query()->toBase()->whereNotNull('foto')->select(['id', 'foto'])->get();
            foreach ($records as $row) {
                if ($row->foto !== null && trim((string) $row->foto) !== '') {
                    $items[] = [
                        'category' => self::CATEGORY_ABG,
                        'relative_path' => trim((string) $row->foto),
                        'table' => 'abg',
                        'id' => $row->id,
                        'field' => 'foto',
                    ];
                }
            }
        }

        // 3. Affidavit Photos
        if ($categoryFilter === 'all' || $categoryFilter === self::CATEGORY_AFFIDAVIT) {
            $records = Affidavit::query()->toBase()->whereNotNull('foto')->select(['id', 'foto'])->get();
            foreach ($records as $row) {
                if ($row->foto !== null && trim((string) $row->foto) !== '') {
                    $items[] = [
                        'category' => self::CATEGORY_AFFIDAVIT,
                        'relative_path' => trim((string) $row->foto),
                        'table' => 'affidavit',
                        'id' => $row->id,
                        'field' => 'foto',
                    ];
                }
            }
        }

        // 4. User Profile Photos
        if ($categoryFilter === 'all' || $categoryFilter === self::CATEGORY_USER) {
            $records = User::query()->toBase()->whereNotNull('foto')->select(['id', 'foto'])->get();
            foreach ($records as $row) {
                if ($row->foto !== null && trim((string) $row->foto) !== '') {
                    $items[] = [
                        'category' => self::CATEGORY_USER,
                        'relative_path' => trim((string) $row->foto),
                        'table' => 'users',
                        'id' => $row->id,
                        'field' => 'foto',
                    ];
                }
            }
        }

        // 5. Branding Logos & Settings Icons
        if ($categoryFilter === 'all' || $categoryFilter === self::CATEGORY_BRANDING) {
            $logoKeys = ['app_logo_1', 'app_logo_2', 'app_logo_3', 'app_icon'];
            $records = Setting::query()->toBase()
                ->whereIn('key', $logoKeys)
                ->whereNotNull('value')
                ->select(['id', 'key', 'value'])
                ->get();

            foreach ($records as $row) {
                if ($row->value !== null && trim((string) $row->value) !== '') {
                    $items[] = [
                        'category' => self::CATEGORY_BRANDING,
                        'relative_path' => trim((string) $row->value),
                        'table' => 'settings',
                        'id' => $row->key ?? (string) $row->id,
                        'field' => 'value',
                    ];
                }
            }
        }

        return $this->normalizeAndDeduplicateRawItems($items, $categoryFilter);
    }

    /**
     * Normalize and deduplicate raw inventory references by relative path.
     *
     * @param  array<int, array<string, mixed>>  $rawItems
     * @return array<string, array{
     *     category: string,
     *     relative_path: string,
     *     references: list<array{table: string, id: int|string, field: string}>
     * }>
     */
    protected function normalizeAndDeduplicateRawItems(array $rawItems, string $categoryFilter): array
    {
        $deduplicated = [];

        foreach ($rawItems as $item) {
            $category = (string) ($item['category'] ?? '');
            $path = trim((string) ($item['relative_path'] ?? ''));

            if ($categoryFilter !== 'all' && $category !== $categoryFilter) {
                continue;
            }

            if ($path === '') {
                continue;
            }

            $table = (string) ($item['table'] ?? 'unknown');
            $id = $item['id'] ?? 0;
            $field = (string) ($item['field'] ?? 'unknown');

            if (! isset($deduplicated[$path])) {
                $deduplicated[$path] = [
                    'category' => $category,
                    'relative_path' => $path,
                    'references' => [],
                ];
            }

            $deduplicated[$path]['references'][] = [
                'table' => $table,
                'id' => $id,
                'field' => $field,
            ];
        }

        return $deduplicated;
    }

    /**
     * Validate path safety and prefix conformity.
     */
    public function validatePath(string $path, string $category): bool
    {
        if (trim($path) === '') {
            return false;
        }

        // 1. Reject path traversal, null bytes, backslashes, wildcards, and control characters
        if (
            str_contains($path, '..') ||
            str_contains($path, "\0") ||
            str_contains($path, '\\') ||
            str_starts_with($path, '/') ||
            preg_match('/[\x00-\x1F\x7F]/', $path) ||
            preg_match('/[*?\[\]]/', $path)
        ) {
            return false;
        }

        // 2. Reject paths not allowed by DocumentStorageService directory allowlist
        if (! $this->storageService->isPathAllowed($path)) {
            return false;
        }

        // 3. Category must be known
        if (! isset(self::CATEGORY_CONFIGS[$category])) {
            return false;
        }

        // 4. Must start with one of the allowed prefixes for the category
        $allowedPrefixes = self::CATEGORY_CONFIGS[$category]['allowed_prefixes'];
        $hasValidPrefix = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $hasValidPrefix = true;
                break;
            }
        }

        return $hasValidPrefix;
    }

    /**
     * Calculate SHA-256 hash and size of a stream in memory-safe 64KB chunks.
     *
     * @param  resource  $stream
     * @return array{sha256: string, size: int}
     */
    public function calculateStreamSha256($stream): array
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException('Expected valid PHP stream resource.');
        }

        try {
            $ctx = hash_init('sha256');
            $size = 0;

            while (! feof($stream)) {
                $chunk = fread($stream, 65536);
                if ($chunk === false) {
                    throw new RuntimeException('Failed reading stream chunk.');
                }
                hash_update($ctx, $chunk);
                $size += strlen($chunk);
            }

            return [
                'sha256' => hash_final($ctx),
                'size' => $size,
            ];
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Evaluate a single inventory item against source and destination disks.
     *
     * @param  array{
     *     category: string,
     *     relative_path: string,
     *     references: list<array{table: string, id: int|string, field: string}>
     * }  $item
     * @param  array{
     *     source_private: string,
     *     source_assets: string,
     *     dest_private: string,
     *     dest_assets: string
     * }  $diskMapping
     * @return array{
     *     category: string,
     *     relative_source_path: string,
     *     destination_key: string,
     *     source_disk: string,
     *     destination_disk: string,
     *     source_file_size: int,
     *     source_sha256: string|null,
     *     destination_sha256: string|null,
     *     status: string,
     *     error: string|null,
     *     references: list<array{table: string, id: int|string, field: string}>
     * }
     */
    public function evaluateItem(array $item, array $diskMapping): array
    {
        $category = $item['category'];
        $path = $item['relative_path'];
        $references = $item['references'];

        // 1. Resolve category mapping
        if (! isset(self::CATEGORY_CONFIGS[$category])) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => 'unknown',
                'destination_disk' => 'unknown',
                'source_file_size' => 0,
                'source_sha256' => null,
                'destination_sha256' => null,
                'status' => self::STATUS_UNRESOLVED_CATEGORY,
                'error' => "Kategori berkas [{$category}] tidak dikenal dalam konfigurasi migrasi.",
                'references' => $references,
            ];
        }

        $type = self::CATEGORY_CONFIGS[$category]['type'];
        $sourceDisk = $type === 'private' ? $diskMapping['source_private'] : $diskMapping['source_assets'];
        $destDisk = $type === 'private' ? $diskMapping['dest_private'] : $diskMapping['dest_assets'];

        // 2. Validate path safety and prefix allowlist
        if (! $this->validatePath($path, $category)) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => 0,
                'source_sha256' => null,
                'destination_sha256' => null,
                'status' => self::STATUS_REJECTED_PATH,
                'error' => "Path [{$path}] tidak aman atau tidak sesuai prefix direktori terkelola.",
                'references' => $references,
            ];
        }

        // 3. Check source file existence on source disk
        if (! Storage::disk($sourceDisk)->exists($path)) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => 0,
                'source_sha256' => null,
                'destination_sha256' => null,
                'status' => self::STATUS_SOURCE_MISSING,
                'error' => "Berkas sumber tidak ditemukan pada disk [{$sourceDisk}].",
                'references' => $references,
            ];
        }

        // 4. Read source file stream and compute source SHA-256 and size
        try {
            $sourceStream = Storage::disk($sourceDisk)->readStream($path);
            if (! is_resource($sourceStream)) {
                throw new RuntimeException("Gagal membuka stream berkas sumber [{$path}].");
            }
            $sourceMeta = $this->calculateStreamSha256($sourceStream);
            $sourceSha256 = $sourceMeta['sha256'];
            $sourceSize = $sourceMeta['size'];
        } catch (\Throwable $e) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => 0,
                'source_sha256' => null,
                'destination_sha256' => null,
                'status' => self::STATUS_SOURCE_READ_FAILED,
                'error' => 'Gagal membaca isi berkas sumber: '.$e->getMessage(),
                'references' => $references,
            ];
        }

        // 5. Check if destination already has the file with hardened existence verification
        $destCheck = $this->checkDestinationExistence($destDisk, $path);

        if (! empty($destCheck['error']) || $destCheck['status'] === self::STATUS_VERIFICATION_FAILED) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => $sourceSize,
                'source_sha256' => $sourceSha256,
                'destination_sha256' => null,
                'status' => self::STATUS_VERIFICATION_FAILED,
                'error' => $destCheck['error'] ?? "Gagal memeriksa keberadaan berkas tujuan pada disk [{$destDisk}].",
                'references' => $references,
            ];
        }

        if (! $destCheck['exists']) {
            if ($destCheck['confirmed_absent'] !== true) {
                // Ambiguous or unconfirmed absence MUST NOT be treated as READY_TO_MIGRATE
                return [
                    'category' => $category,
                    'relative_source_path' => $path,
                    'destination_key' => $path,
                    'source_disk' => $sourceDisk,
                    'destination_disk' => $destDisk,
                    'source_file_size' => $sourceSize,
                    'source_sha256' => $sourceSha256,
                    'destination_sha256' => null,
                    'status' => self::STATUS_VERIFICATION_FAILED,
                    'error' => "Respons pemeriksaan tujuan disk [{$destDisk}] tidak mengonfirmasi ketiadaan objek secara pasti.",
                    'references' => $references,
                ];
            }

            // Target does not exist yet (conclusively confirmed 404 absent): ready to migrate
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => $sourceSize,
                'source_sha256' => $sourceSha256,
                'destination_sha256' => null,
                'status' => self::STATUS_READY_TO_MIGRATE,
                'error' => null,
                'references' => $references,
            ];
        }

        // 6. Target exists: verify checksum to detect ALREADY_MATCHED vs CONFLICT
        try {
            $destStream = Storage::disk($destDisk)->readStream($path);
            if (! is_resource($destStream)) {
                throw new RuntimeException("Gagal membuka stream berkas tujuan [{$path}].");
            }
            $destMeta = $this->calculateStreamSha256($destStream);
            $destSha256 = $destMeta['sha256'];
        } catch (\Throwable $e) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => $sourceSize,
                'source_sha256' => $sourceSha256,
                'destination_sha256' => null,
                'status' => self::STATUS_VERIFICATION_FAILED,
                'error' => 'Gagal membaca dan memverifikasi isi berkas tujuan: '.$this->sanitizeErrorMessage($e->getMessage()),
                'references' => $references,
            ];
        }

        if ($destSha256 === $sourceSha256) {
            return [
                'category' => $category,
                'relative_source_path' => $path,
                'destination_key' => $path,
                'source_disk' => $sourceDisk,
                'destination_disk' => $destDisk,
                'source_file_size' => $sourceSize,
                'source_sha256' => $sourceSha256,
                'destination_sha256' => $destSha256,
                'status' => self::STATUS_ALREADY_MATCHED,
                'error' => null,
                'references' => $references,
            ];
        }

        // Destination exists with DIFFERENT checksum: CONFLICT (do not overwrite)
        return [
            'category' => $category,
            'relative_source_path' => $path,
            'destination_key' => $path,
            'source_disk' => $sourceDisk,
            'destination_disk' => $destDisk,
            'source_file_size' => $sourceSize,
            'source_sha256' => $sourceSha256,
            'destination_sha256' => $destSha256,
            'status' => self::STATUS_CONFLICT,
            'error' => "Konflik berkas: berkas tujuan pada disk [{$destDisk}] memiliki checksum SHA-256 yang berbeda.",
            'references' => $references,
        ];
    }

    /**
     * Check destination object existence with strict distinction between confirmed 404 absence
     * and verification failures (403, 5xx, timeout, transport errors, or ambiguous responses).
     *
     * @return array{
     *     exists: bool,
     *     confirmed_absent: bool,
     *     error: string|null,
     *     status: string|null
     * }
     */
    public function checkDestinationExistence(string $destDisk, string $path): array
    {
        if (self::$customDestinationChecker !== null) {
            $result = (self::$customDestinationChecker)($destDisk, $path);

            return [
                'exists' => ($result['exists'] ?? false) === true,
                'confirmed_absent' => ($result['confirmed_absent'] ?? false) === true,
                'error' => isset($result['error']) && is_string($result['error']) ? $this->sanitizeErrorMessage($result['error']) : null,
                'status' => isset($result['status']) && is_string($result['status']) ? $result['status'] : null,
            ];
        }

        try {
            $storage = Storage::disk($destDisk);

            // 1. Direct AWS S3 Client Inspection (if backed by AwsS3V3Adapter)
            if ($storage instanceof AwsS3V3Adapter) {
                return $this->checkS3DestinationExistence($storage, $destDisk, $path);
            }

            // 2. Flysystem Check for non-S3 disks with fail-closed absence validation
            return $this->checkNonS3DestinationExistence($storage, $destDisk, $path);
        } catch (UnableToCheckFileExistence $e) {
            return $this->handleUnableToCheckFileExistence($e, $destDisk, $path);
        } catch (\Throwable $e) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Gagal memeriksa keberadaan berkas tujuan pada disk [{$destDisk}]: ".$this->sanitizeErrorMessage($e->getMessage()),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }
    }

    /**
     * Check destination existence on non-S3 disks with fail-closed absence validation.
     *
     * @return array{
     *     exists: bool,
     *     confirmed_absent: bool,
     *     error: string|null,
     *     status: string|null
     * }
     */
    /**
     * Execute a callback while capturing PHP filesystem warnings.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return array{result: T, warning: string|null}
     */
    protected function executeWithWarningTrap(Closure $callback): array
    {
        $warningMessage = null;
        set_error_handler(function (int $errno, string $errstr) use (&$warningMessage): bool {
            $warningMessage = $errstr;

            return true;
        });

        try {
            $result = $callback();

            return [
                'result' => $result,
                'warning' => $warningMessage,
            ];
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Check destination existence on non-S3 disks with fail-closed absence validation.
     *
     * @return array{
     *     exists: bool,
     *     confirmed_absent: bool,
     *     error: string|null,
     *     status: string|null
     * }
     */
    protected function checkNonS3DestinationExistence(FilesystemAdapter $storage, string $destDisk, string $path): array
    {
        // 1. Check existence via Flysystem with warning trapping
        $fileCheck = $this->executeWithWarningTrap(fn (): bool => $storage->fileExists($path));

        if ($fileCheck['warning'] !== null) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Peringatan sistem berkas pada disk [{$destDisk}] untuk [{$path}]: ".$this->sanitizeErrorMessage($fileCheck['warning']),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }

        // Object is present as regular file -> proceed to checksum verification
        if ($fileCheck['result']) {
            return [
                'exists' => true,
                'confirmed_absent' => false,
                'error' => null,
                'status' => null,
            ];
        }

        // 2. Object was not found by fileExists(). Conclusively prove absence (Fail-Closed).
        $adapter = $storage->getAdapter();

        // If adapter is a LocalFilesystemAdapter, inspect entry type and directory tree permissions
        if ($adapter instanceof LocalFilesystemAdapter) {
            $localInspection = $this->executeWithWarningTrap(function () use ($storage, $destDisk, $path): array {
                $fullPath = $storage->path($path);

                // Detect dangling symlinks, directories, or special entries
                if (is_link($fullPath) || file_exists($fullPath)) {
                    $entryType = is_link($fullPath) ? 'link/symlink' : (is_dir($fullPath) ? 'direktori' : 'non-regular entry');

                    return [
                        'valid' => false,
                        'error' => "Target pada disk [{$destDisk}] untuk [{$path}] bukan berkas reguler (ditemukan sebagai {$entryType}).",
                    ];
                }

                // Inspect directory tree permissions
                $dir = dirname($fullPath);
                $parent = $dir;
                while (! is_dir($parent) && $parent !== dirname($parent)) {
                    $parent = dirname($parent);
                }

                if (! is_dir($parent) || ! is_readable($parent) || ! is_executable($parent)) {
                    return [
                        'valid' => false,
                        'error' => "Direktori pada disk [{$destDisk}] untuk [{$path}] tidak dapat dibaca atau izin ditolak (Permission Denied).",
                    ];
                }

                return [
                    'valid' => true,
                    'error' => null,
                ];
            });

            if ($localInspection['warning'] !== null) {
                return [
                    'exists' => false,
                    'confirmed_absent' => false,
                    'error' => "Peringatan sistem berkas pada disk [{$destDisk}] untuk [{$path}]: ".$this->sanitizeErrorMessage($localInspection['warning']),
                    'status' => self::STATUS_VERIFICATION_FAILED,
                ];
            }

            if (! $localInspection['result']['valid']) {
                return [
                    'exists' => false,
                    'confirmed_absent' => false,
                    'error' => $localInspection['result']['error'],
                    'status' => self::STATUS_VERIFICATION_FAILED,
                ];
            }

            // Conclusively confirmed absent on local filesystem
            return [
                'exists' => false,
                'confirmed_absent' => true,
                'error' => null,
                'status' => null,
            ];
        }

        // Unknown / non-local adapter that cannot prove absence conclusively: FAIL CLOSED
        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => 'Adapter penyimpanan ['.get_class($adapter)."] pada disk [{$destDisk}] tidak menyediakan mekanisme pembuktian ketiadaan berkas yang konklusif.",
            'status' => self::STATUS_VERIFICATION_FAILED,
        ];
    }

    /**
     * Check existence on S3-compatible disk via direct HeadObject to accurately capture status codes.
     *
     * @return array{
     *     exists: bool,
     *     confirmed_absent: bool,
     *     error: string|null,
     *     status: string|null
     * }
     */
    protected function checkS3DestinationExistence(AwsS3V3Adapter $storage, string $destDisk, string $path): array
    {
        $client = $storage->getClient();
        $config = $storage->getConfig();
        $bucket = (string) ($config['bucket'] ?? '');
        $prefix = (string) ($config['root'] ?? $config['prefix'] ?? '');
        $fullKey = $prefix !== '' ? rtrim($prefix, '/').'/'.ltrim($path, '/') : $path;

        try {
            $client->headObject([
                'Bucket' => $bucket,
                'Key' => $fullKey,
            ]);

            return [
                'exists' => true,
                'confirmed_absent' => false,
                'error' => null,
                'status' => null,
            ];
        } catch (S3Exception $e) {
            $statusCode = (int) $e->getStatusCode();
            $errorCode = (string) $e->getAwsErrorCode();

            // 404 Not Found is conclusively absent
            if ($statusCode === 404 || $errorCode === 'NotFound' || $errorCode === 'NoSuchKey') {
                return [
                    'exists' => false,
                    'confirmed_absent' => true,
                    'error' => null,
                    'status' => null,
                ];
            }

            // 403 Forbidden / Access Denied
            if ($statusCode === 403 || $errorCode === 'AccessDenied') {
                return [
                    'exists' => false,
                    'confirmed_absent' => false,
                    'error' => "Akses ditolak (HTTP 403 / Access Denied) pada disk [{$destDisk}] untuk berkas [{$path}].",
                    'status' => self::STATUS_VERIFICATION_FAILED,
                ];
            }

            // 5xx Server Error
            if ($statusCode >= 500 && $statusCode <= 599) {
                return [
                    'exists' => false,
                    'confirmed_absent' => false,
                    'error' => "Kesalahan server penyimpanan (HTTP {$statusCode}) pada disk [{$destDisk}] untuk berkas [{$path}].",
                    'status' => self::STATUS_VERIFICATION_FAILED,
                ];
            }

            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Pemeriksaan berkas tujuan pada disk [{$destDisk}] gagal (HTTP {$statusCode}): ".$this->sanitizeErrorMessage($e->getMessage()),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        } catch (\Throwable $e) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Gangguan koneksi atau timeout pada disk [{$destDisk}] saat memeriksa [{$path}]: ".$this->sanitizeErrorMessage($e->getMessage()),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }
    }

    /**
     * Inspect previous exception inside UnableToCheckFileExistence for accurate categorization.
     *
     * @return array{
     *     exists: bool,
     *     confirmed_absent: bool,
     *     error: string|null,
     *     status: string|null
     * }
     */
    protected function handleUnableToCheckFileExistence(UnableToCheckFileExistence $e, string $destDisk, string $path): array
    {
        $prev = $e->getPrevious();
        if ($prev instanceof S3Exception) {
            $statusCode = (int) $prev->getStatusCode();
            $errorCode = (string) $prev->getAwsErrorCode();

            if ($statusCode === 404 || $errorCode === 'NotFound' || $errorCode === 'NoSuchKey') {
                return [
                    'exists' => false,
                    'confirmed_absent' => true,
                    'error' => null,
                    'status' => null,
                ];
            }

            if ($statusCode === 403 || $errorCode === 'AccessDenied') {
                return [
                    'exists' => false,
                    'confirmed_absent' => false,
                    'error' => "Akses ditolak (HTTP 403 / Access Denied) pada disk [{$destDisk}] untuk berkas [{$path}].",
                    'status' => self::STATUS_VERIFICATION_FAILED,
                ];
            }

            if ($statusCode >= 500 && $statusCode <= 599) {
                return [
                    'exists' => false,
                    'confirmed_absent' => false,
                    'error' => "Kesalahan server penyimpanan (HTTP {$statusCode}) pada disk [{$destDisk}] untuk berkas [{$path}].",
                    'status' => self::STATUS_VERIFICATION_FAILED,
                ];
            }

            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Pemeriksaan berkas tujuan pada disk [{$destDisk}] gagal (HTTP {$statusCode}): ".$this->sanitizeErrorMessage($prev->getMessage()),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }

        $rawMessage = $prev ? $prev->getMessage() : $e->getMessage();
        $lowerMsg = strtolower($rawMessage);

        if (str_contains($lowerMsg, 'timed out') || str_contains($lowerMsg, 'timeout') || str_contains($lowerMsg, 'curl error 28')) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Pemeriksaan berkas tujuan pada disk [{$destDisk}] mengalami timeout: ".$this->sanitizeErrorMessage($rawMessage),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }

        if (str_contains($lowerMsg, 'could not resolve host') || str_contains($lowerMsg, 'connection refused') || str_contains($lowerMsg, 'network is unreachable')) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Gangguan jaringan saat memeriksa berkas tujuan pada disk [{$destDisk}]: ".$this->sanitizeErrorMessage($rawMessage),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }

        if (str_contains($lowerMsg, 'permission denied') || str_contains($lowerMsg, 'access denied') || str_contains($lowerMsg, 'unauthorized')) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Akses atau izin ditolak pada disk [{$destDisk}] untuk berkas [{$path}]: ".$this->sanitizeErrorMessage($rawMessage),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }

        if (str_contains($lowerMsg, 'input/output error') || str_contains($lowerMsg, 'i/o error') || str_contains($lowerMsg, 'disk full')) {
            return [
                'exists' => false,
                'confirmed_absent' => false,
                'error' => "Kesalahan I/O sistem berkas pada disk [{$destDisk}] untuk berkas [{$path}]: ".$this->sanitizeErrorMessage($rawMessage),
                'status' => self::STATUS_VERIFICATION_FAILED,
            ];
        }

        return [
            'exists' => false,
            'confirmed_absent' => false,
            'error' => "Gagal memeriksa keberadaan berkas tujuan pada disk [{$destDisk}]: ".$this->sanitizeErrorMessage($rawMessage),
            'status' => self::STATUS_VERIFICATION_FAILED,
        ];
    }

    /**
     * Sanitize sensitive credentials from error messages before storing in manifest or output.
     */
    public function sanitizeErrorMessage(string $message): string
    {
        $sanitized = preg_replace('/(AWSAccessKeyId|Signature|key|secret|token)=([^&\s"\']+)/i', '$1=[REDACTED]', $message) ?? $message;
        $sanitized = preg_replace('/(Authorization:\s*AWS[^\s\r\n]+)/i', 'Authorization: [REDACTED]', $sanitized) ?? $sanitized;

        $basePath = base_path();
        if ($basePath !== '') {
            $sanitized = str_replace($basePath, '[APP_ROOT]', $sanitized);
        }

        return $sanitized;
    }

    /**
     * Generate structured manifest file.
     *
     * @param  list<array<string, mixed>>  $evaluatedItems
     * @param  array{
     *     run_id: string,
     *     mode: string,
     *     source_private: string,
     *     source_assets: string,
     *     dest_private: string,
     *     dest_assets: string,
     *     manifest_dir: string
     * }  $context
     * @return array{
     *     filename: string,
     *     path: string,
     *     summary: array<string, int|string>
     * }
     */
    public function generateManifest(array $evaluatedItems, array $context): array
    {
        $runId = $context['run_id'];
        $timestamp = now()->toISOString();
        $dateSlug = now()->format('Ymd-His');
        $filename = "migration-manifest-{$runId}-{$dateSlug}.json";
        $manifestDir = trim($context['manifest_dir'], '/');
        $relativeManifestPath = "{$manifestDir}/{$filename}";

        $summary = [
            'total_database_references' => 0,
            'unique_file_paths' => count($evaluatedItems),
            'ready_to_migrate' => 0,
            'already_matched' => 0,
            'migrated' => 0,
            'source_missing' => 0,
            'conflicts' => 0,
            'rejected_paths' => 0,
            'unresolved_categories' => 0,
            'source_read_failures' => 0,
            'verification_failures' => 0,
            'upload_failures' => 0,
            'total_source_bytes' => 0,
        ];

        foreach ($evaluatedItems as $item) {
            $summary['total_database_references'] += count($item['references'] ?? []);
            $summary['total_source_bytes'] += (int) ($item['source_file_size'] ?? 0);

            switch ($item['status'] ?? '') {
                case self::STATUS_READY_TO_MIGRATE:
                    $summary['ready_to_migrate']++;
                    break;
                case self::STATUS_ALREADY_MATCHED:
                    $summary['already_matched']++;
                    break;
                case self::STATUS_MIGRATED:
                    $summary['migrated']++;
                    break;
                case self::STATUS_CONFLICT:
                    $summary['conflicts']++;
                    break;
                case self::STATUS_SOURCE_MISSING:
                    $summary['source_missing']++;
                    break;
                case self::STATUS_REJECTED_PATH:
                    $summary['rejected_paths']++;
                    break;
                case self::STATUS_UNRESOLVED_CATEGORY:
                    $summary['unresolved_categories']++;
                    break;
                case self::STATUS_SOURCE_READ_FAILED:
                    $summary['source_read_failures']++;
                    break;
                case self::STATUS_VERIFICATION_FAILED:
                    $summary['verification_failures']++;
                    break;
                case self::STATUS_UPLOAD_FAILED:
                    $summary['upload_failures']++;
                    break;
            }
        }

        $manifestData = [
            'migration_run_id' => $runId,
            'timestamp' => $timestamp,
            'mode' => $context['mode'],
            'storage_backend_configured' => config('filesystems.backend', 'local'),
            'disks' => [
                'source_private' => $context['source_private'],
                'source_assets' => $context['source_assets'],
                'dest_private' => $context['dest_private'],
                'dest_assets' => $context['dest_assets'],
            ],
            'summary' => $summary,
            'items' => $evaluatedItems,
        ];

        $json = json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Gagal melakukan serialisasi JSON untuk manifest migrasi.');
        }

        // Atomic file write using temporary file and rename to protect against partial/corrupt writes
        $storage = Storage::disk('local');
        $adapter = $storage->getAdapter();

        if ($adapter instanceof LocalFilesystemAdapter) {
            $fullPath = $storage->path($relativeManifestPath);
            $dir = dirname($fullPath);
            if (! is_dir($dir)) {
                if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
                    throw new RuntimeException("Gagal membuat direktori manifest [{$dir}].");
                }
            }

            $tempPath = $fullPath.'.tmp.'.uniqid('', true);
            $written = @file_put_contents($tempPath, $json, LOCK_EX);

            if ($written === false || $written !== strlen($json)) {
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                throw new RuntimeException("Gagal menulis data ke berkas manifest sementara [{$tempPath}].");
            }

            if (! @rename($tempPath, $fullPath)) {
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                throw new RuntimeException("Gagal memindahkan berkas manifest sementara menjadi [{$fullPath}].");
            }
        } else {
            $storage->put($relativeManifestPath, $json);
        }

        return [
            'filename' => $filename,
            'path' => $relativeManifestPath,
            'summary' => $summary,
        ];
    }

    /**
     * Verify whether an item is safe to transfer and ensure destination has not changed or conflicted.
     *
     * @param  array<string, mixed>  $evaluatedItem
     * @return array{
     *     safe: bool,
     *     status: string,
     *     error: string|null
     * }
     */
    public function verifyPreTransferSafety(array $evaluatedItem): array
    {
        $destDisk = (string) ($evaluatedItem['destination_disk'] ?? '');
        $path = (string) ($evaluatedItem['destination_key'] ?? '');
        $sourceSha256 = (string) ($evaluatedItem['source_sha256'] ?? '');

        // Re-check destination existence immediately before transfer
        $destCheck = $this->checkDestinationExistence($destDisk, $path);

        if ($destCheck['exists']) {
            // Destination appeared (race condition / concurrent upload / pre-existing)
            try {
                $destStream = Storage::disk($destDisk)->readStream($path);
                if (! is_resource($destStream)) {
                    return [
                        'safe' => false,
                        'status' => self::STATUS_VERIFICATION_FAILED,
                        'error' => "Objek tujuan [{$path}] muncul tetapi stream tidak dapat dibaca.",
                    ];
                }
                $destMeta = $this->calculateStreamSha256($destStream);
                if ($destMeta['sha256'] === $sourceSha256) {
                    return [
                        'safe' => false,
                        'status' => self::STATUS_ALREADY_MATCHED,
                        'error' => null,
                    ];
                }

                return [
                    'safe' => false,
                    'status' => self::STATUS_CONFLICT,
                    'error' => "Objek tujuan [{$path}] muncul dengan checksum SHA-256 berbeda (Konflik).",
                ];
            } catch (\Throwable $e) {
                return [
                    'safe' => false,
                    'status' => self::STATUS_VERIFICATION_FAILED,
                    'error' => 'Gagal memverifikasi objek tujuan yang baru muncul: '.$e->getMessage(),
                ];
            }
        }

        if ($destCheck['confirmed_absent'] !== true) {
            return [
                'safe' => false,
                'status' => self::STATUS_VERIFICATION_FAILED,
                'error' => $destCheck['error'] ?? "Ketiadaan objek tujuan disk [{$destDisk}] tidak dapat dipastikan.",
            ];
        }

        return [
            'safe' => true,
            'status' => self::STATUS_READY_TO_MIGRATE,
            'error' => null,
        ];
    }

    /**
     * Transfer a single evaluated item from source storage to destination storage with fail-closed safety.
     *
     * @param  array<string, mixed>  $evaluatedItem
     * @return array<string, mixed>
     */
    public function transferItem(array $evaluatedItem): array
    {
        if (self::$customTransferHandler !== null) {
            $customResult = (self::$customTransferHandler)($evaluatedItem);
            if ($customResult !== null) {
                return $customResult;
            }
        }

        $resultItem = $evaluatedItem;
        $sourceDisk = (string) ($evaluatedItem['source_disk'] ?? '');
        $destDisk = (string) ($evaluatedItem['destination_disk'] ?? '');
        $path = (string) ($evaluatedItem['relative_source_path'] ?? '');
        $destKey = (string) ($evaluatedItem['destination_key'] ?? $path);
        $sourceSha256 = (string) ($evaluatedItem['source_sha256'] ?? '');

        // 1. If item was not ready to migrate, skip transfer
        if (($evaluatedItem['status'] ?? '') !== self::STATUS_READY_TO_MIGRATE) {
            return $resultItem;
        }

        // 2. Pre-transfer safety verification immediately before upload
        $preSafety = $this->verifyPreTransferSafety($evaluatedItem);
        if (! $preSafety['safe']) {
            $resultItem['status'] = $preSafety['status'];
            $resultItem['error'] = $preSafety['error'];

            return $resultItem;
        }

        // 3. Open source stream
        $sourceStream = null;
        try {
            $sourceStream = Storage::disk($sourceDisk)->readStream($path);
            if (! is_resource($sourceStream)) {
                $resultItem['status'] = self::STATUS_SOURCE_READ_FAILED;
                $resultItem['error'] = "Gagal membuka stream berkas sumber [{$path}] pada disk [{$sourceDisk}].";

                return $resultItem;
            }

            // 4. Write stream to destination disk
            $destStorage = Storage::disk($destDisk);
            $written = $destStorage->writeStream($destKey, $sourceStream);
            if ($written === false) {
                $resultItem['status'] = self::STATUS_UPLOAD_FAILED;
                $resultItem['error'] = "Penulisan stream ke berkas tujuan [{$destKey}] pada disk [{$destDisk}] gagal.";

                return $resultItem;
            }
        } catch (\Throwable $e) {
            $resultItem['status'] = self::STATUS_UPLOAD_FAILED;
            $resultItem['error'] = "Gagal mengunggah berkas [{$path}] ke disk [{$destDisk}]: ".$this->sanitizeErrorMessage($e->getMessage());

            return $resultItem;
        } finally {
            if (is_resource($sourceStream)) {
                fclose($sourceStream);
            }
        }

        // 5. Post-transfer checksum verification: re-read destination object
        $destStream = null;
        try {
            $destStream = Storage::disk($destDisk)->readStream($destKey);
            if (! is_resource($destStream)) {
                $resultItem['status'] = self::STATUS_VERIFICATION_FAILED;
                $resultItem['error'] = "Berkas tujuan [{$destKey}] berhasil diunggah tetapi gagal dibaca ulang untuk verifikasi checksum.";

                return $resultItem;
            }

            $destMeta = $this->calculateStreamSha256($destStream);
            $destSha256 = $destMeta['sha256'];
            $resultItem['destination_sha256'] = $destSha256;

            if ($destSha256 !== $sourceSha256) {
                $resultItem['status'] = self::STATUS_VERIFICATION_FAILED;
                $resultItem['error'] = "Verifikasi integritas gagal: Checksum SHA-256 tujuan [{$destSha256}] tidak cocok dengan sumber [{$sourceSha256}].";

                return $resultItem;
            }

            // Successfully migrated!
            $resultItem['status'] = self::STATUS_MIGRATED;
            $resultItem['error'] = null;

            return $resultItem;
        } catch (\Throwable $e) {
            $resultItem['status'] = self::STATUS_VERIFICATION_FAILED;
            $resultItem['error'] = "Gagal memverifikasi checksum berkas tujuan [{$destKey}] setelah upload: ".$this->sanitizeErrorMessage($e->getMessage());

            return $resultItem;
        } finally {
            if (is_resource($destStream)) {
                fclose($destStream);
            }
        }
    }

    /**
     * Validate current inventory or evaluated items against a baseline dry-run manifest.
     *
     * @param  string  $manifestPath  Path to baseline manifest (absolute or relative to local disk root)
     * @param  list<array<string, mixed>>  $currentItems  Evaluated items from current run
     * @return array{
     *     valid: bool,
     *     errors: list<string>,
     *     differences: array<string, mixed>,
     *     manifest_summary: array<string, mixed>|null
     * }
     */
    public function validateInventoryAgainstManifest(string $manifestPath, array $currentItems): array
    {
        $errors = [];
        $differences = [];

        // 1. Resolve manifest file
        $storage = Storage::disk('local');
        $rawJson = null;

        if (file_exists($manifestPath) && is_readable($manifestPath)) {
            $rawJson = @file_get_contents($manifestPath);
        } elseif ($storage->fileExists($manifestPath)) {
            try {
                $rawJson = $storage->get($manifestPath);
            } catch (\Throwable) {
                $rawJson = null;
            }
        } else {
            $adapter = $storage->getAdapter();
            if ($adapter instanceof LocalFilesystemAdapter) {
                $candidate = $storage->path($manifestPath);
                if (file_exists($candidate) && is_readable($candidate)) {
                    $rawJson = @file_get_contents($candidate);
                }
            }
        }

        if ($rawJson === null || $rawJson === false || trim($rawJson) === '') {
            return [
                'valid' => false,
                'errors' => ["Berkas manifest acuan [{$manifestPath}] tidak ditemukan atau tidak dapat dibaca."],
                'differences' => ['manifest_missing' => true],
                'manifest_summary' => null,
            ];
        }

        // 2. Decode and validate JSON structure
        $data = json_decode($rawJson, true);
        if (! is_array($data) || ! isset($data['summary']) || ! isset($data['items']) || ! is_array($data['items']) || ! is_array($data['summary'])) {
            return [
                'valid' => false,
                'errors' => ["Format berkas manifest acuan [{$manifestPath}] tidak valid atau rusak."],
                'differences' => ['invalid_manifest_structure' => true],
                'manifest_summary' => null,
            ];
        }

        /** @var array<string, mixed> $manifestSummary */
        $manifestSummary = $data['summary'];

        // 3. Compare aggregate counts
        $currentRefCount = 0;
        foreach ($currentItems as $ci) {
            $currentRefCount += count($ci['references'] ?? []);
        }
        $manifestRefCount = (int) ($manifestSummary['total_database_references'] ?? 0);
        if ($currentRefCount !== $manifestRefCount) {
            $errors[] = "Jumlah referensi basis data tidak cocok (manifest: {$manifestRefCount}, aktual: {$currentRefCount}).";
            $differences['database_references_mismatch'] = [
                'manifest' => $manifestRefCount,
                'current' => $currentRefCount,
            ];
        }

        $currentUniqueCount = count($currentItems);
        $manifestUniqueCount = (int) ($manifestSummary['unique_file_paths'] ?? 0);
        if ($currentUniqueCount !== $manifestUniqueCount) {
            $errors[] = "Jumlah berkas unik tidak cocok (manifest: {$manifestUniqueCount}, aktual: {$currentUniqueCount}).";
            $differences['unique_files_mismatch'] = [
                'manifest' => $manifestUniqueCount,
                'current' => $currentUniqueCount,
            ];
        }

        // 4. Map items by relative_source_path
        /** @var array<string, array<string, mixed>> $manifestMap */
        $manifestMap = [];
        foreach ($data['items'] as $mItem) {
            if (is_array($mItem) && isset($mItem['relative_source_path']) && is_string($mItem['relative_source_path'])) {
                $manifestMap[$mItem['relative_source_path']] = $mItem;
            }
        }

        /** @var array<string, array<string, mixed>> $currentMap */
        $currentMap = [];
        foreach ($currentItems as $cItem) {
            if (isset($cItem['relative_source_path']) && is_string($cItem['relative_source_path'])) {
                $currentMap[$cItem['relative_source_path']] = $cItem;
            }
        }

        // Detect missing files (in manifest, missing in current)
        foreach ($manifestMap as $relPath => $mItem) {
            if (! isset($currentMap[$relPath])) {
                $errors[] = "Berkas [{$relPath}] tercatat dalam manifest tetapi hilang dari inventaris terkini.";
                $differences['missing_files'][] = $relPath;
            }
        }

        // Detect new files (in current, missing in manifest)
        foreach ($currentMap as $relPath => $cItem) {
            if (! isset($manifestMap[$relPath])) {
                $errors[] = "Berkas baru [{$relPath}] ditemukan di inventaris tetapi tidak ada dalam manifest acuan.";
                $differences['new_files'][] = $relPath;
            }
        }

        // 5. Compare individual item properties
        foreach ($currentMap as $relPath => $cItem) {
            if (! isset($manifestMap[$relPath])) {
                continue;
            }
            $mItem = $manifestMap[$relPath];

            if (($cItem['category'] ?? '') !== ($mItem['category'] ?? '')) {
                $errors[] = "Kategori berkas [{$relPath}] berubah (manifest: {$mItem['category']}, aktual: {$cItem['category']}).";
            }

            if (($cItem['source_disk'] ?? '') !== ($mItem['source_disk'] ?? '')) {
                $errors[] = "Source disk berkas [{$relPath}] berubah (manifest: {$mItem['source_disk']}, aktual: {$cItem['source_disk']}).";
            }

            if (($cItem['destination_disk'] ?? '') !== ($mItem['destination_disk'] ?? '')) {
                $errors[] = "Destination disk berkas [{$relPath}] berubah (manifest: {$mItem['destination_disk']}, aktual: {$cItem['destination_disk']}).";
            }

            $currentSize = (int) ($cItem['source_file_size'] ?? 0);
            $manifestSize = (int) ($mItem['source_file_size'] ?? 0);
            if ($currentSize !== $manifestSize) {
                $errors[] = "Ukuran berkas [{$relPath}] berubah (manifest: {$manifestSize} B, aktual: {$currentSize} B).";
                $differences['size_mismatch'][$relPath] = [
                    'manifest' => $manifestSize,
                    'current' => $currentSize,
                ];
            }

            $currentSha = (string) ($cItem['source_sha256'] ?? '');
            $manifestSha = (string) ($mItem['source_sha256'] ?? '');
            if ($currentSha !== $manifestSha) {
                $errors[] = "Checksum SHA-256 berkas [{$relPath}] berubah (manifest: {$manifestSha}, aktual: {$currentSha}).";
                $differences['sha256_mismatch'][$relPath] = [
                    'manifest' => $manifestSha,
                    'current' => $currentSha,
                ];
            }

            $status = (string) ($cItem['status'] ?? '');
            if ($status === self::STATUS_SOURCE_MISSING) {
                $errors[] = "Berkas sumber [{$relPath}] hilang dari penyimpanan lokal.";
            } elseif ($status === self::STATUS_SOURCE_READ_FAILED) {
                $errors[] = "Gagal membaca berkas sumber [{$relPath}]: ".($cItem['error'] ?? '');
            } elseif ($status === self::STATUS_VERIFICATION_FAILED) {
                $errors[] = "Verifikasi tujuan berkas [{$relPath}] gagal: ".($cItem['error'] ?? '');
            }

            // Deep comparison of database reference identities (table, id, field)
            $manifestRefs = is_array($mItem['references'] ?? null) ? $mItem['references'] : [];
            $currentRefs = is_array($cItem['references'] ?? null) ? $cItem['references'] : [];

            $manifestRefMap = [];
            foreach ($manifestRefs as $ref) {
                if (! is_array($ref)) {
                    continue;
                }
                $t = trim((string) ($ref['table'] ?? ''));
                $id = trim((string) ($ref['id'] ?? ''));
                $f = trim((string) ($ref['field'] ?? ''));
                $sig = "{$t}#{$id}#{$f}";
                $manifestRefMap[$sig] = [
                    'table' => $t,
                    'id' => $id,
                    'field' => $f,
                ];
            }

            $currentRefMap = [];
            foreach ($currentRefs as $ref) {
                if (! is_array($ref)) {
                    continue;
                }
                $t = trim((string) ($ref['table'] ?? ''));
                $id = trim((string) ($ref['id'] ?? ''));
                $f = trim((string) ($ref['field'] ?? ''));
                $sig = "{$t}#{$id}#{$f}";
                $currentRefMap[$sig] = [
                    'table' => $t,
                    'id' => $id,
                    'field' => $f,
                ];
            }

            $missingRefs = [];
            foreach ($manifestRefMap as $sig => $refInfo) {
                if (! isset($currentRefMap[$sig])) {
                    $missingRefs[] = $refInfo;
                }
            }

            $newRefs = [];
            foreach ($currentRefMap as $sig => $refInfo) {
                if (! isset($manifestRefMap[$sig])) {
                    $newRefs[] = $refInfo;
                }
            }

            if (! empty($missingRefs) || ! empty($newRefs)) {
                foreach ($missingRefs as $mRef) {
                    $errors[] = "Referensi basis data [{$mRef['table']}.{$mRef['field']} ID:{$mRef['id']}] untuk berkas [{$relPath}] hilang dari inventaris terkini.";
                }
                foreach ($newRefs as $nRef) {
                    $errors[] = "Referensi basis data baru [{$nRef['table']}.{$nRef['field']} ID:{$nRef['id']}] ditemukan untuk berkas [{$relPath}] tetapi tidak ada dalam manifest acuan.";
                }

                $differences['reference_identity_mismatches'][$relPath] = [
                    'missing' => $missingRefs,
                    'new' => $newRefs,
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'differences' => $differences,
            'manifest_summary' => $manifestSummary,
        ];
    }
}
