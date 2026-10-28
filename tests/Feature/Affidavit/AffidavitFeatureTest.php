<?php

use App\Enums\Gender;
use App\Enums\UserRole;
use App\Models\Affidavit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing affidavit endpoints', function () {
    $affidavit = Affidavit::factory()->create();

    $this->get('/affidavit')->assertRedirect('/login');
    $this->get('/affidavit/'.$affidavit->id)->assertRedirect('/login');
    $this->get('/affidavit/'.$affidavit->id.'/photo')->assertRedirect('/login');
    $this->get('/affidavit/export')->assertRedirect('/login');
    $this->get('/affidavit/print-list')->assertRedirect('/login');
    $this->get('/affidavit/export-pdf')->assertRedirect('/login');
    $this->get('/affidavit/create')->assertRedirect('/login');
    $this->get('/affidavit/'.$affidavit->id.'/edit')->assertRedirect('/login');
    $this->post('/affidavit', [])->assertRedirect('/login');
    $this->put('/affidavit/'.$affidavit->id, [])->assertRedirect('/login');
    $this->delete('/affidavit/'.$affidavit->id)->assertRedirect('/login');
});

test('admin can view affidavit index with inertia props and filters', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Affidavit::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/affidavit');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/Index')
            ->has('affidavit.data', 3)
            ->has('filters')
            ->has('kewarganegaraanOptions')
            ->has('genderOptions')
            ->where('can.create', true)
            ->where('can.printList', true)
            ->where('can.export', true)
        );
});

test('petugas can view affidavit index with read-only permissions', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Affidavit::factory()->count(2)->create();

    $response = $this->actingAs($petugas)->get('/affidavit');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/Index')
            ->has('affidavit.data', 2)
            ->where('can.create', false)
            ->where('can.printList', true)
            ->where('can.export', false)
        );
});

test('admin can create affidavit record with photo upload and db transaction', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $photo = UploadedFile::fake()->image('affidavit_subjek.jpg', 300, 400)->size(500);

    $payload = [
        'no_register' => 'REG-AFF-10293',
        'no_paspor' => 'PASP-AFF-7788',
        'nama' => 'Samantha Smith',
        'tempat_lahir' => 'Sydney',
        'tanggal_lahir' => '2016-04-10',
        'jenis_kelamin' => Gender::Perempuan->value,
        'kewarganegaraan' => 'Indonesia / Australia',
        'masa_berlaku' => '2028-10-15',
        'nama_ayah' => 'David Smith',
        'kewarganegaraan_ayah' => 'Australia',
        'nama_ibu' => 'Dewi Lestari',
        'kewarganegaraan_ibu' => 'Indonesia',
        'nomor_telepon' => '081298765432',
        'alamat' => 'Jl. Kemang Raya No. 12, Jakarta Selatan',
        'foto' => $photo,
    ];

    $response = $this->actingAs($admin)->post('/affidavit', $payload);

    $response->assertRedirect('/affidavit');
    $response->assertSessionHas('success', 'Data Pendaftaran Affidavit berhasil ditambahkan.');

    $this->assertDatabaseHas('affidavit', [
        'no_register' => 'REG-AFF-10293',
        'nama' => 'Samantha Smith',
        'kewarganegaraan' => 'Indonesia / Australia',
    ]);

    $created = Affidavit::where('no_register', 'REG-AFF-10293')->firstOrFail();
    expect($created->foto)->not->toBeNull()
        ->and($created->masa_berlaku?->format('Y-m-d'))->toBe('2028-10-15');
    Storage::disk('local')->assertExists((string) $created->foto);
});

test('petugas is forbidden from creating affidavit record via POST or GET /create', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($petugas)->get('/affidavit/create')
        ->assertForbidden();

    $this->actingAs($petugas)->post('/affidavit', [
        'no_register' => 'REG-AFF-FORBIDDEN',
        'no_paspor' => 'F12345678',
        'nama' => 'Unauthorized Subject',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Belanda',
    ])->assertForbidden();

    $this->assertDatabaseMissing('affidavit', ['no_register' => 'REG-AFF-FORBIDDEN']);
});

test('affidavit creation requires mandatory fields and unique no_register', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Affidavit::factory()->create(['no_register' => 'REG-AFF-EXISTING']);

    $response = $this->actingAs($admin)->post('/affidavit', [
        'no_register' => 'REG-AFF-EXISTING', // Duplicate
        'no_paspor' => '',
        'nama' => '',
        'jenis_kelamin' => '',
        'kewarganegaraan' => '',
    ]);

    $response->assertSessionHasErrors(['no_register', 'no_paspor', 'nama', 'jenis_kelamin', 'kewarganegaraan']);
});

test('affidavit handles null, valid, and invalid masa_berlaku properly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Creation with null/empty masa_berlaku is allowed
    $resNull = $this->actingAs($admin)->post('/affidavit', [
        'no_register' => 'REG-AFF-NULLDATE',
        'no_paspor' => 'PASP-NULL',
        'nama' => 'Child Null Expiration',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Jerman',
        'masa_berlaku' => null,
    ]);
    $resNull->assertRedirect('/affidavit');
    $this->assertDatabaseHas('affidavit', [
        'no_register' => 'REG-AFF-NULLDATE',
    ]);
    $nullRecord = Affidavit::where('no_register', 'REG-AFF-NULLDATE')->firstOrFail();
    expect($nullRecord->masa_berlaku)->toBeNull();

    // 2. Creation with valid date
    $resValid = $this->actingAs($admin)->post('/affidavit', [
        'no_register' => 'REG-AFF-VALIDDATE',
        'no_paspor' => 'PASP-VALID',
        'nama' => 'Child Valid Expiration',
        'jenis_kelamin' => Gender::Perempuan->value,
        'kewarganegaraan' => 'Indonesia / Prancis',
        'masa_berlaku' => '2029-05-20',
    ]);
    $resValid->assertRedirect('/affidavit');
    $this->assertDatabaseHas('affidavit', [
        'no_register' => 'REG-AFF-VALIDDATE',
    ]);
    $validRecord = Affidavit::where('no_register', 'REG-AFF-VALIDDATE')->firstOrFail();
    expect($validRecord->masa_berlaku?->format('Y-m-d'))->toBe('2029-05-20');

    // 3. Creation with invalid date format fails validation
    $resInvalid = $this->actingAs($admin)->post('/affidavit', [
        'no_register' => 'REG-AFF-INVALID',
        'no_paspor' => 'PASP-INV',
        'nama' => 'Child Invalid Date',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Italia',
        'masa_berlaku' => 'bukan-tanggal-valid',
    ]);
    $resInvalid->assertSessionHasErrors(['masa_berlaku']);
    $this->assertDatabaseMissing('affidavit', ['no_register' => 'REG-AFF-INVALID']);
});

test('admin can update affidavit record, change masa_berlaku, and replace photo safely', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $oldFile = UploadedFile::fake()->image('old_photo.jpg', 200, 200);
    $oldPath = Storage::disk('local')->putFile('affidavit/photos', $oldFile);

    $affidavit = Affidavit::factory()->create([
        'no_register' => 'REG-AFF-ORIGINAL',
        'nama' => 'Original Subject',
        'masa_berlaku' => '2027-01-01',
        'foto' => $oldPath,
    ]);

    $newFile = UploadedFile::fake()->image('new_photo.jpg', 300, 300);

    $response = $this->actingAs($admin)->put('/affidavit/'.$affidavit->id, [
        'no_register' => 'REG-AFF-ORIGINAL', // same register is allowed for self
        'no_paspor' => 'PASP-UPDATED',
        'nama' => 'Updated Subject Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Selandia Baru',
        'masa_berlaku' => '2030-08-17',
        'foto' => $newFile,
    ]);

    $response->assertRedirect('/affidavit');
    $response->assertSessionHas('success', 'Data Pendaftaran Affidavit berhasil diperbarui.');

    $affidavit->refresh();
    expect($affidavit->nama)->toBe('Updated Subject Name')
        ->and($affidavit->no_paspor)->toBe('PASP-UPDATED')
        ->and($affidavit->masa_berlaku?->format('Y-m-d'))->toBe('2030-08-17')
        ->and($affidavit->foto)->not->toBe($oldPath);

    // Old photo must be removed, new photo must exist
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists((string) $affidavit->foto);
});

test('admin can remove photo on affidavit update without providing new photo', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('photo_to_remove.jpg', 200, 200);
    $path = Storage::disk('local')->putFile('affidavit/photos', $file);

    $affidavit = Affidavit::factory()->create([
        'foto' => $path,
    ]);

    $response = $this->actingAs($admin)->put('/affidavit/'.$affidavit->id, [
        'no_register' => $affidavit->no_register,
        'no_paspor' => $affidavit->no_paspor,
        'nama' => $affidavit->nama,
        'jenis_kelamin' => $affidavit->jenis_kelamin->value,
        'kewarganegaraan' => $affidavit->kewarganegaraan,
        'remove_foto' => true,
    ]);

    $response->assertRedirect('/affidavit');
    $affidavit->refresh();

    expect($affidavit->foto)->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

test('petugas is forbidden from updating affidavit record via PUT or GET /edit', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $affidavit = Affidavit::factory()->create(['nama' => 'Untouchable Affidavit']);

    $this->actingAs($petugas)->get('/affidavit/'.$affidavit->id.'/edit')
        ->assertForbidden();

    $this->actingAs($petugas)->put('/affidavit/'.$affidavit->id, [
        'no_register' => $affidavit->no_register,
        'no_paspor' => $affidavit->no_paspor,
        'nama' => 'Hacked Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => $affidavit->kewarganegaraan,
    ])->assertForbidden();

    $affidavit->refresh();
    expect($affidavit->nama)->toBe('Untouchable Affidavit');
});

test('admin can delete affidavit record and photo is cleaned up', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('to_delete.jpg', 200, 200);
    $path = Storage::disk('local')->putFile('affidavit/photos', $file);

    $affidavit = Affidavit::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->delete('/affidavit/'.$affidavit->id);

    $response->assertRedirect('/affidavit');
    $response->assertSessionHas('success', 'Data Pendaftaran Affidavit berhasil dihapus.');

    $this->assertDatabaseMissing('affidavit', ['id' => $affidavit->id]);
    Storage::disk('local')->assertMissing($path);
});

test('petugas is forbidden from deleting affidavit record', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $affidavit = Affidavit::factory()->create();

    $this->actingAs($petugas)->delete('/affidavit/'.$affidavit->id)
        ->assertForbidden();

    $this->assertDatabaseHas('affidavit', ['id' => $affidavit->id]);
});

test('both admin and petugas can view affidavit detail page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $affidavit = Affidavit::factory()->create(['nama' => 'Liam Tanaka']);

    $this->actingAs($admin)->get('/affidavit/'.$affidavit->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/Show')
            ->where('affidavit.nama', 'Liam Tanaka')
        );

    $this->actingAs($petugas)->get('/affidavit/'.$affidavit->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/Show')
            ->where('affidavit.nama', 'Liam Tanaka')
        );

    // 404 on missing record
    $this->actingAs($admin)->get('/affidavit/999999')->assertNotFound();
});

test('admin and petugas can stream protected affidavit photo, guest is denied, 404 on missing file or traversal', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $file = UploadedFile::fake()->image('subject_id.jpg', 300, 400);
    $path = Storage::disk('local')->putFile('affidavit/photos', $file);

    $affidavit = Affidavit::factory()->create(['foto' => $path]);

    // Guest is redirected
    $this->get('/affidavit/'.$affidavit->id.'/photo')->assertRedirect('/login');

    // Admin can stream photo
    $resAdmin = $this->actingAs($admin)->get('/affidavit/'.$affidavit->id.'/photo');
    $resAdmin->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($resAdmin->headers->get('Content-Disposition'))->toContain('inline');

    // Petugas can stream photo
    $resPetugas = $this->actingAs($petugas)->get('/affidavit/'.$affidavit->id.'/photo');
    $resPetugas->assertOk();

    // 404 if record has no photo
    $noPhoto = Affidavit::factory()->create(['foto' => null]);
    $this->actingAs($admin)->get('/affidavit/'.$noPhoto->id.'/photo')->assertNotFound();

    // 404 on directory traversal attempt
    $traversal = Affidavit::factory()->create(['foto' => 'affidavit/photos/../../etc/passwd']);
    $this->actingAs($admin)->get('/affidavit/'.$traversal->id.'/photo')->assertNotFound();
});

test('admin and petugas can access affidavit print list', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Affidavit::factory()->count(3)->create();

    $this->actingAs($admin)->get('/affidavit/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/PrintList')
            ->has('affidavitList', 3)
            ->has('printedAt')
        );

    // Backwards-compatible alias
    $this->actingAs($admin)->get('/affidavit/export-pdf')->assertOk();

    $this->actingAs($petugas)->get('/affidavit/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/PrintList')
            ->has('affidavitList', 3)
            ->has('printedAt')
        );
});

test('admin can export affidavit csv while petugas is forbidden', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Affidavit::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get('/affidavit/export');
    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('attachment; filename=data-affidavit-');

    $this->actingAs($petugas)->get('/affidavit/export')
        ->assertForbidden();
});

test('affidavit csv export respects filters and mitigates formula injection', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $match = Affidavit::factory()->create([
        'nama' => " =cmd|' /C calc'!A0",
        'kewarganegaraan' => 'Indonesia / Jerman',
        'jenis_kelamin' => Gender::LakiLaki,
        'nama_ayah' => '   @SUM(1+1)',
        'no_paspor' => "\t-2+3+calc",
        'masa_berlaku' => '2029-01-01',
    ]);

    $nonMatch = Affidavit::factory()->create([
        'nama' => 'Normal Subject',
        'kewarganegaraan' => 'Indonesia / Spanyol',
        'jenis_kelamin' => Gender::Perempuan,
    ]);

    $response = $this->actingAs($admin)->get('/affidavit/export?search=cmd&jenis_kelamin=L');
    $response->assertOk();

    $content = $response->streamedContent();

    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Nomor Register')
        ->and($content)->toContain('Masa Berlaku')
        ->and($content)->toContain("' =cmd|' /C calc'!A0")
        ->and($content)->toContain("'   @SUM(1+1)")
        ->and($content)->toContain("'\t-2+3+calc")
        ->and($content)->toContain('2029-01-01')
        ->and($content)->not->toContain('Normal Subject');
});

test('affidavit search, filter, sorting, and pagination work accurately', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Affidavit::factory()->create([
        'nama' => 'Kenzo Watanabe',
        'kewarganegaraan' => 'Indonesia / Jepang',
        'jenis_kelamin' => Gender::LakiLaki,
        'masa_berlaku' => '2027-05-10',
    ]);
    Affidavit::factory()->create([
        'nama' => 'Aoi Watanabe',
        'kewarganegaraan' => 'Indonesia / Jepang',
        'jenis_kelamin' => Gender::Perempuan,
        'masa_berlaku' => '2028-11-20',
    ]);
    Affidavit::factory()->create([
        'nama' => 'Hans Gruber',
        'kewarganegaraan' => 'Indonesia / Jerman',
        'jenis_kelamin' => Gender::LakiLaki,
        'masa_berlaku' => null,
    ]);

    // Search by Watanabe and filter Perempuan
    $res = $this->actingAs($admin)->get('/affidavit?search=Watanabe&jenis_kelamin=P');
    $res->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('affidavit.data', 1)
            ->where('affidavit.data.0.nama', 'Aoi Watanabe')
        );

    // Sort by masa_berlaku desc
    $resSort = $this->actingAs($admin)->get('/affidavit?sort_by=masa_berlaku&sort_order=desc');
    $resSort->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('affidavit.data', 3)
            ->where('affidavit.data.0.nama', 'Aoi Watanabe')
        );
});

test('failed database creation transaction cleans up newly uploaded photo without leaving orphan file', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $photo = UploadedFile::fake()->image('rollback_subjek.jpg', 300, 400)->size(500);

    // Throw exception inside model saving event during transaction
    Affidavit::saving(function () {
        throw new Exception('Simulated database deadlock or query failure');
    });

    try {
        $this->actingAs($admin)->post('/affidavit', [
            'no_register' => 'REG-AFF-ROLLBACK',
            'no_paspor' => 'PASP-ROLLBACK',
            'nama' => 'Rollback Subject',
            'jenis_kelamin' => Gender::LakiLaki->value,
            'kewarganegaraan' => 'Indonesia / Italia',
            'foto' => $photo,
        ]);
    } catch (Throwable $e) {
        expect($e->getMessage())->toBe('Simulated database deadlock or query failure');
    }

    // Ensure no orphan file left in local storage disk
    expect(Storage::disk('local')->allFiles('affidavit/photos'))->toBeEmpty();
});
