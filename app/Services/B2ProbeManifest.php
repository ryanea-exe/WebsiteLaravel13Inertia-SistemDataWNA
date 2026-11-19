<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class B2ProbeManifest
{
    /**
     * Mandatory prefix for all probe objects.
     */
    public const PROBE_PREFIX = '__stage_4e_probe__';

    /**
     * Cleanup three-state constants.
     */
    public const STATUS_REGISTERED = 'REGISTERED';

    public const STATUS_CREATED = 'CREATED';

    public const STATUS_DELETED_CONFIRMED = 'DELETED_CONFIRMED';

    public const STATUS_RESIDUAL_CONFIRMED = 'RESIDUAL_CONFIRMED';

    public const STATUS_CLEANUP_UNVERIFIED = 'CLEANUP_UNVERIFIED';

    /**
     * Allowed base directories for probe objects.
     *
     * @var array<int, string>
     */
    public const ALLOWED_BASE_DIRS = [
        'wna/photos',
        'settings/icons',
    ];

    /**
     * In-memory registry of probe records for this session.
     *
     * @var array<string, array{
     *     session_id: string,
     *     disk: string,
     *     key: string,
     *     uuid: string,
     *     created: bool,
     *     deleted: bool,
     *     cleanup_status: string,
     *     timestamp: int
     * }>
     */
    protected array $records = [];

    public function __construct(
        protected string $sessionId
    ) {
        if (trim($sessionId) === '') {
            throw new InvalidArgumentException('Session ID probe tidak boleh kosong.');
        }
    }

    /**
     * Get the active session identifier.
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Generate and register a valid probe key for a given disk and module.
     */
    public function registerProbe(string $disk, string $module, string $uuid, string $extension): string
    {
        $cleanUuid = preg_replace('/[^a-zA-Z0-9_-]/', '', $uuid);
        if ($cleanUuid === '' || $cleanUuid !== $uuid) {
            throw new InvalidArgumentException("UUID probe [{$uuid}] tidak valid.");
        }

        $cleanExt = strtolower(trim($extension, '. '));
        if (! in_array($cleanExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new InvalidArgumentException("Ekstensi probe [{$cleanExt}] tidak didukung.");
        }

        $baseDir = match ($module) {
            'wna', 'private' => 'wna/photos',
            'settings', 'assets' => 'settings/icons',
            default => throw new InvalidArgumentException("Modul probe [{$module}] tidak dikenali."),
        };

        $prefix = $module === 'settings' || $module === 'assets' ? 'asset-probe' : 'private-probe';
        $key = "{$baseDir}/".self::PROBE_PREFIX."/{$prefix}-{$cleanUuid}.{$cleanExt}";

        if (! $this->validateProbeKey($key)) {
            throw new InvalidArgumentException("Key probe yang dihasilkan [{$key}] tidak valid.");
        }

        if (isset($this->records[$key])) {
            throw new InvalidArgumentException("Key probe [{$key}] sudah pernah didaftarkan pada sesi ini.");
        }

        $this->records[$key] = [
            'session_id' => $this->sessionId,
            'disk' => $disk,
            'key' => $key,
            'uuid' => $cleanUuid,
            'created' => false,
            'deleted' => false,
            'cleanup_status' => self::STATUS_REGISTERED,
            'timestamp' => time(),
        ];

        return $key;
    }

    /**
     * Validate whether a storage key is strictly a valid probe object path.
     */
    public function validateProbeKey(string $key): bool
    {
        if (trim($key) === '' || str_contains($key, '..') || str_contains($key, "\0") || str_contains($key, '\\')) {
            return false;
        }

        // Must reject control characters and wildcards
        if (preg_match('/[\x00-\x1F\x7F]/', $key) || preg_match('/[*?\[\]]/', $key)) {
            return false;
        }

        // Must contain probe prefix
        if (! str_contains($key, '/'.self::PROBE_PREFIX.'/')) {
            return false;
        }

        // Must start with one of the allowed base directories
        $hasValidBase = false;
        foreach (self::ALLOWED_BASE_DIRS as $baseDir) {
            if (str_starts_with($key, $baseDir.'/'.self::PROBE_PREFIX.'/')) {
                $hasValidBase = true;
                break;
            }
        }

        if (! $hasValidBase) {
            return false;
        }

        // Ensure key does not attempt directory deletion or wildcard
        $filename = basename($key);
        if ($filename === '' || $filename === '.' || $filename === '..' || str_contains($filename, '*')) {
            return false;
        }

        return true;
    }

    /**
     * Mark a probe object as successfully created.
     */
    public function markCreated(string $key): void
    {
        if (! isset($this->records[$key])) {
            throw new RuntimeException("Kunci probe [{$key}] tidak terdaftar dalam sesi manifest ini.");
        }

        $this->records[$key]['created'] = true;
        $this->records[$key]['cleanup_status'] = self::STATUS_CREATED;
    }

    /**
     * Mark a probe object as successfully deleted.
     */
    public function markDeleted(string $key, ?string $disk = null): void
    {
        if (! isset($this->records[$key])) {
            throw new RuntimeException("Kunci probe [{$key}] tidak terdaftar dalam sesi manifest ini.");
        }

        if ($disk !== null && $this->records[$key]['disk'] !== $disk) {
            throw new RuntimeException("Disk [{$disk}] tidak sesuai dengan disk pendaftaran [{$this->records[$key]['disk']}].");
        }

        $this->records[$key]['deleted'] = true;
        $this->records[$key]['cleanup_status'] = self::STATUS_DELETED_CONFIRMED;
    }

    /**
     * Update the cleanup three-state for a probe object.
     */
    public function markCleanupStatus(string $key, string $status, ?string $disk = null): void
    {
        if (! isset($this->records[$key])) {
            throw new RuntimeException("Kunci probe [{$key}] tidak terdaftar dalam sesi manifest ini.");
        }

        if ($disk !== null && $this->records[$key]['disk'] !== $disk) {
            throw new RuntimeException("Disk [{$disk}] tidak sesuai dengan disk pendaftaran [{$this->records[$key]['disk']}].");
        }

        $this->records[$key]['cleanup_status'] = $status;
        if ($status === self::STATUS_DELETED_CONFIRMED) {
            $this->records[$key]['deleted'] = true;
        } else {
            $this->records[$key]['deleted'] = false;
        }
    }

    /**
     * Get the active cleanup status for a registered probe key.
     */
    public function getCleanupStatus(string $key): string
    {
        if (! isset($this->records[$key])) {
            throw new RuntimeException("Kunci probe [{$key}] tidak terdaftar dalam sesi manifest ini.");
        }

        return $this->records[$key]['cleanup_status'];
    }

    /**
     * Get the registered disk for a given probe key.
     */
    public function getRegisteredDisk(string $key): string
    {
        if (! isset($this->records[$key])) {
            throw new RuntimeException("Kunci probe [{$key}] tidak terdaftar dalam sesi manifest ini.");
        }

        return $this->records[$key]['disk'];
    }

    /**
     * Retrieve keys that were successfully created and are pending cleanup.
     *
     * @return array<int, string>
     */
    public function getPendingCleanup(): array
    {
        $pending = [];
        foreach ($this->records as $key => $record) {
            if ($record['created'] && ! $record['deleted']) {
                $pending[] = $key;
            }
        }

        return $pending;
    }

    /**
     * Check if there are any residual uncleaned objects in this session.
     */
    public function hasResidual(): bool
    {
        return count($this->getPendingCleanup()) > 0;
    }

    /**
     * Get all registered records for inspection.
     *
     * @return array<string, array{session_id: string, disk: string, key: string, uuid: string, created: bool, deleted: bool, cleanup_status: string, timestamp: int}>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * Clear all in-memory records.
     */
    public function clear(): void
    {
        $this->records = [];
    }
}
