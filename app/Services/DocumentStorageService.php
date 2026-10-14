<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DocumentStorageService
{
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
        'wna/documents',
        'abg/documents',
        'affidavit/documents',
        'photos',
        'documents',
    ];

    /**
     * Store an uploaded photo safely on the public storage disk.
     *
     * @return string Relative path inside the public disk
     */
    public function storePublicPhoto(UploadedFile $file, string $directory = 'photos'): string
    {
        $this->validatePhoto($file);

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_PHOTO_EXTENSIONS, true)) {
            $extension = $file->guessExtension() ?? 'jpg';
        }

        $filename = Str::random(40).'.'.$extension;

        $path = $file->storeAs($directory, $filename, 'public');

        if ($path === false) {
            throw new \RuntimeException('Failed to store public photo.');
        }

        return $path;
    }

    /**
     * Store a sensitive photo safely on the private local storage disk.
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

        $path = $file->storeAs($directory, $filename, 'local');

        if ($path === false) {
            throw new \RuntimeException('Failed to store private photo.');
        }

        return $path;
    }

    /**
     * Store a sensitive document safely on the private local storage disk.
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

        $path = $file->storeAs($directory, $filename, 'local');

        if ($path === false) {
            throw new \RuntimeException('Failed to store private document.');
        }

        return $path;
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
     * Delete a file safely from private or public storage.
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
        if (Storage::disk('local')->exists($path)) {
            $deleted = Storage::disk('local')->delete($path);
            if (! $deleted) {
                Log::error('DocumentStorageService: failed to delete file from local storage', ['path' => $path]);
            }
        }
        if (Storage::disk('public')->exists($path)) {
            $publicDeleted = Storage::disk('public')->delete($path);
            if (! $publicDeleted) {
                Log::error('DocumentStorageService: failed to delete file from public storage', ['path' => $path]);
            }
            $deleted = $deleted || $publicDeleted;
        }

        return $deleted;
    }

    /**
     * Delete a file from public storage.
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

        $deleted = Storage::disk('public')->delete($path);
        if (! $deleted && Storage::disk('public')->exists($path)) {
            Log::error('DocumentStorageService: failed to delete file from public storage', ['path' => $path]);
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

        $deleted = Storage::disk('local')->delete($path);
        if (! $deleted && Storage::disk('local')->exists($path)) {
            Log::error('DocumentStorageService: failed to delete file from local storage', ['path' => $path]);
        }

        return $deleted;
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
