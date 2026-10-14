<?php

use App\Enums\Gender;
use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing abg endpoints', function () {
    $abg = Abg::factory()->create();

    $this->get('/abg')->assertRedirect('/login');
    $this->get('/abg/'.$abg->id)->assertRedirect('/login');
    $this->get('/abg/'.$abg->id.'/photo')->assertRedirect('/login');
    $this->get('/abg/export')->assertRedirect('/login');
    $this->get('/abg/print-list')->assertRedirect('/login');
    $this->get('/abg/export-pdf')->assertRedirect('/login');
    $this->get('/abg/create')->assertRedirect('/login');
    $this->get('/abg/'.$abg->id.'/edit')->assertRedirect('/login');
    $this->post('/abg', [])->assertRedirect('/login');
    $this->put('/abg/'.$abg->id, [])->assertRedirect('/login');
    $this->delete('/abg/'.$abg->id)->assertRedirect('/login');
});

test('admin can view abg index with inertia props and filters', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Abg::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/abg');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/Index')
            ->has('abg.data', 3)
            ->has('filters')
            ->has('kewarganegaraanOptions')
            ->has('genderOptions')
            ->where('can.create', true)
            ->where('can.printList', true)
            ->where('can.export', true)
        );
});

test('petugas can view abg index with read-only permissions', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Abg::factory()->count(2)->create();

    $response = $this->actingAs($petugas)->get('/abg');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/Index')
            ->has('abg.data', 2)
            ->where('can.create', false)
            ->where('can.printList', false)
            ->where('can.export', false)
        );
});

test('admin can create abg record with photo upload and db transaction', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $photo = UploadedFile::fake()->image('anak.jpg', 300, 400)->size(500);

    $payload = [
        'no_register' => 'REG-ABG-99887',
        'no_paspor' => 'X98765432',
        'nama' => 'Maya Tanaka',
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '2015-06-12',
        'jenis_kelamin' => Gender::Perempuan->value,
        'kewarganegaraan' => 'Indonesia / Jepang',
        'nama_ayah' => 'Kenji Tanaka',
        'kewarganegaraan_ayah' => 'Jepang',
        'nama_ibu' => 'Siti Nurhaliza',
        'kewarganegaraan_ibu' => 'Indonesia',
        'nomor_telepon' => '081234567890',
        'alamat' => 'Jl. Sudirman No. 45, Jakarta',
        'foto' => $photo,
    ];

    $response = $this->actingAs($admin)->post('/abg', $payload);

    $response->assertRedirect('/abg');
    $response->assertSessionHas('success', 'Data Pendaftaran ABG berhasil ditambahkan.');

    $this->assertDatabaseHas('abg', [
        'no_register' => 'REG-ABG-99887',
        'nama' => 'Maya Tanaka',
        'kewarganegaraan' => 'Indonesia / Jepang',
    ]);

    $created = Abg::where('no_register', 'REG-ABG-99887')->firstOrFail();
    expect($created->foto)->not->toBeNull();
    Storage::disk('local')->assertExists((string) $created->foto);
});

test('petugas is forbidden from creating abg record via POST or GET /create', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($petugas)->get('/abg/create')
        ->assertForbidden();

    $this->actingAs($petugas)->post('/abg', [
        'no_register' => 'REG-ABG-FORBIDDEN',
        'no_paspor' => 'F12345678',
        'nama' => 'Unauthorized Child',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Jerman',
    ])->assertForbidden();

    $this->assertDatabaseMissing('abg', ['no_register' => 'REG-ABG-FORBIDDEN']);
});

test('abg creation requires mandatory fields and unique no_register', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Abg::factory()->create(['no_register' => 'REG-ABG-EXISTING']);

    $response = $this->actingAs($admin)->post('/abg', [
        'no_register' => 'REG-ABG-EXISTING', // Duplicate
        'no_paspor' => '',
        'nama' => '',
        'jenis_kelamin' => '',
        'kewarganegaraan' => '',
    ]);

    $response->assertSessionHasErrors(['no_register', 'no_paspor', 'nama', 'jenis_kelamin', 'kewarganegaraan']);
});

test('admin can update abg record and replace photo safely', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $oldFile = UploadedFile::fake()->image('old_photo.jpg', 200, 200);
    $oldPath = Storage::disk('local')->putFile('abg/photos', $oldFile);

    $abg = Abg::factory()->create([
        'no_register' => 'REG-ABG-ORIGINAL',
        'nama' => 'Original Child',
        'foto' => $oldPath,
    ]);

    $newFile = UploadedFile::fake()->image('new_photo.jpg', 300, 300);

    $response = $this->actingAs($admin)->put('/abg/'.$abg->id, [
        'no_register' => 'REG-ABG-ORIGINAL', // same register is allowed for self
        'no_paspor' => 'PASP-UPDATED',
        'nama' => 'Updated Child Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Australia',
        'foto' => $newFile,
    ]);

    $response->assertRedirect('/abg');
    $response->assertSessionHas('success', 'Data Pendaftaran ABG berhasil diperbarui.');

    $abg->refresh();
    expect($abg->nama)->toBe('Updated Child Name')
        ->and($abg->no_paspor)->toBe('PASP-UPDATED')
        ->and($abg->foto)->not->toBe($oldPath);

    // Old photo should be removed, new photo must exist
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists((string) $abg->foto);
});

test('petugas is forbidden from updating abg record via PUT or GET /edit', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $abg = Abg::factory()->create(['nama' => 'Untouchable Child']);

    $this->actingAs($petugas)->get('/abg/'.$abg->id.'/edit')
        ->assertForbidden();

    $this->actingAs($petugas)->put('/abg/'.$abg->id, [
        'no_register' => $abg->no_register,
        'no_paspor' => $abg->no_paspor,
        'nama' => 'Hacked Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => $abg->kewarganegaraan,
    ])->assertForbidden();

    $abg->refresh();
    expect($abg->nama)->toBe('Untouchable Child');
});

test('admin can delete abg record and photo is cleaned up', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('to_delete.jpg', 200, 200);
    $path = Storage::disk('local')->putFile('abg/photos', $file);

    $abg = Abg::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->delete('/abg/'.$abg->id);

    $response->assertRedirect('/abg');
    $response->assertSessionHas('success', 'Data Pendaftaran ABG berhasil dihapus.');

    $this->assertDatabaseMissing('abg', ['id' => $abg->id]);
    Storage::disk('local')->assertMissing($path);
});

test('petugas is forbidden from deleting abg record', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $abg = Abg::factory()->create();

    $this->actingAs($petugas)->delete('/abg/'.$abg->id)
        ->assertForbidden();

    $this->assertDatabaseHas('abg', ['id' => $abg->id]);
});

test('both admin and petugas can view abg detail page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $abg = Abg::factory()->create(['nama' => 'Budi Tanaka']);

    $this->actingAs($admin)->get('/abg/'.$abg->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/Show')
            ->where('abg.nama', 'Budi Tanaka')
        );

    $this->actingAs($petugas)->get('/abg/'.$abg->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/Show')
            ->where('abg.nama', 'Budi Tanaka')
        );

    // 404 on missing record
    $this->actingAs($admin)->get('/abg/999999')->assertNotFound();
});

test('admin and petugas can stream protected abg photo, guest is denied, 404 on missing file', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $file = UploadedFile::fake()->image('child_id.jpg', 300, 400);
    $path = Storage::disk('local')->putFile('abg/photos', $file);

    $abg = Abg::factory()->create(['foto' => $path]);

    // Guest is redirected
    $this->get('/abg/'.$abg->id.'/photo')->assertRedirect('/login');

    // Admin can stream photo
    $resAdmin = $this->actingAs($admin)->get('/abg/'.$abg->id.'/photo');
    $resAdmin->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($resAdmin->headers->get('Content-Disposition'))->toContain('inline');

    // Petugas can stream photo
    $resPetugas = $this->actingAs($petugas)->get('/abg/'.$abg->id.'/photo');
    $resPetugas->assertOk();

    // 404 if record has no photo
    $noPhotoAbg = Abg::factory()->create(['foto' => null]);
    $this->actingAs($admin)->get('/abg/'.$noPhotoAbg->id.'/photo')->assertNotFound();

    // 404 on directory traversal attempt
    $traversalAbg = Abg::factory()->create(['foto' => 'abg/photos/../../etc/passwd']);
    $this->actingAs($admin)->get('/abg/'.$traversalAbg->id.'/photo')->assertNotFound();
});

test('admin can access abg print list while petugas is forbidden', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Abg::factory()->count(3)->create();

    $this->actingAs($admin)->get('/abg/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/PrintList')
            ->has('abgList', 3)
            ->has('printedAt')
        );

    // Backwards-compatible alias
    $this->actingAs($admin)->get('/abg/export-pdf')->assertOk();

    $this->actingAs($petugas)->get('/abg/print-list')
        ->assertForbidden();
});

test('admin can export abg csv while petugas is forbidden', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Abg::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get('/abg/export');
    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('attachment; filename=data-abg-');

    $this->actingAs($petugas)->get('/abg/export')
        ->assertForbidden();
});

test('abg csv export respects filters and mitigates formula injection', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $match = Abg::factory()->create([
        'nama' => " =cmd|' /C calc'!A0",
        'kewarganegaraan' => 'Indonesia / Jerman',
        'jenis_kelamin' => Gender::LakiLaki,
        'nama_ayah' => '   @SUM(1+1)',
        'no_paspor' => "\t-2+3+calc",
    ]);

    $nonMatch = Abg::factory()->create([
        'nama' => 'Normal Child',
        'kewarganegaraan' => 'Indonesia / Perancis',
        'jenis_kelamin' => Gender::Perempuan,
    ]);

    $response = $this->actingAs($admin)->get('/abg/export?search=cmd&jenis_kelamin=L');
    $response->assertOk();

    $content = $response->streamedContent();

    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Nomor Register')
        ->and($content)->toContain('Nama Lengkap')
        ->and($content)->toContain("' =cmd|' /C calc'!A0")
        ->and($content)->toContain("'   @SUM(1+1)")
        ->and($content)->toContain("'\t-2+3+calc")
        ->and($content)->not->toContain('Normal Child');
});

test('abg search, filter, and pagination work accurately', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Abg::factory()->create([
        'nama' => 'Alisa Tanaka',
        'kewarganegaraan' => 'Indonesia / Jepang',
        'jenis_kelamin' => Gender::Perempuan,
    ]);
    Abg::factory()->create([
        'nama' => 'Bento Tanaka',
        'kewarganegaraan' => 'Indonesia / Jepang',
        'jenis_kelamin' => Gender::LakiLaki,
    ]);
    Abg::factory()->create([
        'nama' => 'Charles Schmidt',
        'kewarganegaraan' => 'Indonesia / Jerman',
        'jenis_kelamin' => Gender::LakiLaki,
    ]);

    // Search by Tanaka and filter Perempuan
    $res = $this->actingAs($admin)->get('/abg?search=Tanaka&jenis_kelamin=P');
    $res->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('abg.data', 1)
            ->where('abg.data.0.nama', 'Alisa Tanaka')
        );
});
