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

test('abg photo upload validates file extension and size according to DocumentStorageService', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Text file disguised as photo
    $invalidMimeFile = UploadedFile::fake()->create('script.php', 100, 'application/x-php');
    $response = $this->actingAs($admin)->post('/abg', [
        'no_register' => 'REG-INVALID-MIME',
        'no_paspor' => 'PAS11',
        'nama' => 'Mime Test Child',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Belanda',
        'foto' => $invalidMimeFile,
    ]);
    $response->assertSessionHasErrors(['foto']);

    // 2. Oversized image (> 2048 KB limit)
    $oversizedFile = UploadedFile::fake()->image('huge.jpg', 2000, 2000)->size(3072);
    $responseOversized = $this->actingAs($admin)->post('/abg', [
        'no_register' => 'REG-OVERSIZED',
        'no_paspor' => 'PAS22',
        'nama' => 'Oversized Test Child',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Indonesia / Belanda',
        'foto' => $oversizedFile,
    ]);
    $responseOversized->assertSessionHasErrors(['foto']);
});

test('database failure during abg store cleans up uploaded photo without leaving orphaned files', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $photo = UploadedFile::fake()->image('test_abg.jpg', 300, 400);

    // Simulate DB failure on saving
    Abg::saving(function () {
        throw new RuntimeException('Simulated database failure during ABG store');
    });

    try {
        $this->actingAs($admin)->post('/abg', [
            'no_register' => 'REG-ABG-FAIL-STORE',
            'no_paspor' => 'PAS-FAIL',
            'nama' => 'Fail Store Child',
            'jenis_kelamin' => Gender::LakiLaki->value,
            'kewarganegaraan' => 'Indonesia / Swiss',
            'foto' => $photo,
        ]);
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Simulated database failure during ABG store');
    }

    // Ensure photo was cleaned up and not left orphaned in storage
    $files = Storage::disk('local')->allFiles('abg/photos');
    expect($files)->toBeEmpty();
    $this->assertDatabaseMissing('abg', ['no_register' => 'REG-ABG-FAIL-STORE']);
});

test('database failure during abg update cleans up new photo and preserves active old photo', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $oldFile = UploadedFile::fake()->image('old_abg.jpg', 200, 200);
    $oldPath = Storage::disk('local')->putFile('abg/photos', $oldFile);
    $abg = Abg::factory()->create(['foto' => $oldPath]);

    $newFile = UploadedFile::fake()->image('new_abg.jpg', 200, 200);

    // Simulate DB failure on saving
    Abg::saving(function () {
        throw new RuntimeException('Simulated database failure during ABG update');
    });

    try {
        $this->actingAs($admin)->put('/abg/'.$abg->id, [
            'no_register' => $abg->no_register,
            'no_paspor' => $abg->no_paspor,
            'nama' => 'Attempted New Name',
            'jenis_kelamin' => $abg->jenis_kelamin->value,
            'kewarganegaraan' => $abg->kewarganegaraan,
            'foto' => $newFile,
        ]);
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Simulated database failure during ABG update');
    }

    // Old photo MUST remain intact, and no orphaned new photo should exist
    Storage::disk('local')->assertExists($oldPath);
    $allFiles = Storage::disk('local')->allFiles('abg/photos');
    expect($allFiles)->toEqual([$oldPath]);
});

test('abg photo streaming validates mime types and returns security headers', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Valid image stream headers
    $validImage = UploadedFile::fake()->image('child_photo.png', 200, 200);
    $validPath = Storage::disk('local')->putFile('abg/photos', $validImage);
    $abg = Abg::factory()->create(['foto' => $validPath]);

    $response = $this->actingAs($admin)->get('/abg/'.$abg->id.'/photo');
    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Disposition', 'inline');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('no-cache')
        ->and($cacheControl)->toContain('must-revalidate')
        ->and($cacheControl)->not->toContain('public');

    // 2. Reject unsupported mime type (e.g. PDF file saved in abg/photos) with 415
    $pdfFilePath = 'abg/photos/unsupported.pdf';
    Storage::disk('local')->put($pdfFilePath, '%PDF-1.4 test content');
    $badMimeAbg = Abg::factory()->create(['foto' => $pdfFilePath]);

    $this->actingAs($admin)->get('/abg/'.$badMimeAbg->id.'/photo')
        ->assertStatus(415);

    // 3. Path outside abg/photos returns 404
    $outsideAbg = Abg::factory()->create(['foto' => 'wna/photos/other.jpg']);
    $this->actingAs($admin)->get('/abg/'.$outsideAbg->id.'/photo')
        ->assertNotFound();

    // 4. Null byte in photo path returns 404
    $nullByteAbg = Abg::factory()->create(['foto' => "abg/photos/test\0.jpg"]);
    $this->actingAs($admin)->get('/abg/'.$nullByteAbg->id.'/photo')
        ->assertNotFound();
});

test('abg update allows keeping same no_register but rejects duplicate from another record', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $abgA = Abg::factory()->create(['no_register' => 'REG-ABG-AAA']);
    $abgB = Abg::factory()->create(['no_register' => 'REG-ABG-BBB']);

    // 1. Keeping own no_register should succeed
    $responseOwn = $this->actingAs($admin)->put('/abg/'.$abgA->id, [
        'no_register' => 'REG-ABG-AAA',
        'no_paspor' => $abgA->no_paspor,
        'nama' => 'Updated Name A',
        'jenis_kelamin' => $abgA->jenis_kelamin->value,
        'kewarganegaraan' => $abgA->kewarganegaraan,
    ]);
    $responseOwn->assertSessionHasNoErrors();
    expect($abgA->fresh()->nama)->toBe('Updated Name A');

    // 2. Taking another record's no_register should fail validation
    $responseDuplicate = $this->actingAs($admin)->put('/abg/'.$abgA->id, [
        'no_register' => 'REG-ABG-BBB', // Collision with abgB
        'no_paspor' => $abgA->no_paspor,
        'nama' => 'Attempted Collision',
        'jenis_kelamin' => $abgA->jenis_kelamin->value,
        'kewarganegaraan' => $abgA->kewarganegaraan,
    ]);
    $responseDuplicate->assertSessionHasErrors(['no_register']);
});

test('abg creation and update permit nullable fields without error', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Store with all nullable fields omitted/null
    $response = $this->actingAs($admin)->post('/abg', [
        'no_register' => 'REG-ABG-NULLABLE-OK',
        'no_paspor' => 'PASP-MINIMAL',
        'nama' => 'Minimal Child',
        'jenis_kelamin' => Gender::Perempuan->value,
        'kewarganegaraan' => 'Indonesia / Inggris',
        'tempat_lahir' => null,
        'tanggal_lahir' => null,
        'nama_ayah' => null,
        'kewarganegaraan_ayah' => null,
        'nama_ibu' => null,
        'kewarganegaraan_ibu' => null,
        'nomor_telepon' => null,
        'alamat' => null,
        'foto' => null,
    ]);

    $response->assertRedirect('/abg');
    $this->assertDatabaseHas('abg', [
        'no_register' => 'REG-ABG-NULLABLE-OK',
        'tempat_lahir' => null,
        'tanggal_lahir' => null,
        'nama_ayah' => null,
        'foto' => null,
    ]);
});

test('abg csv export handles complex escaping including commas, double quotes, newlines, and legitimate numbers', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Abg::factory()->create([
        'no_register' => 'REG-ABG-COMPLEX',
        'nama' => 'Elena "Lena" Rostova, Jr.',
        'kewarganegaraan' => 'Indonesia / Rusia',
        'jenis_kelamin' => Gender::Perempuan,
        'alamat' => "Jl. Sudirman, Blok A No. 12\nRT 05/RW \"03\", Jakarta",
        'nomor_telepon' => '+62812345678', // Legitimate numeric string
        'tempat_lahir' => null, // Should export as placeholder '-'
        'nama_ayah' => ' =cmd|calc', // Leading space + formula trigger
        'nama_ibu' => "\x1F@SUM(1+1)", // Control char + formula trigger
    ]);

    $response = $this->actingAs($admin)->get('/abg/export?search=Elena');
    $response->assertOk();

    $content = $response->streamedContent();

    // Check UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();

    // Formula injection sanitization
    expect($content)->toContain("' =cmd|calc")
        ->and($content)->toContain("'\x1F@SUM(1+1)");

    // Legitimate numbers and placeholders preserved
    expect($content)->toContain('+62812345678');

    // RFC 4180 CSV escaping for quotes, commas, and newlines
    expect($content)->toContain('"Elena ""Lena"" Rostova, Jr."')
        ->and($content)->toContain('"Jl. Sudirman, Blok A No. 12')
        ->and($content)->toContain('RT 05/RW ""03"", Jakarta"');
});
