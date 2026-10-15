<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing profile', function () {
    $this->get(route('profile.edit'))
        ->assertRedirect(route('login'));

    $this->patch(route('profile.update'), [
        'nama' => 'Guest Attempt',
        'username' => 'guest_attempt',
    ])->assertRedirect(route('login'));
});

test('user can view own profile page with proper inertia props', function () {
    $user = User::factory()->create([
        'nama' => 'Petugas Satu',
        'username' => 'petugas_satu',
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Profile')
            ->has('user', fn (Assert $prop) => $prop
                ->where('id', $user->id)
                ->where('nama', 'Petugas Satu')
                ->where('username', 'petugas_satu')
                ->where('role', 'user')
                ->where('role_label', 'Petugas')
                ->etc()
            )
        );
});

test('name and username can be updated', function () {
    $user = User::factory()->create([
        'nama' => 'Nama Lama',
        'username' => 'user_lama',
    ]);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => 'Nama Baru',
        'username' => 'user_baru',
        'email' => 'baru@example.com',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success', 'Profil berhasil diperbarui.');

    $user->refresh();

    expect($user->nama)->toBe('Nama Baru')
        ->and($user->name)->toBe('Nama Baru')
        ->and($user->username)->toBe('user_baru')
        ->and($user->email)->toBe('baru@example.com');
});

test('duplicate username is rejected while current username remains valid', function () {
    User::factory()->create(['username' => 'user_lain']);

    $user = User::factory()->create(['username' => 'user_saya']);

    // Attempting to take another user's username
    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => 'Pengguna Saya',
        'username' => 'user_lain',
    ]);

    $response->assertSessionHasErrors('username');

    // Keeping own username should be completely valid
    $validResponse = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => 'Pengguna Saya Update',
        'username' => 'user_saya',
    ]);

    $validResponse->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->nama)->toBe('Pengguna Saya Update')
        ->and($user->username)->toBe('user_saya');
});

test('user cannot change role or another users profile', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($petugas)->patch(route('profile.update'), [
        'nama' => 'Petugas Hacked',
        'username' => $petugas->username,
        'role' => 'admin',
    ]);

    $response->assertSessionHasNoErrors();

    $petugas->refresh();
    expect($petugas->role)->toBe(UserRole::User)
        ->and($petugas->nama)->toBe('Petugas Hacked');

    // Verify admin is also untouched
    $admin->refresh();
    expect($admin->role)->toBe(UserRole::Admin);
});

test('photo upload replacement and deletion work securely', function () {
    Storage::fake('local');

    $user = User::factory()->create(['role' => UserRole::User]);

    $initialPhoto = UploadedFile::fake()->image('avatar.jpg', 300, 300);

    // 1. Upload new photo
    $uploadResponse = $this->actingAs($user)->post(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'foto' => $initialPhoto,
    ]);

    $uploadResponse->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->foto)->not->toBeNull();
    Storage::disk('local')->assertExists($user->foto);

    $oldPhotoPath = $user->foto;

    // 2. Stream photo
    $streamResponse = $this->actingAs($user)->get(route('data-user.photo', $user->id));
    $streamResponse->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');

    // 3. Replace photo
    $replacementPhoto = UploadedFile::fake()->image('new_avatar.png', 400, 400);

    $replaceResponse = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'foto' => $replacementPhoto,
    ]);

    $replaceResponse->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->foto)->not->toBe($oldPhotoPath);
    Storage::disk('local')->assertExists($user->foto);
    Storage::disk('local')->assertMissing($oldPhotoPath);

    // 4. Delete photo
    $deletePhotoResponse = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'remove_foto' => true,
    ]);

    $deletePhotoResponse->assertSessionHasNoErrors();

    $lastPhotoPath = $user->foto;
    $user->refresh();
    expect($user->foto)->toBeNull();
    Storage::disk('local')->assertMissing($lastPhotoPath);
});

test('photo replacement does not delete file if still referenced by another user', function () {
    Storage::fake('local');

    $sharedPhotoPath = 'users/photos/shared_sample.jpg';
    Storage::disk('local')->put($sharedPhotoPath, 'fake-image-data');

    $user1 = User::factory()->create(['foto' => $sharedPhotoPath]);
    $user2 = User::factory()->create(['foto' => $sharedPhotoPath]);

    $newPhoto = UploadedFile::fake()->image('new_photo.png', 200, 200);

    $this->actingAs($user1)->patch(route('profile.update'), [
        'nama' => $user1->nama,
        'username' => $user1->username,
        'foto' => $newPhoto,
    ]);

    $user1->refresh();
    expect($user1->foto)->not->toBe($sharedPhotoPath);
    // Because user2 still references sharedPhotoPath, it must NOT be deleted
    Storage::disk('local')->assertExists($sharedPhotoPath);
});

test('photo with invalid format or excessive size is rejected', function () {
    $user = User::factory()->create();

    // Invalid format (txt instead of jpg/png/webp)
    $textDoc = UploadedFile::fake()->create('malicious.txt', 100, 'text/plain');

    $res1 = $this->actingAs($user)->post(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'foto' => $textDoc,
    ]);

    $res1->assertSessionHasErrors('foto');

    // Excessive size (> 2048 KB)
    $hugePhoto = UploadedFile::fake()->create('oversized.jpg', 3000, 'image/jpeg');

    $res2 = $this->actingAs($user)->post(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'foto' => $hugePhoto,
    ]);

    $res2->assertSessionHasErrors('foto');
});

test('wrong current password is rejected when updating password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123'),
    ]);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'current_password' => 'WrongCurrentPassword',
        'password' => 'NewSecretPassword123',
        'password_confirmation' => 'NewSecretPassword123',
    ]);

    $response->assertSessionHasErrors('current_password');

    $user->refresh();
    expect(Hash::check('CurrentPassword123', $user->password))->toBeTrue();
});

test('new password and confirmation are properly validated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123'),
    ]);

    // Password confirmation mismatch
    $res1 = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'current_password' => 'CurrentPassword123',
        'password' => 'NewSecretPassword123',
        'password_confirmation' => 'DifferentPassword123',
    ]);
    $res1->assertSessionHasErrors('password');

    // Password too short (< 8 chars)
    $res2 = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'current_password' => 'CurrentPassword123',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);
    $res2->assertSessionHasErrors('password');

    // Missing current password when new password is submitted
    $res3 = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'password' => 'NewSecretPassword123',
        'password_confirmation' => 'NewSecretPassword123',
    ]);
    $res3->assertSessionHasErrors('current_password');

    // Current password filled but new password missing
    $res4 = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'current_password' => 'CurrentPassword123',
    ]);
    $res4->assertSessionHasErrors('password');
});

test('old password remains unchanged when password fields are left blank', function () {
    $user = User::factory()->create([
        'password' => Hash::make('UnchangedPassword123'),
    ]);

    $originalHash = $user->password;

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => 'Nama Baru Saja',
        'username' => $user->username,
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->nama)->toBe('Nama Baru Saja')
        ->and(Hash::check('UnchangedPassword123', $user->password))->toBeTrue()
        ->and($user->password)->toBe($originalHash);
});

test('new password is saved with correct hashing', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123'),
    ]);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'nama' => $user->nama,
        'username' => $user->username,
        'current_password' => 'OldPassword123',
        'password' => 'SuperSecretNewPassword123',
        'password_confirmation' => 'SuperSecretNewPassword123',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect(Hash::check('SuperSecretNewPassword123', $user->password))->toBeTrue()
        ->and(Hash::check('OldPassword123', $user->password))->toBeFalse();
});

test('inertia props do not contain password or password hash', function () {
    $user = User::factory()->create([
        'nama' => 'Sensitive Check',
        'password' => Hash::make('Secret123!'),
    ]);

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Profile')
            ->has('user', fn (Assert $prop) => $prop
                ->missing('password')
                ->missing('two_factor_secret')
                ->etc()
            )
            ->has('auth.user', fn (Assert $prop) => $prop
                ->missing('password')
                ->missing('two_factor_secret')
                ->etc()
            )
        );
});

test('profile behavior is consistent for admin and petugas users', function () {
    // Admin user
    $admin = User::factory()->create([
        'nama' => 'Admin Utama',
        'username' => 'admin_utama',
        'role' => UserRole::Admin,
    ]);

    $adminRes = $this->actingAs($admin)->get(route('profile.edit'));
    $adminRes->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('user.role', 'admin')
            ->where('user.role_label', 'Administrator')
        );

    // Petugas user
    $petugas = User::factory()->create([
        'nama' => 'Petugas Loket',
        'username' => 'petugas_loket',
        'role' => UserRole::User,
    ]);

    $petugasRes = $this->actingAs($petugas)->get(route('profile.edit'));
    $petugasRes->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('user.role', 'user')
            ->where('user.role_label', 'Petugas')
        );
});

test('user can delete their account with password verification', function () {
    $user = User::factory()->create([
        'password' => Hash::make('DeletePass123'),
    ]);

    // Wrong password fails
    $failResponse = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'WrongPassword',
    ]);
    $failResponse->assertSessionHasErrors('password');
    expect($user->fresh())->not->toBeNull();

    // Correct password succeeds
    $successResponse = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'DeletePass123',
    ]);

    $successResponse->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});
