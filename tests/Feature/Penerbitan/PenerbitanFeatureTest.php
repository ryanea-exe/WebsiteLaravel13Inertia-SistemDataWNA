<?php

use App\Enums\UserRole;
use App\Models\Penerbitan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing penerbitan endpoints', function () {
    $penerbitan = Penerbitan::factory()->create();

    $this->get('/penerbitan')->assertRedirect('/login');
    $this->get('/penerbitan/'.$penerbitan->id)->assertRedirect('/login');
    $this->get('/penerbitan/print-list')->assertRedirect('/login');
    $this->get('/penerbitan/export-pdf')->assertRedirect('/login');
    $this->get('/penerbitan/create')->assertRedirect('/login');
    $this->get('/penerbitan/'.$penerbitan->id.'/edit')->assertRedirect('/login');
    $this->post('/penerbitan', [])->assertRedirect('/login');
    $this->put('/penerbitan/'.$penerbitan->id, [])->assertRedirect('/login');
    $this->delete('/penerbitan/'.$penerbitan->id)->assertRedirect('/login');
});

test('petugas has read-only access to penerbitan index and detail, can export pdf (CR-011), but is forbidden from mutations', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $penerbitan = Penerbitan::factory()->create();

    $this->actingAs($petugas)->get('/penerbitan')->assertOk();
    $this->actingAs($petugas)->get('/penerbitan/'.$penerbitan->id)->assertOk();
    $this->actingAs($petugas)->get('/penerbitan/print-list')->assertOk();
    $this->actingAs($petugas)->get('/penerbitan/export-pdf')->assertOk();
    $this->actingAs($petugas)->get('/penerbitan/create')->assertForbidden();
    $this->actingAs($petugas)->get('/penerbitan/'.$penerbitan->id.'/edit')->assertForbidden();
    $this->actingAs($petugas)->post('/penerbitan', [
        'nama' => 'Test WNA',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Penerbitan ITAS Baru',
        'tanggal' => '2026-10-14',
    ])->assertForbidden();
    $this->actingAs($petugas)->put('/penerbitan/'.$penerbitan->id, [
        'nama' => 'Update WNA',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Penerbitan ITAS Baru',
        'tanggal' => '2026-10-14',
    ])->assertForbidden();
    $this->actingAs($petugas)->delete('/penerbitan/'.$penerbitan->id)->assertForbidden();
});

test('admin can view penerbitan index with inertia props and filters', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Penerbitan::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/penerbitan');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/Index')
            ->has('penerbitan.data', 3)
            ->has('filters')
            ->has('layananOptions')
            ->has('kewarganegaraanOptions')
            ->has('kabupatenOptions')
            ->where('can.create', true)
            ->where('can.printList', true)
        );
});

test('admin can create a new penerbitan record with valid data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'nama' => 'Arthur Pendelton',
        'no_paspor' => 'GB98765432',
        'kewarganegaraan' => 'Inggris',
        'layanan' => 'Penerbitan ITAS Tenaga Ahli',
        'tanggal' => '2026-10-14',
        'kabupaten' => 'Wonosobo',
        'keterangan' => 'Penerbitan dokumen ITAS selesai dan telah diserahkan kepada sponsor.',
    ];

    $response = $this->actingAs($admin)->post('/penerbitan', $payload);

    $response->assertRedirect('/penerbitan')
        ->assertSessionHas('success', 'Data penerbitan berhasil ditambahkan.');

    $this->assertDatabaseHas('penerbitan', [
        'nama' => 'Arthur Pendelton',
        'no_paspor' => 'GB98765432',
        'kewarganegaraan' => 'Inggris',
        'layanan' => 'Penerbitan ITAS Tenaga Ahli',
        'kabupaten' => 'Wonosobo',
    ]);

    $created = Penerbitan::where('nama', 'Arthur Pendelton')->firstOrFail();
    expect($created->tanggal->format('Y-m-d'))->toBe('2026-10-14')
        ->and($created->keterangan)->toBe('Penerbitan dokumen ITAS selesai dan telah diserahkan kepada sponsor.');
});

test('admin can create penerbitan with only required fields (nullable fields omitted)', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $payload = [
        'nama' => 'Chen Wei',
        'kewarganegaraan' => 'Tiongkok',
        'layanan' => 'Penerbitan ITAP',
        'tanggal' => '2026-10-14',
    ];

    $response = $this->actingAs($admin)->post('/penerbitan', $payload);

    $response->assertRedirect('/penerbitan')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('penerbitan', [
        'nama' => 'Chen Wei',
        'no_paspor' => null,
        'kewarganegaraan' => 'Tiongkok',
        'layanan' => 'Penerbitan ITAP',
        'kabupaten' => null,
        'keterangan' => null,
    ]);

    $created = Penerbitan::where('nama', 'Chen Wei')->firstOrFail();
    expect($created->tanggal->format('Y-m-d'))->toBe('2026-10-14');
});

test('validation fails when required fields are missing on penerbitan creation', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/penerbitan', []);

    $response->assertSessionHasErrors(['nama', 'kewarganegaraan', 'layanan', 'tanggal']);
});

test('validation fails when tanggal is not a valid date', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/penerbitan', [
        'nama' => 'Test User',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Penerbitan ITAS Baru',
        'tanggal' => 'bukan-format-tanggal',
    ]);

    $response->assertSessionHasErrors(['tanggal']);
});

test('validation fails when string fields exceed maximum database lengths', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/penerbitan', [
        'nama' => str_repeat('A', 181),
        'kewarganegaraan' => str_repeat('B', 101),
        'layanan' => str_repeat('C', 151),
        'no_paspor' => str_repeat('D', 81),
        'kabupaten' => str_repeat('E', 121),
        'tanggal' => '2026-10-14',
    ]);

    $response->assertSessionHasErrors(['nama', 'kewarganegaraan', 'layanan', 'no_paspor', 'kabupaten']);
});

test('admin can view specific penerbitan detail', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $penerbitan = Penerbitan::factory()->create([
        'nama' => 'Klaus Schmidt',
        'layanan' => 'Penerbitan ITAS Baru',
        'keterangan' => 'Dokumen asli telah diverifikasi oleh petugas.',
    ]);

    $response = $this->actingAs($admin)->get('/penerbitan/'.$penerbitan->id);

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/Show')
            ->where('penerbitan.id', $penerbitan->id)
            ->where('penerbitan.nama', 'Klaus Schmidt')
            ->where('penerbitan.layanan', 'Penerbitan ITAS Baru')
        );

    // Also supports JSON request for modals or API consumers
    $jsonResponse = $this->actingAs($admin)->getJson('/penerbitan/'.$penerbitan->id);
    $jsonResponse->assertOk()
        ->assertJsonPath('penerbitan.id', $penerbitan->id)
        ->assertJsonPath('penerbitan.nama', 'Klaus Schmidt');
});

test('admin can update an existing penerbitan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $penerbitan = Penerbitan::factory()->create([
        'nama' => 'Original Name',
        'layanan' => 'Penerbitan ITAS Baru',
        'kabupaten' => 'Wonosobo',
    ]);

    $updatePayload = [
        'nama' => 'Updated Penerbitan Name',
        'no_paspor' => 'PASNEW777',
        'kewarganegaraan' => 'Korea Selatan',
        'layanan' => 'Penerbitan ITAP Tetap',
        'tanggal' => '2026-10-25',
        'kabupaten' => 'Purbalingga',
        'keterangan' => 'Penerbitan diperbarui sesuai paspor baru.',
    ];

    $response = $this->actingAs($admin)->put('/penerbitan/'.$penerbitan->id, $updatePayload);

    $response->assertRedirect('/penerbitan')
        ->assertSessionHas('success', 'Data penerbitan berhasil diperbarui.');

    $this->assertDatabaseHas('penerbitan', [
        'id' => $penerbitan->id,
        'nama' => 'Updated Penerbitan Name',
        'no_paspor' => 'PASNEW777',
        'kewarganegaraan' => 'Korea Selatan',
        'layanan' => 'Penerbitan ITAP Tetap',
        'kabupaten' => 'Purbalingga',
    ]);

    expect($penerbitan->fresh()->tanggal->format('Y-m-d'))->toBe('2026-10-25')
        ->and($penerbitan->fresh()->keterangan)->toBe('Penerbitan diperbarui sesuai paspor baru.');
});

test('admin can delete a penerbitan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $penerbitan = Penerbitan::factory()->create();

    $response = $this->actingAs($admin)->delete('/penerbitan/'.$penerbitan->id);

    $response->assertRedirect('/penerbitan')
        ->assertSessionHas('success', 'Data penerbitan berhasil dihapus.');

    $this->assertDatabaseMissing('penerbitan', [
        'id' => $penerbitan->id,
    ]);
});

test('admin can search penerbitan by nama, no_paspor, layanan, kewarganegaraan, kabupaten, and keterangan', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Penerbitan::factory()->create([
        'nama' => 'Target Alpha',
        'no_paspor' => 'ALPHA999',
        'layanan' => 'Penerbitan ITAS',
        'kewarganegaraan' => 'Kanada',
        'kabupaten' => 'Wonosobo',
        'keterangan' => 'Special VIP applicant',
    ]);

    Penerbitan::factory()->create([
        'nama' => 'Target Beta',
        'no_paspor' => 'BETA888',
        'layanan' => 'Penerbitan ITAP',
        'kewarganegaraan' => 'Australia',
        'kabupaten' => 'Kebumen',
        'keterangan' => 'Regular tourist worker',
    ]);

    // Search by nama
    $response = $this->actingAs($admin)->get('/penerbitan?search=Alpha');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Target Alpha')
        );

    // Search by no_paspor
    $response = $this->actingAs($admin)->get('/penerbitan?search=BETA888');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Target Beta')
        );

    // Search by layanan
    $response = $this->actingAs($admin)->get('/penerbitan?search=Penerbitan ITAP');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Target Beta')
        );

    // Search by kabupaten
    $response = $this->actingAs($admin)->get('/penerbitan?search=Kebumen');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Target Beta')
        );

    // Search by keterangan
    $response = $this->actingAs($admin)->get('/penerbitan?search=VIP applicant');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Target Alpha')
        );
});

test('admin can filter penerbitan by layanan, kewarganegaraan, and kabupaten', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Penerbitan::factory()->create([
        'nama' => 'Record One',
        'layanan' => 'Layanan Alpha',
        'kewarganegaraan' => 'Prancis',
        'kabupaten' => 'Wonosobo',
    ]);

    Penerbitan::factory()->create([
        'nama' => 'Record Two',
        'layanan' => 'Layanan Beta',
        'kewarganegaraan' => 'Jerman',
        'kabupaten' => 'Banjarnegara',
    ]);

    $response = $this->actingAs($admin)->get('/penerbitan?layanan=Layanan Alpha');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Record One')
        );

    $response = $this->actingAs($admin)->get('/penerbitan?kewarganegaraan=Jerman');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Record Two')
        );

    $response = $this->actingAs($admin)->get('/penerbitan?kabupaten=Wonosobo');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('penerbitan.data', 1)
            ->where('penerbitan.data.0.nama', 'Record One')
        );
});

test('admin can sort and paginate penerbitan records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Penerbitan::factory()->create(['nama' => 'Aaron Adams', 'tanggal' => '2026-01-01']);
    Penerbitan::factory()->create(['nama' => 'Zack Zimmerman', 'tanggal' => '2026-02-01']);

    // Sort by nama asc
    $response = $this->actingAs($admin)->get('/penerbitan?sort_by=nama&sort_order=asc');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('penerbitan.data.0.nama', 'Aaron Adams')
            ->where('penerbitan.data.1.nama', 'Zack Zimmerman')
        );

    // Sort by nama desc
    $response = $this->actingAs($admin)->get('/penerbitan?sort_by=nama&sort_order=desc');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('penerbitan.data.0.nama', 'Zack Zimmerman')
            ->where('penerbitan.data.1.nama', 'Aaron Adams')
        );

    // Pagination
    Penerbitan::factory()->count(15)->create();
    $pageResponse = $this->actingAs($admin)->get('/penerbitan?per_page=10');
    $pageResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('penerbitan.per_page', 10)
            ->where('penerbitan.total', 17)
            ->has('penerbitan.data', 10)
        );
});

test('invalid sort or filter parameters are safely validated on penerbitan', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get('/penerbitan?sort_by=malicious_col&sort_order=invalidsort&per_page=9999');

    // Validation fails safely and redirects with session errors
    $response->assertSessionHasErrors(['sort_by', 'sort_order', 'per_page']);
});

test('returns 404 for non-existent penerbitan record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/penerbitan/999999')->assertNotFound();
    $this->actingAs($admin)->put('/penerbitan/999999', [
        'nama' => 'Non Existent',
        'kewarganegaraan' => 'Jepang',
        'layanan' => 'Penerbitan ITAS Baru',
        'tanggal' => '2026-10-14',
    ])->assertNotFound();
    $this->actingAs($admin)->delete('/penerbitan/999999')->assertNotFound();
});

test('admin can access printable penerbitan report page with filtered records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Penerbitan::factory()->create([
        'nama' => 'Printable WNA',
        'layanan' => 'Penerbitan ITAS Baru',
    ]);
    Penerbitan::factory()->create([
        'nama' => 'Excluded WNA',
        'layanan' => 'Penerbitan ITAP Tetap',
    ]);

    $response = $this->actingAs($admin)->get('/penerbitan/print-list?layanan=Penerbitan ITAS Baru');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/PrintList')
            ->has('penerbitanList', 1)
            ->where('penerbitanList.0.nama', 'Printable WNA')
            ->has('filters')
            ->has('printedAt')
        );

    // export-pdf alias
    $aliasResponse = $this->actingAs($admin)->get('/penerbitan/export-pdf?layanan=Penerbitan ITAS Baru');
    $aliasResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/PrintList')
            ->has('penerbitanList', 1)
        );
});

test('admin can access printable report page when no records exist', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get('/penerbitan/print-list');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/PrintList')
            ->has('penerbitanList', 0)
            ->has('printedAt')
        );
});

test('admin can update nullable fields back to null', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $penerbitan = Penerbitan::factory()->create([
        'no_paspor' => 'PASSPORT123',
        'kabupaten' => 'Wonosobo',
        'keterangan' => 'Old notes',
    ]);

    $response = $this->actingAs($admin)->put('/penerbitan/'.$penerbitan->id, [
        'nama' => $penerbitan->nama,
        'kewarganegaraan' => $penerbitan->kewarganegaraan,
        'layanan' => $penerbitan->layanan,
        'tanggal' => '2026-10-14',
        'no_paspor' => null,
        'kabupaten' => null,
        'keterangan' => null,
    ]);

    $response->assertRedirect('/penerbitan')
        ->assertSessionHas('success');

    $fresh = $penerbitan->fresh();
    expect($fresh->no_paspor)->toBeNull()
        ->and($fresh->kabupaten)->toBeNull()
        ->and($fresh->keterangan)->toBeNull();
});
