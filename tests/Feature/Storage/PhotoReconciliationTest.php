<?php

use App\Models\Abg;
use App\Models\Wna;
use App\Services\DocumentStorageService;
use Illuminate\Support\Facades\Storage;

test('document storage service enforces directory jail and rejects path traversal', function () {
    $service = app(DocumentStorageService::class);

    // Traversal and null bytes
    expect($service->isPathAllowed('../../etc/passwd'))->toBeFalse()
        ->and($service->isPathAllowed('wna/photos/../../secret.txt'))->toBeFalse()
        ->and($service->isPathAllowed("wna/photos/test\0.jpg"))->toBeFalse();

    // Outside paths
    expect($service->isPathAllowed('random/folder/file.jpg'))->toBeFalse()
        ->and($service->isPathAllowed('app/private/other.jpg'))->toBeFalse();

    // Allowed paths
    expect($service->isPathAllowed('wna/photos/abc.jpg'))->toBeTrue()
        ->and($service->isPathAllowed('abg/photos/xyz.png'))->toBeTrue()
        ->and($service->isPathAllowed('affidavit/photos/123.webp'))->toBeTrue()
        ->and($service->isPathAllowed('documents/report.pdf'))->toBeTrue();

    // DeleteFile blocks unallowed paths without touching disk
    expect($service->deleteFile('../../dangerous.jpg'))->toBeFalse();
});

test('reconciliation command runs in dry-run mode by default without deleting files', function () {
    Storage::fake('local');
    Storage::fake('public');

    // 1. Create active WNA record with photo
    $activeWna = Wna::factory()->create([
        'foto' => 'wna/photos/active_wna.jpg',
    ]);
    Storage::disk('local')->put('wna/photos/active_wna.jpg', 'active-content');

    // 2. Put an orphaned photo older than 60 minutes
    $orphanedPath = 'wna/photos/orphaned_old.jpg';
    Storage::disk('local')->put($orphanedPath, 'orphaned-content');
    // Touch file timestamp back 2 hours
    touch(Storage::disk('local')->path($orphanedPath), time() - 7200);

    // 3. Put a recent file to simulate an in-flight upload (e.g. 5 minutes ago)
    $recentPath = 'wna/photos/in_flight_recent.jpg';
    Storage::disk('local')->put($recentPath, 'in-flight-content');
    touch(Storage::disk('local')->path($recentPath), time() - 300);

    // Run command in default dry-run mode
    $this->artisan('storage:reconcile-photos', ['--older-than' => 60])
        ->expectsOutputToContain('DRY-RUN')
        ->expectsOutputToContain('Kandidat (Dry-run)')
        ->assertSuccessful();

    // Verify NO files were deleted in dry-run mode
    expect(Storage::disk('local')->exists('wna/photos/active_wna.jpg'))->toBeTrue()
        ->and(Storage::disk('local')->exists($orphanedPath))->toBeTrue()
        ->and(Storage::disk('local')->exists($recentPath))->toBeTrue();
});

test('reconciliation command with force deletes confirmed orphaned files while preserving active and in-flight files', function () {
    Storage::fake('local');
    Storage::fake('public');

    // 1. Active WNA and ABG records
    Wna::factory()->create(['foto' => 'wna/photos/active_wna.jpg']);
    Abg::factory()->create(['foto' => 'abg/photos/active_abg.jpg']);
    Storage::disk('local')->put('wna/photos/active_wna.jpg', 'wna-content');
    Storage::disk('local')->put('abg/photos/active_abg.jpg', 'abg-content');

    // 2. Orphaned file older than 60 minutes in wna/photos
    $orphanedPath = 'wna/photos/orphaned_to_delete.jpg';
    Storage::disk('local')->put($orphanedPath, 'orphan-content');
    touch(Storage::disk('local')->path($orphanedPath), time() - 7200);

    // 3. Recent file (in-flight transaction protection)
    $recentPath = 'wna/photos/concurrent_upload.jpg';
    Storage::disk('local')->put($recentPath, 'recent-content');
    touch(Storage::disk('local')->path($recentPath), time() - 120);

    // Run with --force
    $this->artisan('storage:reconcile-photos', ['--force' => true, '--older-than' => 60])
        ->expectsOutputToContain('FORCE EXECUTION')
        ->assertSuccessful();

    // The confirmed orphaned file MUST be deleted
    expect(Storage::disk('local')->exists($orphanedPath))->toBeFalse()
        // Active files MUST be preserved
        ->and(Storage::disk('local')->exists('wna/photos/active_wna.jpg'))->toBeTrue()
        ->and(Storage::disk('local')->exists('abg/photos/active_abg.jpg'))->toBeTrue()
        // In-flight recent file MUST be preserved
        ->and(Storage::disk('local')->exists($recentPath))->toBeTrue();
});

test('reconciliation command respects module filter', function () {
    Storage::fake('local');
    Storage::fake('public');

    // Orphan in WNA
    $wnaOrphan = 'wna/photos/wna_orphan.jpg';
    Storage::disk('local')->put($wnaOrphan, 'content');
    touch(Storage::disk('local')->path($wnaOrphan), time() - 7200);

    // Orphan in ABG
    $abgOrphan = 'abg/photos/abg_orphan.jpg';
    Storage::disk('local')->put($abgOrphan, 'content');
    touch(Storage::disk('local')->path($abgOrphan), time() - 7200);

    // Audit only WNA with --force
    $this->artisan('storage:reconcile-photos', ['--module' => 'wna', '--force' => true, '--older-than' => 60])
        ->assertSuccessful();

    // WNA orphan deleted, ABG orphan untouched
    expect(Storage::disk('local')->exists($wnaOrphan))->toBeFalse()
        ->and(Storage::disk('local')->exists($abgOrphan))->toBeTrue();
});
