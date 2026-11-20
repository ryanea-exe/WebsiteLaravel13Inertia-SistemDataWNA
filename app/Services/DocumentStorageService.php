<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorageService
{
    /**
     * Supported storage backends.
     */
    public const BACKEND_LOCAL = 'local';

    public const BACKEND_BACKBLAZE = 'backblaze';

    public const SUPPORTED_BACKENDS = [
        self::BACKEND_LOCAL,
        self::BACKEND_BACKBLAZE,
    ];

    /**
     * Allowed photo mime types and extensions.
     */
    public const ALLOWED_PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public const ALLOWED_PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public const MAX_PHOTO_SIZE_KB = 2048; // 2MB

    /**
     * Allowed sensitive document mime types and extensions.
     */
    public const ALLOWED_DOCUMENT_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];

    public const ALLOWED_DOCUMENT_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    public const MAX_DOCUMENT_SIZE_KB = 5120; // 5MB

    /**
     * Allowed storage directories managed by this service.
     *
     * @var array<int, string>
     */
    public const ALLOWED_STORAGE_DIRECTORIES = [
        'wna/photos',
        'abg/photos',
        'affidavit/photos',
        'users/photos',
        'settings/icons',
        'wna/documents',
        'abg/documents',
        'affidavit/documents',
        'photos',
        'documents',
    ];

    /**
     * Resolve the active storage backend from configuration.
     *
     * @throws InvalidArgumentException
     */
    public function getBackend(): string
    {
        $configured = config('filesystems.backend');
        $backend = is_string($configured) && trim($configured) !== ''
            ? trim($configured)
            : self::BACKEND_LOCAL;

        if (! in_array($backend, self::SUPPORTED_BACKENDS, true)) {
            throw new InvalidArgumentException(
                "Invalid storage backend [{$backend}]. Supported backends: 'local', 'backblaze'."
            );
        }

        return $backend;
    }

    /**
     * Resolve the disk name used for private sensitive files.
     *
     * @throws RuntimeException
     */
    public function getPrivateDisk(): string
    {
        $backend = $this->getBackend();

        if ($backend === self::BACKEND_BACKBLAZE) {
            $this->validateB2Configuration('b2_private');

            return 'b2_private';
        }

        return 'local';
    }

    /**
     * Resolve the disk name used for application assets.
     *
     * @throws RuntimeException
     */
    public function getAssetsDisk(): string
    {
        $backend = $this->getBackend();

        if ($backend === self::BACKEND_BACKBLAZE) {
            $this->validateB2Configuration('b2_assets_private');

            return 'b2_assets_private';
        }

        return 'public';
    }

    /**
     * Alias for getAssetsDisk to preserve backwards compatibility.
     */
    public function getPublicDisk(): string
    {
        return $this->getAssetsDisk();
    }

    /**
     * Validate that Backblaze B2 disk configuration has essential parameters.
     *
     * @throws RuntimeException
     */
    protected function validateB2Configuration(string $disk): void
    {
        $config = config("filesystems.disks.{$disk}");

        if (! is_array($config)) {
            throw new RuntimeException("Backblaze B2 disk [{$disk}] is not defined in filesystems configuration.");
        }

        $required = ['key', 'secret', 'bucket', 'region', 'endpoint'];
        $missing = [];

        foreach ($required as $field) {
            $value = $config[$field] ?? null;
            if ($value === null || trim((string) $value) === '') {
                $missing[] = $field;
            }
        }

        if (! empty($missing)) {
            throw new RuntimeException(
                "Backblaze B2 configuration for disk [{$disk}] is incomplete. Missing: ".implode(', ', $missing).'.'
            );
        }
    }

    /**
     * Store an uploaded photo safely on the application assets disk.
     *
     * @return string Relative path inside the assets disk
     */
    public function storePublicPhoto(UploadedFile $file, string $directory = 'photos'): string
    {
        $this->validatePhoto($file);

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_PHOTO_EXTENSIONS, true)) {
            $extension = $file->guessExtension() ?? 'jpg';
        }

        $filename = Str::random(40).'.'.$extension;
        $disk = $this->getAssetsDisk();

        try {
            $path = $file->storeAs($directory, $filename, $disk);
        } catch (\Throwable $e) {
            $sanitizedMessage = $this->sanitizeErrorMessage($e->getMessage());
            $diagnostics = $this->extractDiagnosticDetails($e);

            Log::error('DocumentStorageService: Failed to store public photo due to filesystem exception.', array_merge([
                'disk' => $disk,
                'directory' => $directory,
                'error_message' => $sanitizedMessage,
            ], $diagnostics));

            // Throw isolated sanitized exception without raw $e as $previous to prevent credential leakage in exception chains
            throw new RuntimeException(
                'Failed to store public photo: '.$sanitizedMessage,
                is_int($diagnostics['error_code']) ? $diagnostics['error_code'] : 0
            );
        }

        if ($path === false) {
            Log::error('DocumentStorageService: Failed to store public photo (adapter returned false).', [
                'disk' => $disk,
                'directory' => $directory,
            ]);

            throw new RuntimeException('Failed to store public photo.');
        }

        return $path;
    }

    /**
     * Store a sensitive photo safely on the private storage disk.
     *
     * @return string Relative path inside the private disk
     */
    public function storePrivatePhoto(UploadedFile $file, string $directory = 'wna/photos'): string
    {
        $this->validatePhoto($file);

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_PHOTO_EXTENSIONS, true)) {
            $extension = $file->guessExtension() ?? 'jpg';
        }

        $filename = Str::random(40).'.'.$extension;
        $disk = $this->getPrivateDisk();

        try {
            $path = $file->storeAs($directory, $filename, $disk);
        } catch (\Throwable $e) {
            $sanitizedMessage = $this->sanitizeErrorMessage($e->getMessage());
            $diagnostics = $this->extractDiagnosticDetails($e);

            Log::error('DocumentStorageService: Failed to store private photo due to filesystem exception.', array_merge([
                'disk' => $disk,
                'directory' => $directory,
                'error_message' => $sanitizedMessage,
            ], $diagnostics));

            // Throw isolated sanitized exception without raw $e as $previous to prevent credential leakage in exception chains
            throw new RuntimeException(
                'Failed to store private photo: '.$sanitizedMessage,
                is_int($diagnostics['error_code']) ? $diagnostics['error_code'] : 0
            );
        }

        if ($path === false) {
            Log::error('DocumentStorageService: Failed to store private photo (adapter returned false).', [
                'disk' => $disk,
                'directory' => $directory,
            ]);

            throw new RuntimeException('Failed to store private photo.');
        }

        return $path;
    }

    /**
     * Store a sensitive document safely on the private storage disk.
     *
     * @return string Relative path inside the private disk
     */
    public function storePrivateDocument(UploadedFile $file, string $directory = 'documents'): string
    {
        $this->validateDocument($file);

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_DOCUMENT_EXTENSIONS, true)) {
            $extension = $file->guessExtension() ?? 'pdf';
        }

        $filename = Str::random(40).'.'.$extension;
        $disk = $this->getPrivateDisk();

        try {
            $path = $file->storeAs($directory, $filename, $disk);
        } catch (\Throwable $e) {
            $sanitizedMessage = $this->sanitizeErrorMessage($e->getMessage());
            $diagnostics = $this->extractDiagnosticDetails($e);

            Log::error('DocumentStorageService: Failed to store private document due to filesystem exception.', array_merge([
                'disk' => $disk,
                'directory' => $directory,
                'error_message' => $sanitizedMessage,
            ], $diagnostics));

            // Throw isolated sanitized exception without raw $e as $previous to prevent credential leakage in exception chains
            throw new RuntimeException(
                'Failed to store private document: '.$sanitizedMessage,
                is_int($diagnostics['error_code']) ? $diagnostics['error_code'] : 0
            );
        }

        if ($path === false) {
            Log::error('DocumentStorageService: Failed to store private document (adapter returned false).', [
                'disk' => $disk,
                'directory' => $directory,
            ]);

            throw new RuntimeException('Failed to store private document.');
        }

        return $path;
    }

    /**
     * Extract sanitized diagnostic provider error details from an exception.
     *
     * @return array{
     *     exception_class: string,
     *     error_code: int|string,
     *     provider_status: int|null,
     *     provider_error_code: string|null,
     *     root_cause_class: string|null
     * }
     */
    public function extractDiagnosticDetails(\Throwable $e): array
    {
        $exceptionClass = get_class($e);
        $errorCode = $e->getCode();
        $providerStatus = null;
        $providerErrorCode = null;
        $rootCauseClass = null;

        // Check if current exception has AWS SDK / HTTP response methods
        if (method_exists($e, 'getStatusCode')) {
            $providerStatus = $e->getStatusCode();
        }
        if (method_exists($e, 'getAwsErrorCode')) {
            $providerErrorCode = $e->getAwsErrorCode();
        }

        // Check chained root cause without storing the exception object itself
        $prev = $e->getPrevious();
        if ($prev instanceof \Throwable) {
            $rootCauseClass = get_class($prev);

            if ($providerStatus === null && method_exists($prev, 'getStatusCode')) {
                $providerStatus = $prev->getStatusCode();
            }
            if ($providerErrorCode === null && method_exists($prev, 'getAwsErrorCode')) {
                $providerErrorCode = $prev->getAwsErrorCode();
            }
            if ($errorCode === 0 && $prev->getCode() !== 0) {
                $errorCode = $prev->getCode();
            }
        }

        return [
            'exception_class' => $exceptionClass,
            'error_code' => $errorCode,
            'provider_status' => $providerStatus,
            'provider_error_code' => $providerErrorCode,
            'root_cause_class' => $rootCauseClass,
        ];
    }

    /**
     * Sanitize sensitive credentials, tokens, and absolute paths from error messages.
     */
    public function sanitizeErrorMessage(string $message): string
    {
        // 1. Redact key/secret/token/signature in query strings or parameter formats
        $sanitized = preg_replace(
            '/(AWSAccessKeyId|Signature|key|secret|token|X-Amz-Signature|X-Amz-Credential|X-Amz-Security-Token)=([^&\s"\'<]+)/i',
            '$1=[REDACTED]',
            $message
        ) ?? $message;

        // 2. Redact authorization headers
        $sanitized = preg_replace(
            '/(Authorization:\s*(?:AWS|Bearer)[^\s\r\n]+)/i',
            'Authorization: [REDACTED]',
            $sanitized
        ) ?? $sanitized;

        // 3. Redact configured S3/B2 keys and secrets if present in config
        $sensitiveValues = array_filter([
            config('filesystems.disks.b2_private.key'),
            config('filesystems.disks.b2_private.secret'),
            config('filesystems.disks.b2_assets_private.key'),
            config('filesystems.disks.b2_assets_private.secret'),
            config('filesystems.disks.s3.key'),
            config('filesystems.disks.s3.secret'),
        ], fn ($val) => is_string($val) && strlen(trim($val)) >= 4);

        if (! empty($sensitiveValues)) {
            $sanitized = str_replace($sensitiveValues, '[REDACTED]', $sanitized);
        }

        // 4. Redact system base paths
        $basePath = base_path();
        if ($basePath !== '') {
            $sanitized = str_replace($basePath, '[APP_ROOT]', $sanitized);
        }

        return $sanitized;
    }

    /**
     * Store a temporary probe file safely on the designated active storage disk.
     * Strictly restricted to the probe directory prefix (__stage_4e_probe__) within managed paths.
     *
     * @throws InvalidArgumentException
     */
    public function storeProbeFile(string $disk, string $path, string $contents): bool
    {
        // 1. Disk must strictly match active private or assets disk
        $allowedDisks = [$this->getPrivateDisk(), $this->getAssetsDisk()];
        if (! in_array($disk, $allowedDisks, true)) {
            throw new InvalidArgumentException("Disk [{$disk}] tidak diizinkan untuk penyimpanan berkas probe.");
        }

        // 2. Reject traversal, null bytes, backslashes, wildcards, and control characters
        if (
            trim($path) === '' ||
            str_contains($path, '..') ||
            str_contains($path, "\0") ||
            str_contains($path, '\\') ||
            preg_match('/[\x00-\x1F\x7F]/', $path) ||
            preg_match('/[*?\[\]]/', $path)
        ) {
            throw new InvalidArgumentException("Path berkas probe [{$path}] memuat karakter ilegal atau traversal.");
        }

        // 3. Must strictly be within the probe isolation prefix
        if (! str_contains($path, '/__stage_4e_probe__/')) {
            throw new InvalidArgumentException("Path berkas probe [{$path}] wajib memuat prefix isolasi '__stage_4e_probe__'.");
        }

        // 4. Validate disk-specific module path
        if ($disk === $this->getPrivateDisk() && ! str_starts_with($path, 'wna/photos/__stage_4e_probe__/')) {
            throw new InvalidArgumentException("Path probe untuk private disk wajib diawali dengan 'wna/photos/__stage_4e_probe__/'.");
        }

        if ($disk === $this->getAssetsDisk() && ! str_starts_with($path, 'settings/icons/__stage_4e_probe__/')) {
            throw new InvalidArgumentException("Path probe untuk assets disk wajib diawali dengan 'settings/icons/__stage_4e_probe__/'.");
        }

        // 5. Must strictly pass general allowlist check
        if (! $this->isPathAllowed($path)) {
            throw new InvalidArgumentException("Path berkas probe [{$path}] tidak diizinkan oleh allowlist direktori terkelola.");
        }

        // 6. Write via active configured Flysystem storage disk
        return (bool) Storage::disk($disk)->put($path, $contents);
    }

    /**
     * Check if a given storage relative path is strictly within the allowed managed directories.
     */
    public function isPathAllowed(string $path): bool
    {
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return false;
        }

        foreach (self::ALLOWED_STORAGE_DIRECTORIES as $allowedDir) {
            if ($path === $allowedDir || str_starts_with($path, $allowedDir.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete a file safely from private or public/assets storage across active and local disks.
     */
    public function deleteFile(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        if (! $this->isPathAllowed($path)) {
            Log::warning('DocumentStorageService: blocked deletion outside managed directories or with traversal', [
                'path' => $path,
            ]);

            return false;
        }

        $deleted = false;
        $disksToCheck = array_unique([
            $this->getPrivateDisk(),
            $this->getAssetsDisk(),
            'local',
            'public',
        ]);

        foreach ($disksToCheck as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    $wasDeleted = Storage::disk($disk)->delete($path);
                    if (! $wasDeleted) {
                        Log::error("DocumentStorageService: failed to delete file from disk [{$disk}]", ['path' => $path]);
                    }
                    $deleted = $deleted || $wasDeleted;
                }
            } catch (\Throwable $e) {
                Log::error("DocumentStorageService: error checking/deleting file from disk [{$disk}]", [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $deleted;
    }

    /**
     * Delete a file from application assets storage.
     */
    public function deletePublic(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        if (! $this->isPathAllowed($path)) {
            Log::warning('DocumentStorageService: blocked deletion outside managed directories or with traversal', [
                'path' => $path,
            ]);

            return false;
        }

        $disk = $this->getAssetsDisk();
        $deleted = Storage::disk($disk)->delete($path);

        if (! $deleted && Storage::disk($disk)->exists($path)) {
            Log::error("DocumentStorageService: failed to delete file from assets disk [{$disk}]", ['path' => $path]);
        }

        return $deleted;
    }

    /**
     * Delete a file from private storage.
     */
    public function deletePrivate(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        if (! $this->isPathAllowed($path)) {
            Log::warning('DocumentStorageService: blocked deletion outside managed directories or with traversal', [
                'path' => $path,
            ]);

            return false;
        }

        $disk = $this->getPrivateDisk();
        $deleted = Storage::disk($disk)->delete($path);

        if (! $deleted && Storage::disk($disk)->exists($path)) {
            Log::error("DocumentStorageService: failed to delete file from private disk [{$disk}]", ['path' => $path]);
        }

        return $deleted;
    }

    /**
     * Check if a file exists on the designated or resolved disk.
     */
    public function exists(string $path, ?string $disk = null): bool
    {
        if (! $this->isPathAllowed($path)) {
            return false;
        }

        if ($disk !== null) {
            return Storage::disk($disk)->exists($path);
        }

        // Check active private disk first, then assets disk
        if (Storage::disk($this->getPrivateDisk())->exists($path)) {
            return true;
        }

        return Storage::disk($this->getAssetsDisk())->exists($path);
    }

    /**
     * Read a file as a stream resource from the designated or resolved disk.
     *
     * @return resource|null
     */
    public function readStream(string $path, ?string $disk = null)
    {
        if (! $this->isPathAllowed($path)) {
            return null;
        }

        $resolvedDisk = $disk ?? $this->resolveDiskForPath($path);

        if ($resolvedDisk === null) {
            return null;
        }

        return Storage::disk($resolvedDisk)->readStream($path);
    }

    /**
     * Return a StreamedResponse to stream a file safely from any storage disk.
     * When $mimeType is null, the MIME type is automatically detected and validated.
     *
     * @param  array<string, string>  $headers
     */
    public function streamFile(
        string $disk,
        string $path,
        ?string $mimeType = null,
        array $headers = [],
        string $disposition = 'inline'
    ): StreamedResponse {
        if (! $this->isPathAllowed($path) || ! Storage::disk($disk)->exists($path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        if ($mimeType === null || trim($mimeType) === '') {
            try {
                $detectedMime = Storage::disk($disk)->mimeType($path);
                $mimeType = is_string($detectedMime) ? $detectedMime : null;
            } catch (\Throwable) {
                $mimeType = null;
            }
        }

        $isPhotoPath = str_contains($path, '/photos') || str_starts_with($path, 'photos/') || str_starts_with($path, 'settings/icons');
        $allowedMimes = $isPhotoPath
            ? self::ALLOWED_PHOTO_MIMES
            : array_merge(self::ALLOWED_PHOTO_MIMES, self::ALLOWED_DOCUMENT_MIMES);

        if ($mimeType === null || ! in_array($mimeType, $allowedMimes, true)) {
            abort(415, 'Format berkas tidak didukung.');
        }

        $stream = Storage::disk($disk)->readStream($path);

        if (! is_resource($stream)) {
            abort(404, 'Berkas tidak dapat dibaca.');
        }

        $mandatoryHeaders = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition,
            'X-Content-Type-Options' => 'nosniff',
        ];

        // Ensure caller headers cannot override mandatory security headers
        $responseHeaders = array_merge($headers, $mandatoryHeaders);

        return response()->stream(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, $responseHeaders);
    }

    /**
     * Retrieve the MIME type of a stored file.
     */
    public function mimeType(string $path, ?string $disk = null): ?string
    {
        if (! $this->isPathAllowed($path)) {
            return null;
        }

        $resolvedDisk = $disk ?? $this->resolveDiskForPath($path);

        if ($resolvedDisk === null) {
            return null;
        }

        $mime = Storage::disk($resolvedDisk)->mimeType($path);

        return is_string($mime) ? $mime : null;
    }

    /**
     * Retrieve the size of a stored file in bytes.
     */
    public function size(string $path, ?string $disk = null): int
    {
        if (! $this->isPathAllowed($path)) {
            return 0;
        }

        $resolvedDisk = $disk ?? $this->resolveDiskForPath($path);

        if ($resolvedDisk === null) {
            return 0;
        }

        return (int) Storage::disk($resolvedDisk)->size($path);
    }

    /**
     * Find which disk holds an existing file.
     */
    public function resolveDiskForPath(string $path): ?string
    {
        $disks = array_unique([
            $this->getPrivateDisk(),
            $this->getAssetsDisk(),
        ]);

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * Validate an uploaded photo.
     */
    public function validatePhoto(UploadedFile $file): void
    {
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $sizeKb = (int) ceil($file->getSize() / 1024);

        if ($sizeKb > self::MAX_PHOTO_SIZE_KB) {
            throw new InvalidArgumentException('Ukuran foto melebihi batas maksimum ('.self::MAX_PHOTO_SIZE_KB.' KB).');
        }

        if ($mime === null || ! in_array($mime, self::ALLOWED_PHOTO_MIMES, true)) {
            throw new InvalidArgumentException('Tipe konten foto tidak valid. Format yang diizinkan: JPG, PNG, WEBP.');
        }

        if ($extension !== '' && ! in_array($extension, self::ALLOWED_PHOTO_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Ekstensi foto tidak valid. Format yang diizinkan: JPG, PNG, WEBP.');
        }
    }

    /**
     * Validate an uploaded sensitive document.
     */
    public function validateDocument(UploadedFile $file): void
    {
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $sizeKb = (int) ceil($file->getSize() / 1024);

        if ($sizeKb > self::MAX_DOCUMENT_SIZE_KB) {
            throw new InvalidArgumentException('Ukuran dokumen melebihi batas maksimum ('.self::MAX_DOCUMENT_SIZE_KB.' KB).');
        }

        if ($mime === null || ! in_array($mime, self::ALLOWED_DOCUMENT_MIMES, true)) {
            throw new InvalidArgumentException('Tipe konten dokumen tidak valid. Format yang diizinkan: PDF, JPG, PNG.');
        }

        if ($extension !== '' && ! in_array($extension, self::ALLOWED_DOCUMENT_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Ekstensi dokumen tidak valid. Format yang diizinkan: PDF, JPG, PNG.');
        }
    }

    /**
     * Validation rule definition for photo uploads.
     *
     * @return array<int, string>
     */
    public static function photoRules(bool $required = false): array
    {
        $rules = [
            'file',
            'mimes:'.implode(',', self::ALLOWED_PHOTO_EXTENSIONS),
            'max:'.self::MAX_PHOTO_SIZE_KB,
        ];

        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * Validation rule definition for document uploads.
     *
     * @return array<int, string>
     */
    public static function documentRules(bool $required = false): array
    {
        $rules = [
            'file',
            'mimes:'.implode(',', self::ALLOWED_DOCUMENT_EXTENSIONS),
            'max:'.self::MAX_DOCUMENT_SIZE_KB,
        ];

        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }
}
