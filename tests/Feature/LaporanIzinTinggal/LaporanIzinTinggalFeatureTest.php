<?php

use App\Enums\UserRole;
use App\Models\LaporanIzinTinggal;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing laporan izin tinggal endpoints', function () {
    $laporan = LaporanIzinTinggal::factory()->create();

    $this->get('/laporan-izin-tinggal')->assertRedirect('/login');
    $this->get('/laporan-izin-tinggal/'.$laporan->id)->assertRedirect('/login');
    $this->get('/laporan-izin-tinggal/print-list')->assertRedirect('/login');
    $this->get('/laporan-izin-tinggal/export-pdf')->assertRedirect('/login');
    $this->get('/laporan-izin-tinggal/create')->assertRedirect('/login');
    $this->get('/laporan-izin-tinggal/'.$laporan->id.'/edit')->assertRedirect('/login');
    $this->post('/laporan-izin-tinggal', [])->assertRedirect('/login');
    $this->put('/laporan-izin-tinggal/'.$laporan->id, [])->assertRedirect('/login');
    $this->delete('/laporan-izin-tinggal/'.$laporan->id)->assertRedirect('/login');
});

test('petugas is forbidden from accessing all laporan izin tinggal endpoints according to prd', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $laporan = LaporanIzinTinggal::factory()->create();

    $this->actingAs($petugas)->get('/laporan-izin-tinggal')->assertForbidden();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/'.$laporan->id)->assertForbidden();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/print-list')->assertForbidden();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/export-pdf')->assertForbidden();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/create')->assertForbidden();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/'.$laporan->id.'/edit')->assertForbidden();
    $this->actingAs($petugas)->post('/laporan-izin-tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 10,
        'disetujui_p' => 10,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ])->assertForbidden();
    $this->actingAs($petugas)->put('/laporan-izin-tinggal/'.$laporan->id, [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 15,
        'disetujui_p' => 12,
        'ditolak_l' => 1,
        'ditolak_p' => 0,
    ])->assertForbidden();
    $this->actingAs($petugas)->delete('/laporan-izin-tinggal/'.$laporan->id)->assertForbidden();
});

test('admin can view laporan izin tinggal index with inertia props and summary', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    LaporanIzinTinggal::factory()->create([
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 10,
        'disetujui_p' => 5,
        'ditolak_l' => 2,
        'ditolak_p' => 1,
    ]);

    $response = $this->actingAs($admin)->get('/laporan-izin-tinggal');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/Index')
            ->has('laporan.data', 1)
            ->has('summary')
            ->where('summary.total_disetujui_l', 10)
            ->where('summary.total_disetujui_p', 5)
            ->where('summary.total_ditolak_l', 2)
            ->where('summary.total_ditolak_p', 1)
            ->where('summary.grand_total_disetujui', 15)
            ->where('summary.grand_total_ditolak', 3)
            ->where('summary.grand_total', 18)
            ->has('filters')
            ->has('kategoriOptions')
            ->has('layananOptions')
            ->where('can.create', true)
            ->where('can.printList', true)
        );
});

test('admin can create a new laporan izin tinggal with all fields', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'kategori' => 'Izin Tinggal Terbatas',
        'layanan' => 'Alih Status',
        'disetujui_l' => 14,
        'disetujui_p' => 11,
        'ditolak_l' => 3,
        'ditolak_p' => 2,
        'keterangan' => 'Rekapitulasi berkas kuartal pertama tahun 2026.',
    ];

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', $payload);

    $response->assertRedirect('/laporan-izin-tinggal')
        ->assertSessionHas('success', 'Data laporan izin tinggal berhasil ditambahkan.');

    $this->assertDatabaseHas('laporan_izin_tinggal', [
        'kategori' => 'Izin Tinggal Terbatas',
        'layanan' => 'Alih Status',
        'disetujui_l' => 14,
        'disetujui_p' => 11,
        'ditolak_l' => 3,
        'ditolak_p' => 2,
        'keterangan' => 'Rekapitulasi berkas kuartal pertama tahun 2026.',
    ]);

    $created = LaporanIzinTinggal::where('layanan', 'Alih Status')->firstOrFail();
    expect($created->total_disetujui)->toBe(25)
        ->and($created->total_ditolak)->toBe(5)
        ->and($created->total)->toBe(30);
});

test('admin can create laporan izin tinggal with nullable keterangan omitted', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'kategori' => 'Izin Tinggal Tetap',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 5,
        'disetujui_p' => 4,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ];

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', $payload);

    $response->assertRedirect('/laporan-izin-tinggal')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('laporan_izin_tinggal', [
        'kategori' => 'Izin Tinggal Tetap',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 5,
        'disetujui_p' => 4,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
        'keterangan' => null,
    ]);
});

test('validation fails when required fields are missing', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', []);

    $response->assertSessionHasErrors(['kategori', 'layanan', 'disetujui_l', 'disetujui_p', 'ditolak_l', 'ditolak_p']);
});

test('validation fails when numeric counts are negative', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => -1,
        'disetujui_p' => -10,
        'ditolak_l' => -5,
        'ditolak_p' => 0,
    ]);

    $response->assertSessionHasErrors(['disetujui_l', 'disetujui_p', 'ditolak_l']);
});

test('validation fails when numeric counts are decimals', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => 12.5,
        'disetujui_p' => 5.2,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]);

    $response->assertSessionHasErrors(['disetujui_l', 'disetujui_p']);
});

test('validation fails when numeric counts are non-numeric strings', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => 'sepuluh',
        'disetujui_p' => 'lima',
        'ditolak_l' => 'nol',
        'ditolak_p' => 'nol',
    ]);

    $response->assertSessionHasErrors(['disetujui_l', 'disetujui_p', 'ditolak_l', 'ditolak_p']);
});

test('validation allows zero values for count fields', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => 0,
        'disetujui_p' => 0,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]);

    $response->assertRedirect('/laporan-izin-tinggal')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('laporan_izin_tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => 0,
        'disetujui_p' => 0,
    ]);
});

test('validation fails when count exceeds reasonable upper limit', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => 1000001,
        'disetujui_p' => 0,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]);

    $response->assertSessionHasErrors(['disetujui_l']);
});

test('validation fails when strings exceed maximum lengths', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/laporan-izin-tinggal', [
        'kategori' => str_repeat('A', 121),
        'layanan' => str_repeat('B', 151),
        'disetujui_l' => 1,
        'disetujui_p' => 1,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ]);

    $response->assertSessionHasErrors(['kategori', 'layanan']);
});

test('admin can view specific laporan detail', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $laporan = LaporanIzinTinggal::factory()->create([
        'kategori' => 'Izin Tinggal Terbatas',
        'layanan' => 'Alih Status',
        'disetujui_l' => 20,
        'disetujui_p' => 15,
        'ditolak_l' => 3,
        'ditolak_p' => 2,
    ]);

    $response = $this->actingAs($admin)->get('/laporan-izin-tinggal/'.$laporan->id);

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/Show')
            ->where('laporan.id', $laporan->id)
            ->where('laporan.kategori', 'Izin Tinggal Terbatas')
            ->where('laporan.layanan', 'Alih Status')
            ->where('laporan.total_disetujui', 35)
            ->where('laporan.total_ditolak', 5)
            ->where('laporan.total', 40)
        );

    // Also supports JSON request
    $jsonResponse = $this->actingAs($admin)->getJson('/laporan-izin-tinggal/'.$laporan->id);
    $jsonResponse->assertOk()
        ->assertJsonPath('laporan.id', $laporan->id)
        ->assertJsonPath('laporan.kategori', 'Izin Tinggal Terbatas');
});

test('admin can update an existing laporan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $laporan = LaporanIzinTinggal::factory()->create([
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 10,
        'disetujui_p' => 10,
        'ditolak_l' => 1,
        'ditolak_p' => 1,
    ]);

    $payload = [
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Perpanjangan',
        'disetujui_l' => 25,
        'disetujui_p' => 15,
        'ditolak_l' => 2,
        'ditolak_p' => 0,
        'keterangan' => 'Jumlah telah disesuaikan berdasarkan verifikasi loket.',
    ];

    $response = $this->actingAs($admin)->put('/laporan-izin-tinggal/'.$laporan->id, $payload);

    $response->assertRedirect('/laporan-izin-tinggal')
        ->assertSessionHas('success', 'Data laporan izin tinggal berhasil diperbarui.');

    $this->assertDatabaseHas('laporan_izin_tinggal', [
        'id' => $laporan->id,
        'disetujui_l' => 25,
        'disetujui_p' => 15,
        'ditolak_l' => 2,
        'ditolak_p' => 0,
        'keterangan' => 'Jumlah telah disesuaikan berdasarkan verifikasi loket.',
    ]);

    $laporan->refresh();
    expect($laporan->total_disetujui)->toBe(40)
        ->and($laporan->total_ditolak)->toBe(2)
        ->and($laporan->total)->toBe(42);
});

test('admin can delete a laporan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $laporan = LaporanIzinTinggal::factory()->create();

    $response = $this->actingAs($admin)->delete('/laporan-izin-tinggal/'.$laporan->id);

    $response->assertRedirect('/laporan-izin-tinggal')
        ->assertSessionHas('success', 'Data laporan izin tinggal berhasil dihapus.');

    $this->assertDatabaseMissing('laporan_izin_tinggal', [
        'id' => $laporan->id,
    ]);
});

test('laporan record not found returns 404', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/laporan-izin-tinggal/999999')->assertNotFound();
    $this->actingAs($admin)->put('/laporan-izin-tinggal/999999', [
        'kategori' => 'Test',
        'layanan' => 'Test',
        'disetujui_l' => 1,
        'disetujui_p' => 1,
        'ditolak_l' => 0,
        'ditolak_p' => 0,
    ])->assertNotFound();
    $this->actingAs($admin)->delete('/laporan-izin-tinggal/999999')->assertNotFound();
});

test('admin can filter laporan records by search query, kategori, and layanan', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    LaporanIzinTinggal::factory()->create([
        'kategori' => 'Izin Tinggal Kunjungan',
        'layanan' => 'Pemberian Baru',
        'keterangan' => 'Catatan khusus wisman',
    ]);
    LaporanIzinTinggal::factory()->create([
        'kategori' => 'Izin Tinggal Terbatas',
        'layanan' => 'Perpanjangan',
        'keterangan' => 'Pekerja asing',
    ]);

    // Search by keyword
    $response = $this->actingAs($admin)->get('/laporan-izin-tinggal?search=wisman');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('laporan.data', 1)
            ->where('laporan.data.0.kategori', 'Izin Tinggal Kunjungan')
        );

    // Filter by kategori
    $responseKategori = $this->actingAs($admin)->get('/laporan-izin-tinggal?kategori=Izin+Tinggal+Terbatas');
    $responseKategori->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('laporan.data', 1)
            ->where('laporan.data.0.kategori', 'Izin Tinggal Terbatas')
        );

    // Filter by layanan
    $responseLayanan = $this->actingAs($admin)->get('/laporan-izin-tinggal?layanan=Pemberian+Baru');
    $responseLayanan->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('laporan.data', 1)
            ->where('laporan.data.0.layanan', 'Pemberian Baru')
        );
});

test('admin can sort and paginate laporan records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    LaporanIzinTinggal::factory()->create([
        'kategori' => 'Kategori Alpha',
        'layanan' => 'Layanan Alpha',
        'disetujui_l' => 50,
    ]);
    LaporanIzinTinggal::factory()->create([
        'kategori' => 'Kategori Beta',
        'layanan' => 'Layanan Beta',
        'disetujui_l' => 10,
    ]);

    $responseSort = $this->actingAs($admin)->get('/laporan-izin-tinggal?sort_by=disetujui_l&sort_order=desc');
    $responseSort->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('laporan.data.0.disetujui_l', 50)
            ->where('laporan.data.1.disetujui_l', 10)
        );

    // Pagination limit check
    LaporanIzinTinggal::factory()->count(15)->create();
    $responsePage = $this->actingAs($admin)->get('/laporan-izin-tinggal?per_page=10');
    $responsePage->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('laporan.data', 10)
            ->where('laporan.per_page', 10)
        );
});

test('admin can access print-list and export-pdf endpoints', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    LaporanIzinTinggal::factory()->create([
        'kategori' => 'Izin Tinggal Tetap',
        'layanan' => 'Pemberian Baru',
        'disetujui_l' => 8,
        'disetujui_p' => 6,
        'ditolak_l' => 1,
        'ditolak_p' => 0,
    ]);

    $responsePrint = $this->actingAs($admin)->get('/laporan-izin-tinggal/print-list');
    $responsePrint->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/PrintList')
            ->has('laporanList', 1)
            ->has('summary')
            ->where('summary.grand_total_disetujui', 14)
            ->where('summary.grand_total_ditolak', 1)
            ->where('summary.grand_total', 15)
            ->has('filters')
            ->has('printedAt')
        );

    // Alias export-pdf
    $responseExport = $this->actingAs($admin)->get('/laporan-izin-tinggal/export-pdf');
    $responseExport->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/PrintList')
            ->has('laporanList', 1)
        );
});
