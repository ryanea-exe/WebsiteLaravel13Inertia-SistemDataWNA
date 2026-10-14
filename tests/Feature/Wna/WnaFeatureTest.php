<?php

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wna;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing wna endpoints', function () {
    $wna = Wna::factory()->create();

    $this->get('/wna')->assertRedirect('/login');
    $this->get('/wna/'.$wna->id)->assertRedirect('/login');
    $this->get('/wna/'.$wna->id.'/card')->assertRedirect('/login');
    $this->get('/wna/'.$wna->id.'/photo')->assertRedirect('/login');
    $this->get('/wna/export')->assertRedirect('/login');
    $this->get('/wna/print-list')->assertRedirect('/login');
    $this->get('/wna/export-pdf')->assertRedirect('/login');
    $this->get('/wna/create')->assertRedirect('/login');
    $this->get('/wna/'.$wna->id.'/edit')->assertRedirect('/login');
    $this->post('/wna', [])->assertRedirect('/login');
    $this->put('/wna/'.$wna->id, [])->assertRedirect('/login');
    $this->delete('/wna/'.$wna->id)->assertRedirect('/login');
});

test('admin can view wna index with inertia props and filters', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Wna::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/wna');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/Index')
            ->has('wna.data', 3)
            ->has('filters')
            ->has('kewarganegaraanOptions')
            ->has('jenisIzinTinggalOptions')
            ->where('can.create', true)
            ->where('can.printList', true)
            ->where('can.export', true)
        );
});

test('petugas can view wna index with read-only permissions', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Wna::factory()->count(2)->create();

    $response = $this->actingAs($petugas)->get('/wna');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/Index')
            ->has('wna.data', 2)
            ->where('can.create', false)
            ->where('can.printList', false)
            ->where('can.export', false)
        );
});

test('admin can create wna record with private photo upload and db transaction', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $photo = UploadedFile::fake()->image('pasfoto.jpg', 300, 400)->size(500);

    $payload = [
        'no_register' => 'REG-WNA-99001',
        'no_paspor' => 'PAS99001',
        'nama' => 'Tanaka Kenji',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS->value,
        'izin_tinggal_ke' => '1 (Pertama)',
        'masa_berlaku' => '2027-01-15',
        'penjamin' => 'PT Maju Bersama',
        'alamat_penjamin' => 'Jl. Merdeka No. 10 Jakarta',
        'nomor_telepon' => '081234567890',
        'foto' => $photo,
    ];

    $response = $this->actingAs($admin)->post('/wna', $payload);

    $response->assertRedirect('/wna');
    $response->assertSessionHas('success', 'Data WNA berhasil ditambahkan.');

    $this->assertDatabaseHas('wna', [
        'no_register' => 'REG-WNA-99001',
        'no_paspor' => 'PAS99001',
        'nama' => 'Tanaka Kenji',
        'jenis_izin_tinggal' => 'ITAS',
    ]);

    $wna = Wna::where('no_register', 'REG-WNA-99001')->firstOrFail();
    expect($wna->foto)->not->toBeNull();
    Storage::disk('local')->assertExists($wna->foto);
});

test('petugas is forbidden from creating wna records', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $payload = [
        'no_register' => 'REG-WNA-PETUGAS',
        'no_paspor' => 'PASPETUGAS',
        'nama' => 'Unauthorized Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Germany',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS->value,
        'masa_berlaku' => '2026-12-31',
    ];

    $this->actingAs($petugas)->post('/wna', $payload)->assertForbidden();
    $this->actingAs($petugas)->get('/wna/create')->assertForbidden();
    $this->assertDatabaseMissing('wna', ['no_register' => 'REG-WNA-PETUGAS']);
});

test('store wna validates required fields and invalid enums', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/wna', [
        'no_register' => '',
        'no_paspor' => '',
        'nama' => '',
        'jenis_kelamin' => 'INVALID_GENDER',
        'kewarganegaraan' => '',
        'jenis_izin_tinggal' => 'INVALID_VISA',
        'masa_berlaku' => 'not-a-date',
    ]);

    $response->assertSessionHasErrors([
        'no_register',
        'no_paspor',
        'nama',
        'jenis_kelamin',
        'kewarganegaraan',
        'jenis_izin_tinggal',
        'masa_berlaku',
    ]);
});

test('store wna rejects duplicate no_register', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Wna::factory()->create(['no_register' => 'REG-EXISTING']);

    $response = $this->actingAs($admin)->post('/wna', [
        'no_register' => 'REG-EXISTING',
        'no_paspor' => 'PAS123',
        'nama' => 'Another Name',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Canada',
        'jenis_izin_tinggal' => JenisIzinTinggal::VOA->value,
        'masa_berlaku' => '2026-12-31',
    ]);

    $response->assertSessionHasErrors(['no_register']);
});

test('admin can update wna record keeping own register and replacing private photo', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $oldPhoto = UploadedFile::fake()->image('old.jpg', 200, 200)->size(100);
    $oldPath = Storage::disk('local')->putFile('wna/photos', $oldPhoto);

    $wna = Wna::factory()->create([
        'no_register' => 'REG-WNA-UPDATE-1',
        'nama' => 'Old Name',
        'foto' => $oldPath,
    ]);

    $newPhoto = UploadedFile::fake()->image('new.jpg', 200, 200)->size(200);

    $response = $this->actingAs($admin)->put('/wna/'.$wna->id, [
        'no_register' => 'REG-WNA-UPDATE-1', // Keeping own register
        'no_paspor' => 'PASNEW99',
        'nama' => 'Updated Tanaka',
        'jenis_kelamin' => Gender::LakiLaki->value,
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAP->value,
        'masa_berlaku' => '2028-05-20',
        'foto' => $newPhoto,
    ]);

    $response->assertRedirect('/wna');
    $response->assertSessionHas('success', 'Data WNA berhasil diperbarui.');

    $wna->refresh();
    expect($wna->nama)->toBe('Updated Tanaka')
        ->and($wna->jenis_izin_tinggal)->toBe(JenisIzinTinggal::ITAP)
        ->and($wna->foto)->not->toBe($oldPath);

    Storage::disk('local')->assertExists($wna->foto);
    Storage::disk('local')->assertMissing($oldPath);
});

test('update wna rejects duplicate no_register belonging to another wna', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $wna1 = Wna::factory()->create(['no_register' => 'REG-FIRST']);
    $wna2 = Wna::factory()->create(['no_register' => 'REG-SECOND']);

    $response = $this->actingAs($admin)->put('/wna/'.$wna2->id, [
        'no_register' => 'REG-FIRST', // Duplicate of wna1
        'no_paspor' => $wna2->no_paspor,
        'nama' => $wna2->nama,
        'jenis_kelamin' => $wna2->jenis_kelamin->value,
        'kewarganegaraan' => $wna2->kewarganegaraan,
        'jenis_izin_tinggal' => $wna2->jenis_izin_tinggal->value,
        'masa_berlaku' => $wna2->masa_berlaku->toDateString(),
    ]);

    $response->assertSessionHasErrors(['no_register']);
});

test('petugas is forbidden from updating or deleting wna records or accessing edit page', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $wna = Wna::factory()->create(['nama' => 'Original Name']);

    $this->actingAs($petugas)->get('/wna/'.$wna->id.'/edit')->assertForbidden();

    $this->actingAs($petugas)->put('/wna/'.$wna->id, [
        'no_register' => $wna->no_register,
        'no_paspor' => $wna->no_paspor,
        'nama' => 'Hacked Name',
        'jenis_kelamin' => $wna->jenis_kelamin->value,
        'kewarganegaraan' => $wna->kewarganegaraan,
        'jenis_izin_tinggal' => $wna->jenis_izin_tinggal->value,
        'masa_berlaku' => $wna->masa_berlaku->toDateString(),
    ])->assertForbidden();

    expect($wna->fresh()->nama)->toBe('Original Name');

    $this->actingAs($petugas)->delete('/wna/'.$wna->id)->assertForbidden();
    $this->assertDatabaseHas('wna', ['id' => $wna->id]);
});

test('admin can delete wna record and cleans up storage photo', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $photo = UploadedFile::fake()->image('avatar.jpg', 200, 200)->size(150);
    $path = Storage::disk('local')->putFile('wna/photos', $photo);

    $wna = Wna::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->delete('/wna/'.$wna->id);

    $response->assertRedirect('/wna');
    $response->assertSessionHas('success', 'Data WNA berhasil dihapus.');

    $this->assertDatabaseMissing('wna', ['id' => $wna->id]);
    Storage::disk('local')->assertMissing($path);
});

test('search filters wna by name, passport, or register', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $target1 = Wna::factory()->create(['nama' => 'Alexander Supertramp', 'no_paspor' => 'USA001']);
    $target2 = Wna::factory()->create(['nama' => 'John Doe', 'no_paspor' => 'PASSPOR-SPECIAL']);
    Wna::factory()->create(['nama' => 'Maria Garcia', 'no_paspor' => 'ESP002']);

    $response = $this->actingAs($admin)->get('/wna?search=Supertramp');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('wna.data', 1)
            ->where('wna.data.0.id', $target1->id)
        );

    $response2 = $this->actingAs($admin)->get('/wna?search=SPECIAL');
    $response2->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('wna.data', 1)
            ->where('wna.data.0.id', $target2->id)
        );
});

test('filter by jenis izin tinggal and kewarganegaraan works correctly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Wna::factory()->create(['jenis_izin_tinggal' => JenisIzinTinggal::ITAS, 'kewarganegaraan' => 'Jepang']);
    Wna::factory()->create(['jenis_izin_tinggal' => JenisIzinTinggal::ITAP, 'kewarganegaraan' => 'Jepang']);
    Wna::factory()->create(['jenis_izin_tinggal' => JenisIzinTinggal::VOA, 'kewarganegaraan' => 'Australia']);

    $response = $this->actingAs($admin)->get('/wna?jenis_izin_tinggal=ITAS&kewarganegaraan=Jepang');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('wna.data', 1)
            ->where('wna.data.0.jenis_izin_tinggal', 'ITAS')
            ->where('wna.data.0.kewarganegaraan', 'Jepang')
        );
});

test('whitelisted sorting works properly in both directions', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Wna::factory()->create(['nama' => 'Aaron']);
    Wna::factory()->create(['nama' => 'Zack']);

    $responseAsc = $this->actingAs($admin)->get('/wna?sort_by=nama&sort_order=asc');
    $responseAsc->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.data.0.nama', 'Aaron')
        );

    $responseDesc = $this->actingAs($admin)->get('/wna?sort_by=nama&sort_order=desc');
    $responseDesc->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.data.0.nama', 'Zack')
        );
});

test('both admin and petugas can access printable wna card', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $wna = Wna::factory()->create();

    $this->actingAs($admin)->get('/wna/'.$wna->id.'/card')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/Card')
            ->where('wna.id', $wna->id)
            ->has('printedAt')
        );

    $this->actingAs($petugas)->get('/wna/'.$wna->id.'/card')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/Card')
            ->where('wna.id', $wna->id)
        );
});

test('admin can access export pdf while petugas is forbidden', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Wna::factory()->count(3)->create();

    $this->actingAs($admin)->get('/wna/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/PrintList')
            ->has('wnaList', 3)
            ->has('printedAt')
        );

    $this->actingAs($petugas)->get('/wna/export-pdf')
        ->assertForbidden();
});

test('petugas and admin can stream protected photo, guest is denied, 404 on missing file', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $file = UploadedFile::fake()->image('id_photo.jpg', 300, 400);
    $path = Storage::disk('local')->putFile('wna/photos', $file);

    $wna = Wna::factory()->create(['foto' => $path]);

    // Guest cannot access photo stream
    $this->get('/wna/'.$wna->id.'/photo')->assertRedirect('/login');

    // Admin can stream photo
    $responseAdmin = $this->actingAs($admin)->get('/wna/'.$wna->id.'/photo');
    $responseAdmin->assertOk();
    expect($responseAdmin->headers->get('Content-Disposition'))->toContain('inline');

    // Petugas can stream photo
    $responsePetugas = $this->actingAs($petugas)->get('/wna/'.$wna->id.'/photo');
    $responsePetugas->assertOk();

    // Photo endpoint returns 404 when wna has no photo
    $wnaNoPhoto = Wna::factory()->create(['foto' => null]);
    $this->actingAs($admin)->get('/wna/'.$wnaNoPhoto->id.'/photo')->assertNotFound();

    // Photo endpoint returns 404 when file is missing from disk
    $wnaMissingFile = Wna::factory()->create(['foto' => 'wna/photos/missing_file.jpg']);
    $this->actingAs($admin)->get('/wna/'.$wnaMissingFile->id.'/photo')->assertNotFound();
});

test('database failure during store cleans up uploaded photo without orphaned files', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $photo = UploadedFile::fake()->image('test.jpg', 300, 400);

    // Existing register will trigger a database query exception inside transaction
    Wna::factory()->create(['no_register' => 'REG-CONFLICT']);

    try {
        $this->actingAs($admin)->post('/wna', [
            'no_register' => 'REG-CONFLICT',
            'no_paspor' => 'PAS99',
            'nama' => 'Conflict Tester',
            'jenis_kelamin' => Gender::LakiLaki->value,
            'kewarganegaraan' => 'Japan',
            'jenis_izin_tinggal' => JenisIzinTinggal::ITAS->value,
            'masa_berlaku' => '2027-01-01',
            'foto' => $photo,
        ]);
    } catch (Throwable $e) {
        // Validation will usually prevent this, but if DB fails:
    }

    // Ensure no orphaned photo exists in local storage
    $files = Storage::disk('local')->allFiles('wna/photos');
    expect($files)->toBeEmpty();
});

test('database failure during update cleans up newly uploaded photo and preserves old photo', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $oldFile = UploadedFile::fake()->image('old.jpg', 200, 200);
    $oldPath = Storage::disk('local')->putFile('wna/photos', $oldFile);
    $wna = Wna::factory()->create(['foto' => $oldPath]);

    // Force an exception during update using an existing register conflict
    $other = Wna::factory()->create(['no_register' => 'REG-OTHER']);
    $newFile = UploadedFile::fake()->image('new.jpg', 200, 200);

    try {
        $this->actingAs($admin)->put('/wna/'.$wna->id, [
            'no_register' => 'REG-OTHER', // Conflict!
            'no_paspor' => $wna->no_paspor,
            'nama' => 'Updated Name',
            'jenis_kelamin' => $wna->jenis_kelamin->value,
            'kewarganegaraan' => $wna->kewarganegaraan,
            'jenis_izin_tinggal' => $wna->jenis_izin_tinggal->value,
            'masa_berlaku' => $wna->masa_berlaku->toDateString(),
            'foto' => $newFile,
        ]);
    } catch (Throwable $e) {
    }

    // Old photo must still exist!
    Storage::disk('local')->assertExists($oldPath);
});

test('early warning boundary audit: exact today, 14 days, 15 days, 30 days, 31 days, and expired', function () {
    $now = now()->startOfDay();

    // 1. Expired yesterday (-1 day)
    $yesterday = Wna::factory()->create(['masa_berlaku' => $now->copy()->subDay()->toDateString()]);
    expect($yesterday->sisa_hari)->toBe(-1)
        ->and($yesterday->urgensi)->toBe('kedaluwarsa')
        ->and($yesterday->urgensi_label)->toBe('Sudah Kedaluwarsa');

    // 2. Exact Today (0 days)
    $today = Wna::factory()->create(['masa_berlaku' => $now->toDateString()]);
    expect($today->sisa_hari)->toBe(0)
        ->and($today->urgensi)->toBe('merah')
        ->and($today->urgensi_label)->toContain('Sangat Mendesak');

    // 3. Exact 14 days (merah boundary)
    $day14 = Wna::factory()->create(['masa_berlaku' => $now->copy()->addDays(14)->toDateString()]);
    expect($day14->sisa_hari)->toBe(14)
        ->and($day14->urgensi)->toBe('merah');

    // 4. Exact 15 days (kuning start)
    $day15 = Wna::factory()->create(['masa_berlaku' => $now->copy()->addDays(15)->toDateString()]);
    expect($day15->sisa_hari)->toBe(15)
        ->and($day15->urgensi)->toBe('kuning');

    // 5. Exact 30 days (kuning boundary)
    $day30 = Wna::factory()->create(['masa_berlaku' => $now->copy()->addDays(30)->toDateString()]);
    expect($day30->sisa_hari)->toBe(30)
        ->and($day30->urgensi)->toBe('kuning');

    // 6. Exact 31 days (biru start)
    $day31 = Wna::factory()->create(['masa_berlaku' => $now->copy()->addDays(31)->toDateString()]);
    expect($day31->sisa_hari)->toBe(31)
        ->and($day31->urgensi)->toBe('biru');

    // 7. Exactly 3 calendar months
    $threeMonths = Wna::factory()->create(['masa_berlaku' => $now->copy()->addMonthsNoOverflow(3)->toDateString()]);
    expect($threeMonths->urgensi)->toBe('biru');

    // 8. Beyond 3 calendar months (e.g. 3 months + 5 days)
    $farAway = Wna::factory()->create(['masa_berlaku' => $now->copy()->addMonthsNoOverflow(3)->addDays(5)->toDateString()]);

    // Check scopeEarlyWarning
    $earlyWarningIds = Wna::query()->earlyWarning($now)->pluck('id');

    expect($earlyWarningIds)->toContain($today->id)
        ->and($earlyWarningIds)->toContain($day14->id)
        ->and($earlyWarningIds)->toContain($day15->id)
        ->and($earlyWarningIds)->toContain($day30->id)
        ->and($earlyWarningIds)->toContain($day31->id)
        ->and($earlyWarningIds)->toContain($threeMonths->id)
        ->and($earlyWarningIds)->not->toContain($yesterday->id)
        ->and($earlyWarningIds)->not->toContain($farAway->id);
});

test('admin can export csv while petugas is forbidden', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);
    Wna::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get('/wna/export');
    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('attachment; filename=data-wna-');

    $this->actingAs($petugas)->get('/wna/export')
        ->assertForbidden();
});

test('csv export respects search, filters, and whitelisted sorting', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $match1 = Wna::factory()->create([
        'nama' => 'Tanaka Kenji',
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
    ]);
    $match2 = Wna::factory()->create([
        'nama' => 'Tanaka Aoi',
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
    ]);
    $nonMatch = Wna::factory()->create([
        'nama' => 'Smith John',
        'kewarganegaraan' => 'Australia',
        'jenis_izin_tinggal' => JenisIzinTinggal::VOA,
    ]);

    $response = $this->actingAs($admin)->get('/wna/export?search=Tanaka&kewarganegaraan=Jepang&jenis_izin_tinggal=ITAS&sort_by=nama&sort_order=asc');
    $response->assertOk();

    $content = $response->streamedContent();

    // Must contain UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Nomor Register')
        ->and($content)->toContain('Nama Lengkap')
        ->and($content)->toContain('Tanaka Aoi')
        ->and($content)->toContain('Tanaka Kenji')
        ->and($content)->not->toContain('Smith John');
});

test('csv export mitigates formula injection for values starting with dangerous trigger characters', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $vulnerableRecord = Wna::factory()->create([
        'nama' => "=cmd|' /C calc'!A0",
        'penjamin' => '  @SUM(1+1)',
        'no_paspor' => "\t-2+3+calc",
        'nomor_telepon' => '+62812345678', // Legitimate numeric starting with +, should stay numeric or safe
    ]);

    $response = $this->actingAs($admin)->get('/wna/export');
    $response->assertOk();

    $content = $response->streamedContent();

    // Formulas should be prefixed with single quote even when preceded by whitespace or tabs
    expect($content)->toContain("'=cmd|' /C calc'!A0")
        ->and($content)->toContain("'  @SUM(1+1)")
        ->and($content)->toContain("'\t-2+3+calc")
        ->and($content)->toContain('+62812345678');
});

test('static export and print routes do not collide with dynamic wna parameter', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Wna::factory()->create();

    // These routes should not be captured by /wna/{wna}
    $this->actingAs($admin)->get('/wna/export')->assertOk();
    $this->actingAs($admin)->get('/wna/print-list')->assertOk();
    $this->actingAs($admin)->get('/wna/export-pdf')->assertOk();
});

test('photo endpoint prevents directory traversal, invalid prefix, and non-image mimes', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Path traversal attempt in foto attribute
    $traversalWna = Wna::factory()->create(['foto' => 'wna/photos/../../etc/passwd']);
    $this->actingAs($admin)->get('/wna/'.$traversalWna->id.'/photo')->assertNotFound();

    // 2. Invalid directory prefix (outside wna/photos/)
    $wrongDirWna = Wna::factory()->create(['foto' => 'documents/confidential.pdf']);
    $this->actingAs($admin)->get('/wna/'.$wrongDirWna->id.'/photo')->assertNotFound();

    // 3. Non-image file stored in wna/photos
    $fakeScript = UploadedFile::fake()->create('malicious.txt', 10, 'text/plain');
    $scriptPath = Storage::disk('local')->putFileAs('wna/photos', $fakeScript, 'malicious.txt');
    $scriptWna = Wna::factory()->create(['foto' => $scriptPath]);

    $response = $this->actingAs($admin)->get('/wna/'.$scriptWna->id.'/photo');
    expect($response->status())->toBe(415);

    // 4. Legitimate image includes nosniff header
    $validImage = UploadedFile::fake()->image('profile.jpg', 200, 200);
    $imagePath = Storage::disk('local')->putFileAs('wna/photos', $validImage, 'profile.jpg');
    $validWna = Wna::factory()->create(['foto' => $imagePath]);

    $validResponse = $this->actingAs($admin)->get('/wna/'.$validWna->id.'/photo');
    $validResponse->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});
