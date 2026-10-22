<?php

use App\Enums\UserRole;
use App\Models\RealisasiPnbp;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing realisasi pnbp endpoints', function () {
    $record = RealisasiPnbp::factory()->create();

    $this->get('/realisasi-pnbp')->assertRedirect('/login');
    $this->get('/realisasi-pnbp/'.$record->id)->assertRedirect('/login');
    $this->get('/realisasi-pnbp/print-list')->assertRedirect('/login');
    $this->get('/realisasi-pnbp/export-pdf')->assertRedirect('/login');
    $this->get('/realisasi-pnbp/create')->assertRedirect('/login');
    $this->get('/realisasi-pnbp/'.$record->id.'/edit')->assertRedirect('/login');
    $this->post('/realisasi-pnbp', [])->assertRedirect('/login');
    $this->put('/realisasi-pnbp/'.$record->id, [])->assertRedirect('/login');
    $this->delete('/realisasi-pnbp/'.$record->id)->assertRedirect('/login');
});

test('petugas has read-only access to realisasi pnbp index and detail, but is forbidden from mutations and exports', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $record = RealisasiPnbp::factory()->create();

    $this->actingAs($petugas)->get('/realisasi-pnbp')->assertOk();
    $this->actingAs($petugas)->get('/realisasi-pnbp/'.$record->id)->assertOk();
    $this->actingAs($petugas)->get('/realisasi-pnbp/print-list')->assertForbidden();
    $this->actingAs($petugas)->get('/realisasi-pnbp/export-pdf')->assertForbidden();
    $this->actingAs($petugas)->get('/realisasi-pnbp/create')->assertForbidden();
    $this->actingAs($petugas)->get('/realisasi-pnbp/'.$record->id.'/edit')->assertForbidden();
    $this->actingAs($petugas)->post('/realisasi-pnbp', [
        'bulan' => '2026-10-01',
        'target' => 100000000,
        'realisasi' => 120000000,
    ])->assertForbidden();
    $this->actingAs($petugas)->put('/realisasi-pnbp/'.$record->id, [
        'bulan' => '2026-10-01',
        'target' => 100000000,
        'realisasi' => 120000000,
    ])->assertForbidden();
    $this->actingAs($petugas)->delete('/realisasi-pnbp/'.$record->id)->assertForbidden();
});

test('admin can view realisasi pnbp index with inertia props and summary', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    RealisasiPnbp::factory()->create([
        'bulan' => '2026-01-01',
        'target' => 100000000,
        'realisasi' => 110000000,
    ]);
    RealisasiPnbp::factory()->create([
        'bulan' => '2026-02-01',
        'target' => 100000000,
        'realisasi' => 90000000,
    ]);

    $response = $this->actingAs($admin)->get('/realisasi-pnbp');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/Index')
            ->has('realisasiPnbp.data', 2)
            ->has('filters')
            ->has('tahunOptions')
            ->where('summary.total_target', 200000000)
            ->where('summary.total_realisasi', 200000000)
            ->where('summary.total_selisih', 0)
            ->where('summary.persentase_total', 100)
            ->where('can.create', true)
            ->where('can.printList', true)
        );
});

test('admin can create a new realisasi pnbp record with valid data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'bulan' => '2026-10-15', // Should normalize to 2026-10-01
        'target' => 150000000,
        'realisasi' => 180000000,
        'keterangan' => 'Peningkatan permohonan paspor dan visa akhir tahun.',
    ];

    $response = $this->actingAs($admin)->post('/realisasi-pnbp', $payload);

    $response->assertRedirect('/realisasi-pnbp')
        ->assertSessionHas('success', 'Data realisasi PNBP berhasil ditambahkan.');

    $this->assertDatabaseHas('realisasi_pnbp', [
        'bulan' => '2026-10-01',
        'target' => 150000000,
        'realisasi' => 180000000,
        'keterangan' => 'Peningkatan permohonan paspor dan visa akhir tahun.',
    ]);

    $created = RealisasiPnbp::where('bulan', '2026-10-01')->firstOrFail();
    expect($created->selisih)->toBe(30000000.0)
        ->and($created->persentase)->toBe(120.0);
});

test('admin can create realisasi pnbp with nullable keterangan omitted', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'bulan' => '2026-11-01',
        'target' => 50000000,
        'realisasi' => 45000000,
    ];

    $response = $this->actingAs($admin)->post('/realisasi-pnbp', $payload);

    $response->assertRedirect('/realisasi-pnbp')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('realisasi_pnbp', [
        'bulan' => '2026-11-01',
        'target' => 50000000,
        'realisasi' => 45000000,
        'keterangan' => null,
    ]);

    $created = RealisasiPnbp::where('bulan', '2026-11-01')->firstOrFail();
    expect($created->selisih)->toBe(-5000000.0)
        ->and($created->persentase)->toBe(90.0);
});

test('bulan must be unique and cannot be duplicated in store', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    RealisasiPnbp::factory()->create([
        'bulan' => '2026-05-01',
    ]);

    // Attempt to create another record for the same month (even with different day)
    $response = $this->actingAs($admin)->post('/realisasi-pnbp', [
        'bulan' => '2026-05-20',
        'target' => 100000000,
        'realisasi' => 100000000,
    ]);

    $response->assertSessionHasErrors(['bulan']);
});

test('validation fails when negative target or realisasi is supplied', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/realisasi-pnbp', [
        'bulan' => '2026-06-01',
        'target' => -50000,
        'realisasi' => -10000,
    ]);

    $response->assertSessionHasErrors(['target', 'realisasi']);
});

test('validation fails when required fields are missing', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/realisasi-pnbp', []);

    $response->assertSessionHasErrors(['bulan', 'target', 'realisasi']);
});

test('admin can view specific realisasi pnbp detail', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $record = RealisasiPnbp::factory()->create([
        'bulan' => '2026-07-01',
        'target' => 100000000,
        'realisasi' => 125000000,
    ]);

    $response = $this->actingAs($admin)->get('/realisasi-pnbp/'.$record->id);

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/Show')
            ->where('realisasiPnbp.id', $record->id)
            ->where('realisasiPnbp.bulan', '2026-07-01')
        );

    // JSON response
    $json = $this->actingAs($admin)->getJson('/realisasi-pnbp/'.$record->id);
    $json->assertOk()
        ->assertJsonPath('realisasiPnbp.id', $record->id)
        ->assertJsonPath('realisasiPnbp.persentase', 125);
});

test('admin can update an existing realisasi pnbp record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $record = RealisasiPnbp::factory()->create([
        'bulan' => '2026-08-01',
        'target' => 100000000,
        'realisasi' => 80000000,
        'keterangan' => 'Old note',
    ]);

    $response = $this->actingAs($admin)->put('/realisasi-pnbp/'.$record->id, [
        'bulan' => '2026-08-01', // Same month allowed for self
        'target' => 110000000,
        'realisasi' => 132000000,
        'keterangan' => 'Updated note',
    ]);

    $response->assertRedirect('/realisasi-pnbp')
        ->assertSessionHas('success', 'Data realisasi PNBP berhasil diperbarui.');

    $this->assertDatabaseHas('realisasi_pnbp', [
        'id' => $record->id,
        'bulan' => '2026-08-01',
        'target' => 110000000,
        'realisasi' => 132000000,
        'keterangan' => 'Updated note',
    ]);

    expect($record->fresh()->persentase)->toBe(120.0);
});

test('updating to an existing month of another record fails validation', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $recordA = RealisasiPnbp::factory()->create(['bulan' => '2026-01-01']);
    $recordB = RealisasiPnbp::factory()->create(['bulan' => '2026-02-01']);

    // Attempt to update recordB's month to match recordA
    $response = $this->actingAs($admin)->put('/realisasi-pnbp/'.$recordB->id, [
        'bulan' => '2026-01-10', // Normalizes to 2026-01-01
        'target' => 100000000,
        'realisasi' => 100000000,
    ]);

    $response->assertSessionHasErrors(['bulan']);
});

test('admin can delete a realisasi pnbp record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $record = RealisasiPnbp::factory()->create();

    $response = $this->actingAs($admin)->delete('/realisasi-pnbp/'.$record->id);

    $response->assertRedirect('/realisasi-pnbp')
        ->assertSessionHas('success', 'Data realisasi PNBP berhasil dihapus.');

    $this->assertDatabaseMissing('realisasi_pnbp', [
        'id' => $record->id,
    ]);
});

test('admin can search and filter realisasi pnbp by keyword and year', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    RealisasiPnbp::factory()->create([
        'bulan' => '2025-06-01',
        'keterangan' => 'Rekap tahun lalu',
    ]);
    RealisasiPnbp::factory()->create([
        'bulan' => '2026-03-01',
        'keterangan' => 'Rekap khusus Maret',
    ]);

    // Filter by year
    $resTahun = $this->actingAs($admin)->get('/realisasi-pnbp?tahun=2026');
    $resTahun->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('realisasiPnbp.data', 1)
            ->where('realisasiPnbp.data.0.bulan', '2026-03-01')
        );

    // Search by keyword in keterangan
    $resSearch = $this->actingAs($admin)->get('/realisasi-pnbp?search=Maret');
    $resSearch->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('realisasiPnbp.data', 1)
            ->where('realisasiPnbp.data.0.keterangan', 'Rekap khusus Maret')
        );
});

test('admin can sort and paginate realisasi pnbp records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    RealisasiPnbp::factory()->create(['bulan' => '2026-01-01', 'target' => 100000]);
    RealisasiPnbp::factory()->create(['bulan' => '2026-02-01', 'target' => 500000]);

    // Sort target asc
    $resSortAsc = $this->actingAs($admin)->get('/realisasi-pnbp?sort_by=target&sort_order=asc');
    $resSortAsc->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('realisasiPnbp.data.0.target', '100000.00')
            ->where('realisasiPnbp.data.1.target', '500000.00')
        );

    // Sort target desc
    $resSortDesc = $this->actingAs($admin)->get('/realisasi-pnbp?sort_by=target&sort_order=desc');
    $resSortDesc->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('realisasiPnbp.data.0.target', '500000.00')
            ->where('realisasiPnbp.data.1.target', '100000.00')
        );
});

test('invalid sort or pagination parameters are safely rejected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get('/realisasi-pnbp?sort_by=evil_col&sort_order=not_asc&per_page=9999');

    $response->assertSessionHasErrors(['sort_by', 'sort_order', 'per_page']);
});

test('returns 404 for non-existent realisasi pnbp record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/realisasi-pnbp/999999')->assertNotFound();
    $this->actingAs($admin)->put('/realisasi-pnbp/999999', [
        'bulan' => '2026-10-01',
        'target' => 10000000,
        'realisasi' => 10000000,
    ])->assertNotFound();
    $this->actingAs($admin)->delete('/realisasi-pnbp/999999')->assertNotFound();
});

test('admin can access printable realisasi pnbp report with summary', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    RealisasiPnbp::factory()->create([
        'bulan' => '2026-01-01',
        'target' => 100000000,
        'realisasi' => 110000000,
    ]);

    $response = $this->actingAs($admin)->get('/realisasi-pnbp/print-list');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/PrintList')
            ->has('realisasiList', 1)
            ->has('summary')
            ->where('summary.total_target', 100000000)
            ->where('summary.total_realisasi', 110000000)
            ->where('summary.total_selisih', 10000000)
            ->where('summary.persentase_total', 110)
            ->has('filters')
            ->has('printedAt')
        );

    // export-pdf alias
    $aliasResponse = $this->actingAs($admin)->get('/realisasi-pnbp/export-pdf');
    $aliasResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/PrintList')
            ->has('realisasiList', 1)
        );
});

test('admin can access printable report when table is empty', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get('/realisasi-pnbp/print-list');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/PrintList')
            ->has('realisasiList', 0)
            ->where('summary.total_target', 0)
            ->where('summary.total_realisasi', 0)
        );
});
