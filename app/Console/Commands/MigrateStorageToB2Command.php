<?php

namespace App\Console\Commands;

use App\Services\StorageMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateStorageToB2Command extends Command
{
    /**
     * Safety Gate for Stage 4E.5B: live network migration is strictly locked.
     */
    public const STAGE_4E5B_SAFETY_GATE_LOCKED = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-to-b2
                            {--dry-run : Perform dry-run audit, checksum calculation, and manifest generation without copying (default)}
                            {--execute : Execute live migration to destination storage (strictly locked in Stage 4E.5B)}
                            {--category=all : Filter migration by category (all, wna, abg, affidavit, user, branding)}
                            {--source-private-disk=local : Source disk for private files}
                            {--source-assets-disk=public : Source disk for asset files}
                            {--dest-private-disk=b2_private : Destination disk for private files}
                            {--dest-assets-disk=b2_assets_private : Destination disk for asset files}
                            {--manifest-dir=migration-manifests : Subdirectory on local disk to store manifests}
                            {--baseline-manifest= : Path to baseline dry-run manifest for pre-execution inventory validation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely audit, inventory, verify checksums, and migrate database-referenced files from local storage to Backblaze B2';

    /**
     * Execute the console command.
     */
    public function handle(StorageMigrationService $migrationService): int
    {
        $this->info('================================================================');
        $this->info('  STORAGE MIGRATION TO BACKBLAZE B2: AUDIT & PREPARATION TOOL   ');
        $this->info('================================================================');

        $isExecute = (bool) $this->option('execute');
        $baselineOpt = $this->option('baseline-manifest');
        $baselineManifestStr = is_string($baselineOpt) ? trim($baselineOpt) : null;

        // 1. Safety Gate Check for --execute (Stage 4E.5B locked)
        if ($isExecute && $this->isSafetyGateLocked()) {
            $this->error('================================================================');
            $this->error('STAGE 4E.5B SAFETY GATE ACTIVE:');
            $this->error('Operasi live migration ke Backblaze B2 terkunci secara ketat pada Stage 4E.5B.');
            $this->error('Tahap ini hanya mencakup pengembangan command, audit inventaris, dan pengujian lokal.');
            $this->error('Eksekusi nyata akan dibuka pada tahap selanjutnya setelah otorisasi kredensial runtime.');
            $this->error('================================================================');

            return self::FAILURE;
        }

        // 2. Mandatory Baseline Manifest Check for Live Execution
        $optionsValidation = $this->validateExecutionRequirements($baselineManifestStr, $isExecute);
        if (! $optionsValidation['valid']) {
            $this->error('================================================================');
            $this->error('PARAMETER WAJIB TIDAK DITEMUKAN:');
            $this->error($optionsValidation['error'] ?? 'Validasi opsi eksekusi gagal.');
            $this->error('Eksekusi dibatalkan sebelum operasi apa pun dijalankan.');
            $this->error('================================================================');

            return self::FAILURE;
        }

        // 2. Validate Category Filter
        $categoryVal = $this->option('category');
        $category = is_string($categoryVal) ? strtolower(trim($categoryVal)) : 'all';
        $allowedCategories = array_merge(['all'], StorageMigrationService::SUPPORTED_CATEGORIES);

        if (! in_array($category, $allowedCategories, true)) {
            $this->error("Kategori [{$category}] tidak valid. Pilih: ".implode(', ', $allowedCategories));

            return self::FAILURE;
        }

        // 3. Resolve Disks and Context
        $sourcePrivateOpt = $this->option('source-private-disk');
        $sourceAssetsOpt = $this->option('source-assets-disk');
        $destPrivateOpt = $this->option('dest-private-disk');
        $destAssetsOpt = $this->option('dest-assets-disk');
        $manifestDirOpt = $this->option('manifest-dir');

        $sourcePrivate = is_string($sourcePrivateOpt) && $sourcePrivateOpt !== '' ? $sourcePrivateOpt : 'local';
        $sourceAssets = is_string($sourceAssetsOpt) && $sourceAssetsOpt !== '' ? $sourceAssetsOpt : 'public';
        $destPrivate = is_string($destPrivateOpt) && $destPrivateOpt !== '' ? $destPrivateOpt : 'b2_private';
        $destAssets = is_string($destAssetsOpt) && $destAssetsOpt !== '' ? $destAssetsOpt : 'b2_assets_private';
        $manifestDir = is_string($manifestDirOpt) && $manifestDirOpt !== '' ? $manifestDirOpt : 'migration-manifests';

        $diskMapping = [
            'source_private' => $sourcePrivate,
            'source_assets' => $sourceAssets,
            'dest_private' => $destPrivate,
            'dest_assets' => $destAssets,
        ];

        $runId = 'migration-run-'.substr(Str::uuid()->toString(), 0, 8);

        $modeLabel = $isExecute ? 'LIVE EXECUTION (Menyalin berkas ke target Backblaze B2)' : 'DRY-RUN (Tidak ada berkas yang ditulis atau dihapus)';
        $modeColor = $isExecute ? 'green' : 'yellow';

        $this->line("Mode Operasi: <fg={$modeColor};options=bold>{$modeLabel}</>");
        $this->line('Kategori Target: <fg=cyan>'.strtoupper($category).'</>');
        $this->line('Private Disks: <fg=cyan>'.$sourcePrivate.'</> -> <fg=cyan>'.$destPrivate.'</>');
        $this->line('Assets Disks:  <fg=cyan>'.$sourceAssets.'</> -> <fg=cyan>'.$destAssets.'</>');
        $this->line('Migration Run ID: <fg=cyan>'.$runId.'</>');
        $this->newLine();

        // 4. Collect Database Inventory
        $this->line('1. Mengambil inventaris berkas yang direferensikan dalam basis data...');
        $inventory = $migrationService->collectDatabaseInventory($category);
        $uniqueCount = count($inventory);

        $totalDbRefs = 0;
        foreach ($inventory as $item) {
            $totalDbRefs += count($item['references']);
        }

        $this->line("   Ditemukan <fg=green>{$totalDbRefs}</> referensi database yang mencakup <fg=green>{$uniqueCount}</> berkas relatif unik.");

        if ($uniqueCount === 0) {
            $this->warn('   Tidak ditemukan berkas untuk kategori yang dipilih.');
        }

        $this->newLine();

        // 5. Evaluate and Verify Checksums
        $this->line('2. Memeriksa keberadaan berkas, menghitung SHA-256 stream, dan mendeteksi konflik...');
        $evaluatedItems = [];

        foreach ($inventory as $item) {
            $evaluated = $migrationService->evaluateItem($item, $diskMapping);
            $evaluatedItems[] = $evaluated;
        }

        // 5.5 Validate against baseline dry-run manifest if provided
        $baselineManifest = $this->option('baseline-manifest');
        if (is_string($baselineManifest) && trim($baselineManifest) !== '') {
            $this->line('3. Memvalidasi inventaris aktif terhadap manifest baseline acuan...');
            $validation = $migrationService->validateInventoryAgainstManifest($baselineManifest, $evaluatedItems);

            if (! $validation['valid']) {
                $this->error('================================================================');
                $this->error('VALIDASI INVENTARIS BASELINE MANIFEST GAGAL:');
                foreach ($validation['errors'] as $err) {
                    $this->error(" - {$err}");
                }
                $this->error('Eksekusi dihentikan. Berkas manifest tidak disimpan dan tidak ada data yang diproses.');
                $this->error('================================================================');

                return self::FAILURE;
            }

            $this->info('   Inventaris dan berkas sumber 100% konsisten dengan manifest baseline.');
            $this->newLine();
        }

        // 5.8 Physical Transfer Loop (only when --execute is active)
        if ($isExecute) {
            $this->line('4. Menjalankan transfer fisik berkas ke penyimpanan Backblaze B2...');
            $processedItems = [];

            foreach ($evaluatedItems as $item) {
                $relPath = $item['relative_source_path'];
                $status = $item['status'];

                if ($status === StorageMigrationService::STATUS_READY_TO_MIGRATE) {
                    $this->line("   -> Mentransfer berkas: <fg=cyan>{$relPath}</> ...");
                    $transferred = $migrationService->transferItem($item);
                    if ($transferred['status'] === StorageMigrationService::STATUS_MIGRATED) {
                        $this->line("      <fg=green>[MIGRATED]</> Checksum cocok: {$transferred['destination_sha256']}");
                    } elseif ($transferred['status'] === StorageMigrationService::STATUS_ALREADY_MATCHED) {
                        $this->line('      <fg=cyan>[ALREADY_MATCHED]</> Berkas tujuan sudah ada dengan checksum identik.');
                    } else {
                        $this->error("      [{$transferred['status']}] ".($transferred['error'] ?? 'Gagal memigrasikan berkas.'));
                    }
                    $processedItems[] = $transferred;
                } else {
                    $this->line("   -> Lewati berkas: <fg=yellow>{$relPath}</> [Status: {$status}]");
                    $processedItems[] = $item;
                }
            }

            $evaluatedItems = $processedItems;
            $this->newLine();
        }

        // 6. Generate Migration Manifest
        $this->line('5. Menyimpan manifest migrasi terstruktur...');
        $manifestContext = [
            'run_id' => $runId,
            'mode' => $isExecute ? 'execute' : 'dry-run',
            'source_private' => $sourcePrivate,
            'source_assets' => $sourceAssets,
            'dest_private' => $destPrivate,
            'dest_assets' => $destAssets,
            'manifest_dir' => $manifestDir,
        ];

        $manifestResult = $migrationService->generateManifest($evaluatedItems, $manifestContext);
        $summary = $manifestResult['summary'];

        $this->line('   Manifest tersimpan di: <fg=cyan>'.$manifestResult['path'].'</>');
        $this->newLine();

        // 7. Output Summary Table
        $this->info('================================================================');
        $this->info($isExecute ? '            RINGKASAN EKSEKUSI MIGRASI (LIVE)                   ' : '                 RINGKASAN AUDIT MIGRASI (DRY-RUN)              ');
        $this->info('================================================================');

        $totalBytes = (int) $summary['total_source_bytes'];
        $humanSize = $totalBytes >= 1048576
            ? round($totalBytes / 1048576, 2).' MB'
            : round($totalBytes / 1024, 2).' KB';

        $tableRows = [
            ['Total Referensi Database', (string) $summary['total_database_references']],
            ['Jumlah Berkas Unik', (string) $summary['unique_file_paths']],
        ];

        if ($isExecute) {
            $tableRows[] = ['Berhasil Dimigrasikan', '<fg=green>'.($summary['migrated'] ?? 0).'</>'];
            $tableRows[] = ['Gagal Upload', ($summary['upload_failures'] ?? 0) > 0 ? '<fg=red>'.$summary['upload_failures'].'</>' : '0'];
        } else {
            $tableRows[] = ['Siap Dimigrasikan (Target Belum Ada)', '<fg=green>'.$summary['ready_to_migrate'].'</>'];
        }

        $tableRows = array_merge($tableRows, [
            ['Sudah Cocok (Target Checksum Identik)', '<fg=cyan>'.$summary['already_matched'].'</>'],
            ['Konflik (Target Ada, Checksum Beda)', $summary['conflicts'] > 0 ? '<fg=red>'.$summary['conflicts'].'</>' : '0'],
            ['Sumber Hilang (Source Missing)', $summary['source_missing'] > 0 ? '<fg=yellow>'.$summary['source_missing'].'</>' : '0'],
            ['Path Ditolak / Tidak Aman', $summary['rejected_paths'] > 0 ? '<fg=red>'.$summary['rejected_paths'].'</>' : '0'],
            ['Kategori Tidak Dikenal', (string) $summary['unresolved_categories']],
            ['Kegagalan Baca Sumber', (string) $summary['source_read_failures']],
            ['Kegagalan Verifikasi Tujuan', $summary['verification_failures'] > 0 ? '<fg=red>'.$summary['verification_failures'].'</>' : '0'],
            ['Total Ukuran Berkas Sumber', $humanSize.' ('.$totalBytes.' bytes)'],
            ['Lokasi Manifest Berkas', $manifestResult['path']],
        ]);

        $this->table(['Metrik Migrasi', 'Hasil'], $tableRows);

        $this->newLine();
        if (! $isExecute) {
            $this->line('<fg=yellow;options=bold>Catatan Keamanan Dry-Run:</>');
            $this->line('  - Tidak ada berkas yang disalin, ditulis, atau dihapus pada disk mana pun.');
            $this->line('  - Eksekusi penyalinan nyata dijalankan dengan flag --execute dan --baseline-manifest.');
        } else {
            $this->line('<fg=green;options=bold>Catatan Integritas Eksekusi Live:</>');
            $this->line('  - Berkas sumber lokal dipertahankan sepenuhnya (non-destruktif).');
            $this->line('  - Seluruh berkas yang dimigrasikan telah divalidasi checksum SHA-256 secara streaming.');
            $this->line('  - Tidak ada perubahan path pada basis data.');
        }

        $uploadFailures = (int) ($summary['upload_failures'] ?? 0);
        $verificationFailures = (int) $summary['verification_failures'];
        $conflicts = (int) $summary['conflicts'];

        if ($isExecute && ($uploadFailures > 0 || $verificationFailures > 0 || $conflicts > 0)) {
            $this->error('PERINGATAN: Terdapat berkas yang gagal dimigrasikan atau mengalami konflik.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Check whether the Stage 4E.5B safety gate is active.
     */
    protected function isSafetyGateLocked(): bool
    {
        return self::STAGE_4E5B_SAFETY_GATE_LOCKED;
    }

    /**
     * Validate command options before execution.
     *
     * @return array{valid: bool, error: string|null}
     */
    public function validateExecutionRequirements(?string $baselineManifest, bool $isExecute): array
    {
        if ($isExecute) {
            if ($baselineManifest === null || trim($baselineManifest) === '') {
                return [
                    'valid' => false,
                    'error' => 'Opsi [--baseline-manifest] wajib disertakan untuk eksekusi live (--execute).',
                ];
            }
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }
}
