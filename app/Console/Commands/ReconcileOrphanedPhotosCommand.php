<?php

namespace App\Console\Commands;

use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\User;
use App\Models\Wna;
use App\Services\DocumentStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ReconcileOrphanedPhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:reconcile-photos
                            {--dry-run : Only report orphaned files without deleting them (default)}
                            {--force : Actually delete confirmed orphaned files}
                            {--older-than=60 : Minimum age of files in minutes to avoid race conditions with in-progress uploads}
                            {--module=all : Specific module to check (wna, abg, affidavit, user, all)}
                            {--disk= : Specific storage disk to check (must match active backend private disk)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely audit and reconcile orphaned photo files in private storage with database records';

    /**
     * Managed module directories.
     *
     * @var array<string, string>
     */
    protected array $moduleDirectories = [
        'wna' => 'wna/photos',
        'abg' => 'abg/photos',
        'affidavit' => 'affidavit/photos',
        'user' => 'users/photos',
    ];

    /**
     * Execute the console command.
     */
    public function handle(DocumentStorageService $storageService): int
    {
        $force = (bool) $this->option('force');
        $isDryRun = ! $force || (bool) $this->option('dry-run');
        $olderThanOpt = $this->option('older-than');
        $olderThanMinutes = max(1, is_numeric($olderThanOpt) ? (int) $olderThanOpt : 60);

        $moduleVal = $this->option('module');
        $moduleOption = is_string($moduleVal) ? strtolower(trim($moduleVal)) : 'all';
        if ($moduleOption === 'users') {
            $moduleOption = 'user';
        }

        if (! in_array($moduleOption, ['all', 'wna', 'abg', 'affidavit', 'user'], true)) {
            $this->error('Modul tidak valid. Pilih: all, wna, abg, affidavit, atau user.');

            return self::FAILURE;
        }

        try {
            $activeBackend = $storageService->getBackend();
            $defaultPrivateDisk = $storageService->getPrivateDisk();
        } catch (RuntimeException $e) {
            $this->error('Gagal memvalidasi konfigurasi storage: '.$e->getMessage());

            return self::FAILURE;
        }

        $diskOpt = $this->option('disk');
        if (is_string($diskOpt) && trim($diskOpt) !== '') {
            $targetDisk = strtolower(trim($diskOpt));

            // Explicitly reject public / asset disks
            if (in_array($targetDisk, ['public', 'b2_assets_private'], true)) {
                $this->error("Disk [{$targetDisk}] adalah disk aset dan tidak diizinkan untuk rekonsiliasi foto privat.");

                return self::FAILURE;
            }

            // Strict validation against active backend
            if ($activeBackend === DocumentStorageService::BACKEND_LOCAL) {
                if ($targetDisk !== 'local') {
                    $this->error("Disk [{$targetDisk}] tidak valid untuk backend aktif [local]. Hanya disk [local] yang diizinkan.");

                    return self::FAILURE;
                }
            } elseif ($activeBackend === DocumentStorageService::BACKEND_BACKBLAZE) {
                if ($targetDisk !== 'b2_private') {
                    $this->error("Disk [{$targetDisk}] tidak valid untuk backend aktif [backblaze]. Hanya disk [b2_private] yang diizinkan.");

                    return self::FAILURE;
                }
            } else {
                $this->error("Backend [{$activeBackend}] tidak didukung.");

                return self::FAILURE;
            }
        } else {
            $targetDisk = $defaultPrivateDisk;
        }

        $this->info('=== Audit & Rekonsiliasi File Foto Privat ===');
        $this->line('Backend Storage: <fg=cyan>'.$activeBackend.'</>');
        $this->line('Disk Target: <fg=cyan>'.$targetDisk.'</>');
        $this->line('Mode: '.($isDryRun ? '<fg=yellow>DRY-RUN (Tidak ada file yang dihapus)</>' : '<fg=red>FORCE EXECUTION (File yatim akan dihapus)</>'));
        $this->line('Grace Period: '.$olderThanMinutes.' menit (file lebih baru dari ini tidak akan disentuh)');
        $this->line('Modul Target: '.strtoupper($moduleOption));
        $this->newLine();

        // Collect active photo references from database with memory optimization
        /** @var array<int, string> $activePhotos */
        $activePhotos = [];

        if ($moduleOption === 'all' || $moduleOption === 'wna') {
            $wnaPhotos = Wna::query()->toBase()->whereNotNull('foto')->pluck('foto')->filter()->all();
            $activePhotos = array_merge($activePhotos, $wnaPhotos);
        }

        if ($moduleOption === 'all' || $moduleOption === 'abg') {
            $abgPhotos = Abg::query()->toBase()->whereNotNull('foto')->pluck('foto')->filter()->all();
            $activePhotos = array_merge($activePhotos, $abgPhotos);
        }

        if ($moduleOption === 'all' || $moduleOption === 'affidavit') {
            $affidavitPhotos = Affidavit::query()->toBase()->whereNotNull('foto')->pluck('foto')->filter()->all();
            $activePhotos = array_merge($activePhotos, $affidavitPhotos);
        }

        if ($moduleOption === 'all' || $moduleOption === 'user') {
            $userPhotos = User::query()->toBase()->whereNotNull('foto')->pluck('foto')->filter()->all();
            $activePhotos = array_merge($activePhotos, $userPhotos);
        }

        // Fast lookup set
        $activePhotosSet = array_fill_keys($activePhotos, true);

        $targetDirs = $moduleOption === 'all'
            ? $this->moduleDirectories
            : [$moduleOption => $this->moduleDirectories[$moduleOption]];

        $disk = $targetDisk;
        $now = time();
        $thresholdTime = $now - ($olderThanMinutes * 60);

        $totalScanned = 0;
        $totalActivePreserved = 0;
        $totalRecentProtected = 0;
        $totalOrphaned = 0;
        $totalDeleted = 0;
        $totalErrors = 0;

        $results = [];

        foreach ($targetDirs as $module => $directory) {
            if (! Storage::disk($disk)->exists($directory)) {
                continue;
            }

            $files = Storage::disk($disk)->files($directory);

            foreach ($files as $filePath) {
                $totalScanned++;

                // Jail boundary & traversal protection check
                if (! $storageService->isPathAllowed($filePath) || ! str_starts_with($filePath, $directory.'/')) {
                    Log::warning('ReconcilePhotos: file found outside allowed module path or contains traversal', [
                        'disk' => $disk,
                        'path' => $filePath,
                    ]);

                    continue;
                }

                // Check active database reference
                if (isset($activePhotosSet[$filePath])) {
                    $totalActivePreserved++;

                    continue;
                }

                // Check file timestamp to protect in-flight transactions or uploads
                $lastModified = Storage::disk($disk)->lastModified($filePath);
                $ageMinutes = (int) floor(($now - $lastModified) / 60);

                if ($lastModified > $thresholdTime) {
                    $totalRecentProtected++;
                    Log::info('ReconcilePhotos: skipped recent unreferenced file to protect in-flight uploads', [
                        'disk' => $disk,
                        'path' => $filePath,
                        'age_minutes' => $ageMinutes,
                    ]);

                    continue;
                }

                // Confirmed orphaned candidate
                $totalOrphaned++;
                $fileSize = Storage::disk($disk)->size($filePath);
                $fileSizeKb = round($fileSize / 1024, 2);

                if ($isDryRun) {
                    Log::info('ReconcilePhotos: orphaned photo candidate detected (dry-run)', [
                        'disk' => $disk,
                        'module' => $module,
                        'path' => $filePath,
                        'size_bytes' => $fileSize,
                        'age_minutes' => $ageMinutes,
                    ]);

                    $results[] = [
                        'disk' => $disk,
                        'module' => strtoupper($module),
                        'path' => $filePath,
                        'size' => $fileSizeKb.' KB',
                        'age' => $ageMinutes.' min',
                        'status' => 'Kandidat (Dry-run)',
                    ];
                } else {
                    // Double check against DB right before destructive action
                    if ($this->isReferencedInDatabase($filePath, $module)) {
                        Log::warning('ReconcilePhotos: race condition avoided, file recently referenced in DB', [
                            'disk' => $disk,
                            'path' => $filePath,
                        ]);
                        $totalActivePreserved++;

                        continue;
                    }

                    $deleted = Storage::disk($disk)->delete($filePath);

                    if ($deleted) {
                        $totalDeleted++;
                        Log::info('ReconcilePhotos: deleted confirmed orphaned photo', [
                            'disk' => $disk,
                            'module' => $module,
                            'path' => $filePath,
                            'size_bytes' => $fileSize,
                        ]);

                        $results[] = [
                            'disk' => $disk,
                            'module' => strtoupper($module),
                            'path' => $filePath,
                            'size' => $fileSizeKb.' KB',
                            'age' => $ageMinutes.' min',
                            'status' => '<fg=green>Dihapus</>',
                        ];
                    } else {
                        $totalErrors++;
                        Log::error('ReconcilePhotos: failed to delete orphaned photo', [
                            'disk' => $disk,
                            'module' => $module,
                            'path' => $filePath,
                        ]);

                        $results[] = [
                            'disk' => $disk,
                            'module' => strtoupper($module),
                            'path' => $filePath,
                            'size' => $fileSizeKb.' KB',
                            'age' => $ageMinutes.' min',
                            'status' => '<fg=red>Gagal Dihapus</>',
                        ];
                    }
                }
            }
        }

        if (count($results) > 0) {
            $this->table(['Disk', 'Modul', 'File Path', 'Ukuran', 'Umur', 'Status'], $results);
        } else {
            $this->info('Tidak ditemukan file foto yatim (orphaned files).');
        }

        $this->newLine();
        $this->line('=== Ringkasan Audit ===');
        $this->line('Disk diperiksa: '.$disk);
        $this->line('Total file diperiksa: '.$totalScanned);
        $this->line('File aktif dipertahankan: '.$totalActivePreserved);
        $this->line('File baru dilindungi (grace period): '.$totalRecentProtected);
        $this->line('File foto yatim terdeteksi: '.$totalOrphaned);

        if (! $isDryRun) {
            $this->line('File foto yatim berhasil dihapus: '.$totalDeleted);
            if ($totalErrors > 0) {
                $this->error('File gagal dihapus: '.$totalErrors);
            }
        } else {
            $this->comment('Peringatan: Berjalan dalam mode DRY-RUN. Tidak ada file yang dihapus.');
            $this->comment('Gunakan opsi --force untuk menghapus file yang terverifikasi yatim.');
        }

        return self::SUCCESS;
    }

    /**
     * Real-time double verification against database records.
     */
    protected function isReferencedInDatabase(string $path, string $module): bool
    {
        return match ($module) {
            'wna' => Wna::where('foto', $path)->exists(),
            'abg' => Abg::where('foto', $path)->exists(),
            'affidavit' => Affidavit::where('foto', $path)->exists(),
            'user' => User::where('foto', $path)->exists(),
            default => Wna::where('foto', $path)->exists()
                || Abg::where('foto', $path)->exists()
                || Affidavit::where('foto', $path)->exists()
                || User::where('foto', $path)->exists(),
        };
    }
}
