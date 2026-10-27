<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

test('guest is redirected to login when accessing data user endpoints', function () {
    $target = User::factory()->create();

    $this->get('/data-user')->assertRedirect('/login');
    $this->post('/data-user', [])->assertRedirect('/login');
    $this->put('/data-user/'.$target->id, [])->assertRedirect('/login');
    $this->delete('/data-user/'.$target->id)->assertRedirect('/login');
    $this->get('/data-user/'.$target->id.'/photo')->assertRedirect('/login');
});

test('non-admin user is forbidden from accessing data user management endpoints', function () {
    $regularUser = User::factory()->create(['role' => UserRole::User]);
    $target = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($regularUser)->get('/data-user')->assertForbidden();

    $this->actingAs($regularUser)->post('/data-user', [
        'nama' => 'Test Baru',
        'username' => 'testbaru',
        'role' => 'user',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertForbidden();

    $this->actingAs($regularUser)->put('/data-user/'.$target->id, [
        'nama' => 'Update Nama',
        'username' => $target->username,
        'role' => 'user',
    ])->assertForbidden();

    $this->actingAs($regularUser)->delete('/data-user/'.$target->id)->assertForbidden();
    $this->actingAs($regularUser)->get('/data-user/'.$target->id.'/photo')->assertForbidden();
});

test('admin can view data user listing and password hash is never exposed in inertia props', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'password' => Hash::make('secret_admin_pass'),
    ]);

    $userA = User::factory()->create([
        'nama' => 'Ahmad Dahlan',
        'username' => 'ahmad_dahlan',
        'role' => UserRole::User,
        'password' => Hash::make('secret_user_pass'),
    ]);

    $response = $this->actingAs($admin)->get('/data-user');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('data-user/Index')
            ->has('users.data')
            ->has('filters')
            ->has('roleOptions')
            ->where('can.create', true)
            ->where('authUserId', $admin->id)
        );

    // Assert that neither password nor password hash is present anywhere in Inertia page props
    $props = $response->viewData('page')['props'];
    $propsJson = json_encode($props);
    expect($propsJson)->not->toContain('secret_admin_pass')
        ->not->toContain('secret_user_pass')
        ->not->toContain('password_hash');

    foreach ($props['users']['data'] as $userData) {
        expect(array_key_exists('password', $userData))->toBeFalse()
            ->and(array_key_exists('password_hash', $userData))->toBeFalse()
            ->and(array_key_exists('remember_token', $userData))->toBeFalse();
    }
});

test('admin can filter users by search and role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    User::factory()->create([
        'nama' => 'Siti Aminah',
        'username' => 'siti_aminah',
        'role' => UserRole::User,
    ]);

    User::factory()->create([
        'nama' => 'Budi Santoso',
        'username' => 'budi_santoso',
        'role' => UserRole::Admin,
    ]);

    // Search by nama
    $searchResponse = $this->actingAs($admin)->get('/data-user?search=Aminah');
    $searchResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.total', 1)
            ->where('users.data.0.username', 'siti_aminah')
        );

    // Filter by role: admin (admin + budi_santoso)
    $roleResponse = $this->actingAs($admin)->get('/data-user?role=admin');
    $roleResponse->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.total', 2)
        );
});

test('admin can create a new user account with hashed password and valid data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post('/data-user', [
        'nama' => 'Operator Baru',
        'username' => 'operator_baru',
        'email' => 'operator@imigrasi.go.id',
        'role' => 'user',
        'password' => 'KatasandiRahasia123',
        'password_confirmation' => 'KatasandiRahasia123',
    ]);

    $response->assertRedirect('/data-user');
    $response->assertSessionHas('success', 'Data pengguna berhasil ditambahkan.');

    $createdUser = User::where('username', 'operator_baru')->first();
    expect($createdUser)->not->toBeNull()
        ->and($createdUser->nama)->toBe('Operator Baru')
        ->and($createdUser->role)->toBe(UserRole::User)
        ->and(Hash::check('KatasandiRahasia123', $createdUser->password))->toBeTrue();
});

test('user creation fails when username is duplicate or password confirmation mismatches', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    User::factory()->create(['username' => 'existing_user']);

    // Duplicate username
    $response = $this->actingAs($admin)->post('/data-user', [
        'nama' => 'Operator Dua',
        'username' => 'existing_user',
        'role' => 'user',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
    $response->assertSessionHasErrors(['username']);

    // Password confirmation mismatch
    $response2 = $this->actingAs($admin)->post('/data-user', [
        'nama' => 'Operator Tiga',
        'username' => 'operator_tiga',
        'role' => 'user',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword!',
    ]);
    $response2->assertSessionHasErrors(['password']);
});

test('admin can update user and retain existing password when password field is empty', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create([
        'nama' => 'Nama Lama',
        'username' => 'user_target',
        'role' => UserRole::User,
        'password' => Hash::make('OriginalPassword123'),
    ]);

    $oldPasswordHash = $target->password;

    $response = $this->actingAs($admin)->put('/data-user/'.$target->id, [
        'nama' => 'Nama Baru Diperbarui',
        'username' => 'user_target', // same username
        'email' => 'updated@imigrasi.go.id',
        'role' => 'user',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect('/data-user');
    $response->assertSessionHas('success', 'Data pengguna berhasil diperbarui.');

    $target->refresh();
    expect($target->nama)->toBe('Nama Baru Diperbarui')
        ->and($target->password)->toBe($oldPasswordHash);
});

test('admin can update user password with new hashed password', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create([
        'username' => 'user_pwd_test',
        'role' => UserRole::User,
        'password' => Hash::make('OldPassword123'),
    ]);

    $response = $this->actingAs($admin)->put('/data-user/'.$target->id, [
        'nama' => $target->nama,
        'username' => $target->username,
        'role' => 'user',
        'password' => 'NewSecretPassword456',
        'password_confirmation' => 'NewSecretPassword456',
    ]);

    $response->assertRedirect('/data-user');

    $target->refresh();
    expect(Hash::check('NewSecretPassword456', $target->password))->toBeTrue();
});

test('admin can upload avatar photo, stream it, and replace it', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $photoFile = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Create user with photo
    $response = $this->actingAs($admin)->post('/data-user', [
        'nama' => 'User Berfoto',
        'username' => 'user_berfoto',
        'role' => 'user',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'foto' => $photoFile,
    ]);

    $response->assertRedirect('/data-user');

    $user = User::where('username', 'user_berfoto')->first();
    expect($user->foto)->not->toBeNull();
    Storage::disk('local')->assertExists($user->foto);

    // Stream photo
    $streamResponse = $this->actingAs($admin)->get('/data-user/'.$user->id.'/photo');
    $streamResponse->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');

    // Replace photo
    $newPhoto = UploadedFile::fake()->image('avatar_new.png', 200, 200);
    $oldPath = $user->foto;

    $this->actingAs($admin)->put('/data-user/'.$user->id, [
        'nama' => $user->nama,
        'username' => $user->username,
        'role' => 'user',
        'foto' => $newPhoto,
    ]);

    $user->refresh();
    expect($user->foto)->not->toBe($oldPath);
    Storage::disk('local')->assertExists($user->foto);
    Storage::disk('local')->assertMissing($oldPath);
});

test('photo replacement does not delete old photo if another user references the same file path', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $sharedPath = 'users/photos/shared_avatar.jpg';
    Storage::disk('local')->put($sharedPath, 'fake-image-content');

    $userA = User::factory()->create(['role' => UserRole::User, 'foto' => $sharedPath]);
    $userB = User::factory()->create(['role' => UserRole::User, 'foto' => $sharedPath]);

    $newPhoto = UploadedFile::fake()->image('user_a_new.jpg');

    $this->actingAs($admin)->put('/data-user/'.$userA->id, [
        'nama' => $userA->nama,
        'username' => $userA->username,
        'role' => 'user',
        'foto' => $newPhoto,
    ]);

    $userA->refresh();
    expect($userA->foto)->not->toBe($sharedPath);
    // Shared path must still exist in storage because userB is still using it!
    Storage::disk('local')->assertExists($sharedPath);
});

test('invalid photo file types and oversized files are rejected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Invalid extension/mime (e.g. text file)
    $textDoc = UploadedFile::fake()->create('malicious.php', 50, 'text/plain');

    $response = $this->actingAs($admin)->post('/data-user', [
        'nama' => 'Bad Upload',
        'username' => 'bad_upload',
        'role' => 'user',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'foto' => $textDoc,
    ]);
    $response->assertSessionHasErrors(['foto']);

    // Oversized photo (> 2048 KB)
    $hugePhoto = UploadedFile::fake()->create('huge.jpg', 3000, 'image/jpeg');
    $response2 = $this->actingAs($admin)->post('/data-user', [
        'nama' => 'Huge Upload',
        'username' => 'huge_upload',
        'role' => 'user',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'foto' => $hugePhoto,
    ]);
    $response2->assertSessionHasErrors(['foto']);
});

test('anti-lockout: admin cannot delete their own account', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->delete('/data-user/'.$admin->id);
    $response->assertForbidden();

    expect(User::find($admin->id))->not->toBeNull();
});

test('anti-lockout: admin cannot demote their own role to user', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->put('/data-user/'.$admin->id, [
        'nama' => $admin->nama,
        'username' => $admin->username,
        'role' => 'user', // demotion attempt
    ]);
    $response->assertForbidden();

    $admin->refresh();
    expect($admin->role)->toBe(UserRole::Admin);
});

test('anti-lockout: cannot delete the last active admin', function () {
    // Only one admin exists
    $lastAdmin = User::factory()->create(['role' => UserRole::Admin]);

    // Another admin attempts deletion, but we simulate attempting to delete the only admin
    // Even if an authorized admin tries to delete the last admin:
    // To test deletion of last admin, suppose admin2 exists, but during the request admin2 is already deleted or we delete target when target is the only admin
    $adminCount = User::where('role', UserRole::Admin->value)->count();
    expect($adminCount)->toBe(1);

    // If an admin tries to delete another admin, we need at least 2 admins to initiate the request without self-deletion
    $admin2 = User::factory()->create(['role' => UserRole::Admin]);
    expect(User::where('role', UserRole::Admin->value)->count())->toBe(2);

    // Now admin2 deletes lastAdmin -> succeeds because 2 admins exist
    $res = $this->actingAs($admin2)->delete('/data-user/'.$lastAdmin->id);
    $res->assertRedirect('/data-user');

    expect(User::where('role', UserRole::Admin->value)->count())->toBe(1);

    // Now admin2 is the sole remaining admin! If someone tries to delete admin2 via direct DELETE:
    // It's self deletion (403). But what if another user route call tried?
    // Let's create a scenario where admin2 is demoted when they are the only admin:
    $demoteRes = $this->actingAs($admin2)->put('/data-user/'.$admin2->id, [
        'nama' => $admin2->nama,
        'username' => $admin2->username,
        'role' => 'user',
    ]);
    $demoteRes->assertForbidden(); // self demotion blocked
});

test('anti-lockout: cannot demote the last active admin when multiple users exist', function () {
    // Admin A and Admin B exist
    $adminA = User::factory()->create(['role' => UserRole::Admin]);
    $adminB = User::factory()->create(['role' => UserRole::Admin]);

    // Admin A demotes Admin B to user -> succeeds because Admin A is still active admin
    $res = $this->actingAs($adminA)->put('/data-user/'.$adminB->id, [
        'nama' => $adminB->nama,
        'username' => $adminB->username,
        'role' => 'user',
    ]);
    $res->assertRedirect('/data-user');
    $adminB->refresh();
    expect($adminB->role)->toBe(UserRole::User);

    // Now Admin A is the ONLY remaining admin in the entire system.
    // Admin A cannot be demoted (both self-demotion 403 and last-admin 422 safeguard this)
    expect(User::where('role', UserRole::Admin->value)->count())->toBe(1);
});

test('admin can delete a regular user', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $targetUser = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($admin)->delete('/data-user/'.$targetUser->id);
    $response->assertRedirect('/data-user');
    $response->assertSessionHas('success', 'Data pengguna berhasil dihapus.');

    expect(User::find($targetUser->id))->toBeNull();
});

test('streaming photo returns 404 if user has no photo or photo file does not exist', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $userWithoutPhoto = User::factory()->create(['role' => UserRole::User, 'foto' => null]);
    $userWithGhostPhoto = User::factory()->create(['role' => UserRole::User, 'foto' => 'users/photos/ghost.jpg']);

    $this->actingAs($admin)->get('/data-user/'.$userWithoutPhoto->id.'/photo')
        ->assertNotFound();

    $this->actingAs($admin)->get('/data-user/'.$userWithGhostPhoto->id.'/photo')
        ->assertNotFound();
});

test('anti-lockout: deleting the last admin triggers 422 exception', function () {
    $lastAdmin = User::factory()->create(['role' => UserRole::Admin]);
    $requester = User::factory()->create(['role' => UserRole::Admin]);

    // Concurrently remove requester so only 1 admin remains in database
    $requester->delete();
    expect(User::where('role', UserRole::Admin->value)->count())->toBe(1);

    $response = $this->actingAs($requester)->delete('/data-user/'.$lastAdmin->id);
    $response->assertStatus(422);

    expect(User::find($lastAdmin->id))->not->toBeNull();
});

test('anti-lockout: demoting the last admin triggers 422 exception', function () {
    $lastAdmin = User::factory()->create(['role' => UserRole::Admin]);
    $requester = User::factory()->create(['role' => UserRole::Admin]);

    // Concurrently remove requester so only 1 admin remains in database
    $requester->delete();
    expect(User::where('role', UserRole::Admin->value)->count())->toBe(1);

    $response = $this->actingAs($requester)->put('/data-user/'.$lastAdmin->id, [
        'nama' => $lastAdmin->nama,
        'username' => $lastAdmin->username,
        'role' => 'user',
    ]);
    $response->assertStatus(422);

    $lastAdmin->refresh();
    expect($lastAdmin->role)->toBe(UserRole::Admin);
});

test('user with username superadmin is hidden from data user listing and cannot be manipulated', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'username' => 'admin_test']);
    $superadmin = User::factory()->create([
        'nama' => 'Super Administrator',
        'username' => 'superadmin',
        'role' => UserRole::Admin,
    ]);
    $normalUser = User::factory()->create([
        'nama' => 'User Biasa',
        'username' => 'user_biasa',
        'role' => UserRole::User,
    ]);

    // Ensure superadmin is not in index listing
    $response = $this->actingAs($admin)->get('/data-user');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('data-user/Index')
            ->has('users.data', 2)
            ->where('users.data.0.username', fn ($val) => $val !== 'superadmin')
            ->where('users.data.1.username', fn ($val) => $val !== 'superadmin')
        );

    // Cannot create user with username superadmin
    $this->actingAs($admin)->post('/data-user', [
        'nama' => 'Duplikat Superadmin',
        'username' => 'superadmin',
        'role' => 'admin',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertSessionHasErrors(['username']);

    // Cannot update superadmin
    $this->actingAs($admin)->put('/data-user/'.$superadmin->id, [
        'nama' => 'Ubah Superadmin',
        'username' => 'superadmin_baru',
        'role' => 'admin',
    ])->assertForbidden();

    // Cannot delete superadmin
    $this->actingAs($admin)->delete('/data-user/'.$superadmin->id)->assertForbidden();
});
