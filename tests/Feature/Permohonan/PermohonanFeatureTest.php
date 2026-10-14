<?php

use App\Enums\UserRole;
use App\Models\Permohonan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing permohonan endpoints', function () {
    $permohonan = Permohonan::factory()->create();

    $this->get('/permohonan')->assertRedirect('/login');
    $this->get('/permohonan/'.$permohonan->id)->assertRedirect('/login');
    $this->get('/permohonan/print-list')->assertRedirect('/login');
    $this->get('/permohonan/export-pdf')->assertRedirect('/login');
    $this->get('/permohonan/create')->assertRedirect('/login');
    $this->get('/permohonan/'.$permohonan->id.'/edit')->assertRedirect('/login');
    $this->post('/permohonan', [])->assertRedirect('/login');
    $this->put('/permohonan/'.$permohonan->id, [])->assertRedirect('/login');
    $this->delete('/permohonan/'.$permohonan->id)->assertRedirect('/login');
});

test('petugas is forbidden from accessing all permohonan endpoints according to prd', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $permohonan = Permohonan::factory()->create();

    $this->actingAs($petugas)->get('/permohonan')->assertForbidden();
    $this->actingAs($petugas)->get('/permohonan/'.$permohonan->id)->assertForbidden();
    $this->actingAs($petugas)->get('/permohonan/print-list')->assertForbidden();
    $this->actingAs($petugas)->get('/permohonan/export-pdf')->assertForbidden();
    $this->actingAs($petugas)->get('/permohonan/create')->assertForbidden();
    $this->actingAs($petugas)->get('/permohonan/'.$permohonan->id.'/edit')->assertForbidden();
    $this->actingAs($petugas)->post('/permohonan', [
        'nama' => 'Test Pemohon',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Perpanjangan ITAS',
        'tanggal' => '2026-10-14',
    ])->assertForbidden();
    $this->actingAs($petugas)->put('/permohonan/'.$permohonan->id, [
        'nama' => 'Update Pemohon',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Perpanjangan ITAS',
        'tanggal' => '2026-10-14',
    ])->assertForbidden();
    $this->actingAs($petugas)->delete('/permohonan/'.$permohonan->id)->assertForbidden();
});

test('admin can view permohonan index with inertia props and filters', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Permohonan::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/permohonan');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/Index')
            ->has('permohonan.data', 3)
            ->has('filters')
            ->has('layananOptions')
            ->has('kewarganegaraanOptions')
            ->has('kabupatenOptions')
            ->where('can.create', true)
            ->where('can.printList', true)
        );
});

test('admin can create a new permohonan with valid data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'nama' => 'Hans Gruber',
        'no_paspor' => 'DE12345678',
        'kewarganegaraan' => 'Jerman',
        'layanan' => 'Alih Status ITAP',
        'tanggal' => '2026-10-14',
        'kabupaten' => 'Wonosobo',
        'permohonan' => 'Permohonan alih status izin tinggal tetap atas dasar perkawinan campur.',
    ];

    $response = $this->actingAs($admin)->post('/permohonan', $payload);

    $response->assertRedirect('/permohonan')
        ->assertSessionHas('success', 'Data permohonan keimigrasian berhasil ditambahkan.');

    $this->assertDatabaseHas('permohonan', [
        'nama' => 'Hans Gruber',
        'no_paspor' => 'DE12345678',
        'kewarganegaraan' => 'Jerman',
        'layanan' => 'Alih Status ITAP',
        'kabupaten' => 'Wonosobo',
    ]);

    $created = Permohonan::where('nama', 'Hans Gruber')->firstOrFail();
    expect($created->tanggal->format('Y-m-d'))->toBe('2026-10-14')
        ->and($created->permohonan)->toBe('Permohonan alih status izin tinggal tetap atas dasar perkawinan campur.');
});

test('admin can create permohonan with only required fields (nullable fields omitted)', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'nama' => 'Maria Santos',
        'kewarganegaraan' => 'Filipina',
        'layanan' => 'Perpanjangan ITAS',
        'tanggal' => '2026-10-14',
    ];

    $response = $this->actingAs($admin)->post('/permohonan', $payload);

    $response->assertRedirect('/permohonan')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('permohonan', [
        'nama' => 'Maria Santos',
        'no_paspor' => null,
        'kewarganegaraan' => 'Filipina',
        'layanan' => 'Perpanjangan ITAS',
        'kabupaten' => null,
        'permohonan' => null,
    ]);

    $created = Permohonan::where('nama', 'Maria Santos')->firstOrFail();
    expect($created->tanggal->format('Y-m-d'))->toBe('2026-10-14');
});

test('validation fails when required fields are missing', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/permohonan', []);

    $response->assertSessionHasErrors(['nama', 'kewarganegaraan', 'layanan', 'tanggal']);
});

test('validation fails when tanggal is not a valid date', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/permohonan', [
        'nama' => 'Test User',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Perpanjangan ITAS',
        'tanggal' => 'invalid-date-format',
    ]);

    $response->assertSessionHasErrors(['tanggal']);
});

test('validation fails when string fields exceed maximum lengths', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/permohonan', [
        'nama' => str_repeat('A', 181),
        'kewarganegaraan' => str_repeat('B', 101),
        'layanan' => str_repeat('C', 151),
        'no_paspor' => str_repeat('D', 81),
        'kabupaten' => str_repeat('E', 121),
        'tanggal' => '2026-10-14',
    ]);

    $response->assertSessionHasErrors(['nama', 'kewarganegaraan', 'layanan', 'no_paspor', 'kabupaten']);
});

test('admin can view specific permohonan detail', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $permohonan = Permohonan::factory()->create([
        'nama' => 'Tanaka Kenji',
        'layanan' => 'Pemberian EPO',
    ]);

    $response = $this->actingAs($admin)->get('/permohonan/'.$permohonan->id);

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/Show')
            ->where('permohonan.id', $permohonan->id)
            ->where('permohonan.nama', 'Tanaka Kenji')
            ->where('permohonan.layanan', 'Pemberian EPO')
        );

    // Also supports JSON request
    $jsonResponse = $this->actingAs($admin)->getJson('/permohonan/'.$permohonan->id);
    $jsonResponse->assertOk()
        ->assertJsonPath('permohonan.id', $permohonan->id)
        ->assertJsonPath('permohonan.nama', 'Tanaka Kenji');
});

test('admin can update an existing permohonan', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $permohonan = Permohonan::factory()->create([
        'nama' => 'Original Name',
        'layanan' => 'Perpanjangan ITAS',
        'kabupaten' => 'Wonosobo',
    ]);

    $updatePayload = [
        'nama' => 'Updated Name',
        'no_paspor' => 'PAS123999',
        'kewarganegaraan' => 'Korea Selatan',
        'layanan' => 'Alih Status ITAP',
        'tanggal' => '2026-10-20',
        'kabupaten' => 'Purbalingga',
        'permohonan' => 'Rincian berkas telah diperbarui.',
    ];

    $response = $this->actingAs($admin)->put('/permohonan/'.$permohonan->id, $updatePayload);

    $response->assertRedirect('/permohonan')
        ->assertSessionHas('success', 'Data permohonan keimigrasian berhasil diperbarui.');

    $this->assertDatabaseHas('permohonan', [
        'id' => $permohonan->id,
        'nama' => 'Updated Name',
        'no_paspor' => 'PAS123999',
        'kewarganegaraan' => 'Korea Selatan',
        'layanan' => 'Alih Status ITAP',
        'kabupaten' => 'Purbalingga',
    ]);

    expect($permohonan->fresh()->tanggal->format('Y-m-d'))->toBe('2026-10-20')
        ->and($permohonan->fresh()->permohonan)->toBe('Rincian berkas telah diperbarui.');
});

test('admin can delete a permohonan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $permohonan = Permohonan::factory()->create();

    $response = $this->actingAs($admin)->delete('/permohonan/'.$permohonan->id);

    $response->assertRedirect('/permohonan')
        ->assertSessionHas('success', 'Data permohonan keimigrasian berhasil dihapus.');

    $this->assertDatabaseMissing('permohonan', [
        'id' => $permohonan->id,
    ]);
});

test('admin can search permohonan by nama, no_paspor, layanan, kewarganegaraan, and kabupaten', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Permohonan::factory()->create([
        'nama' => 'Unique Alice',
        'no_paspor' => 'PASS111',
        'layanan' => 'Perpanjangan ITAS',
        'kewarganegaraan' => 'Kanada',
        'kabupaten' => 'Wonosobo',
    ]);

    Permohonan::factory()->create([
        'nama' => 'Bob Smith',
        'no_paspor' => 'PASS222',
        'layanan' => 'Alih Status ITAP',
        'kewarganegaraan' => 'Australia',
        'kabupaten' => 'Kebumen',
    ]);

    // Search by nama
    $response = $this->actingAs($admin)->get('/permohonan?search=Alice');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Unique Alice')
        );

    // Search by no_paspor
    $response = $this->actingAs($admin)->get('/permohonan?search=PASS222');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Bob Smith')
        );

    // Search by layanan
    $response = $this->actingAs($admin)->get('/permohonan?search=Alih Status');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Bob Smith')
        );

    // Search by kabupaten
    $response = $this->actingAs($admin)->get('/permohonan?search=Kebumen');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Bob Smith')
        );
});

test('admin can filter permohonan by layanan, kewarganegaraan, and kabupaten', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Permohonan::factory()->create([
        'nama' => 'Person One',
        'layanan' => 'Layanan Khusus A',
        'kewarganegaraan' => 'Prancis',
        'kabupaten' => 'Wonosobo',
    ]);

    Permohonan::factory()->create([
        'nama' => 'Person Two',
        'layanan' => 'Layanan Khusus B',
        'kewarganegaraan' => 'Jerman',
        'kabupaten' => 'Banjarnegara',
    ]);

    $response = $this->actingAs($admin)->get('/permohonan?layanan=Layanan Khusus A');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Person One')
        );

    $response = $this->actingAs($admin)->get('/permohonan?kewarganegaraan=Jerman');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Person Two')
        );

    $response = $this->actingAs($admin)->get('/permohonan?kabupaten=Wonosobo');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('permohonan.data', 1)
            ->where('permohonan.data.0.nama', 'Person One')
        );
});

test('admin can sort and paginate permohonan records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Permohonan::factory()->create(['nama' => 'Aaron Adams', 'tanggal' => '2026-01-01']);
    Permohonan::factory()->create(['nama' => 'Zack Zimmerman', 'tanggal' => '2026-02-01']);

    // Sort by nama asc
    $response = $this->actingAs($admin)->get('/permohonan?sort_by=nama&sort_order=asc');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permohonan.data.0.nama', 'Aaron Adams')
            ->where('permohonan.data.1.nama', 'Zack Zimmerman')
        );

    // Sort by nama desc
    $response = $this->actingAs($admin)->get('/permohonan?sort_by=nama&sort_order=desc');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permohonan.data.0.nama', 'Zack Zimmerman')
            ->where('permohonan.data.1.nama', 'Aaron Adams')
        );

    // Pagination
    Permohonan::factory()->count(15)->create();
    $pageResponse = $this->actingAs($admin)->get('/permohonan?per_page=10');
    $pageResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permohonan.per_page', 10)
            ->where('permohonan.total', 17)
            ->has('permohonan.data', 10)
        );
});

test('invalid sort or filter parameters are safely validated', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get('/permohonan?sort_by=malicious_col&sort_order=invalidsort&per_page=9999');

    // Validation fails safely and redirects with session errors rather than executing unsafe SQL
    $response->assertSessionHasErrors(['sort_by', 'sort_order', 'per_page']);
});

test('returns 404 for non-existent permohonan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/permohonan/999999')->assertNotFound();
    $this->actingAs($admin)->put('/permohonan/999999', [
        'nama' => 'Non Existent',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Perpanjangan ITAS',
        'tanggal' => '2026-10-14',
    ])->assertNotFound();
    $this->actingAs($admin)->delete('/permohonan/999999')->assertNotFound();
});

test('admin can access printable report page with filtered records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Permohonan::factory()->create([
        'nama' => 'Printable Pemohon',
        'layanan' => 'Perpanjangan ITAS',
    ]);
    Permohonan::factory()->create([
        'nama' => 'Excluded Pemohon',
        'layanan' => 'Alih Status ITAP',
    ]);

    $response = $this->actingAs($admin)->get('/permohonan/print-list?layanan=Perpanjangan ITAS');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/PrintList')
            ->has('permohonanList', 1)
            ->where('permohonanList.0.nama', 'Printable Pemohon')
            ->has('filters')
            ->has('printedAt')
        );

    // export-pdf alias
    $aliasResponse = $this->actingAs($admin)->get('/permohonan/export-pdf?layanan=Perpanjangan ITAS');
    $aliasResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/PrintList')
            ->has('permohonanList', 1)
        );
});
