<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DevelopmentUserSeeder;
use Illuminate\Support\Facades\Hash;

test('development user seeder stops and creates no accounts if credentials are missing or blank', function () {
    config([
        'auth.dev_credentials.admin_password' => null,
        'auth.dev_credentials.petugas_password' => null,
    ]);

    $this->seed(DevelopmentUserSeeder::class);

    expect(User::where('username', 'admin')->count())->toBe(0);
    expect(User::where('username', 'petugas')->count())->toBe(0);

    // Also verify when set to empty string
    config([
        'auth.dev_credentials.admin_password' => '   ',
        'auth.dev_credentials.petugas_password' => '',
    ]);

    $this->seed(DevelopmentUserSeeder::class);

    expect(User::where('username', 'admin')->count())->toBe(0);
    expect(User::where('username', 'petugas')->count())->toBe(0);
});

test('development user seeder creates admin and petugas accounts when env credentials are configured', function () {
    config([
        'auth.dev_credentials.admin_password' => 'TestAdminPass123!',
        'auth.dev_credentials.petugas_password' => 'TestPetugasPass123!',
    ]);

    $this->seed(DevelopmentUserSeeder::class);

    $admin = User::where('username', 'admin')->first();
    expect($admin)->not->toBeNull();
    expect($admin->role)->toBe(UserRole::Admin);
    expect($admin->nama)->toBe('Administrator Sistem');
    expect(Hash::check('TestAdminPass123!', $admin->password))->toBeTrue();

    $petugas = User::where('username', 'petugas')->first();
    expect($petugas)->not->toBeNull();
    expect($petugas->role)->toBe(UserRole::User);
    expect($petugas->nama)->toBe('Petugas Sistem');
    expect(Hash::check('TestPetugasPass123!', $petugas->password))->toBeTrue();
});

test('development user seeder is idempotent and does not overwrite existing user password or data', function () {
    config([
        'auth.dev_credentials.admin_password' => 'TestAdminPass123!',
        'auth.dev_credentials.petugas_password' => 'TestPetugasPass123!',
    ]);

    // Pre-create admin with a custom password
    $customHash = Hash::make('CustomAdminPass999!');
    User::create([
        'nama' => 'Custom Admin Name',
        'name' => 'Custom Admin Name',
        'username' => 'admin',
        'email' => 'custom_admin@local.test',
        'password' => $customHash,
        'role' => UserRole::Admin->value,
    ]);

    // Run seeder
    $this->seed(DevelopmentUserSeeder::class);

    $admin = User::where('username', 'admin')->first();
    expect($admin->nama)->toBe('Custom Admin Name');
    expect($admin->email)->toBe('custom_admin@local.test');
    expect($admin->password)->toBe($customHash);
    expect(Hash::check('CustomAdminPass999!', $admin->password))->toBeTrue();
});

test('development user seeder is prevented in production environment', function () {
    config([
        'auth.dev_credentials.admin_password' => 'TestAdminPass123!',
        'auth.dev_credentials.petugas_password' => 'TestPetugasPass123!',
    ]);

    $this->app->detectEnvironment(fn () => 'production');

    $this->seed(DevelopmentUserSeeder::class);

    expect(User::where('username', 'admin')->count())->toBe(0);
    expect(User::where('username', 'petugas')->count())->toBe(0);
});
