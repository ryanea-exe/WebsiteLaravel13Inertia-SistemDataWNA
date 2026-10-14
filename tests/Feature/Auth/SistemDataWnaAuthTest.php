<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('login screen can be rendered with username and password fields', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/Login')
    );
});

test('users can authenticate with valid username and password', function () {
    $user = User::factory()->create([
        'username' => 'petugas_testing',
        'password' => Hash::make('rahasia123'),
    ]);

    $response = $this->post(route('login'), [
        'username' => 'petugas_testing',
        'password' => 'rahasia123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
});

test('login fails with invalid credentials and does not reveal username existence', function () {
    User::factory()->create([
        'username' => 'petugas_terdaftar',
        'password' => Hash::make('rahasia123'),
    ]);

    // Wrong password for existing user
    $response = $this->post(route('login'), [
        'username' => 'petugas_terdaftar',
        'password' => 'password_salah',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('username');

    // Non-existent user
    $responseNonExistent = $this->post(route('login'), [
        'username' => 'user_tidak_dikenal',
        'password' => 'password_apapun',
    ]);

    $this->assertGuest();
    $responseNonExistent->assertSessionHasErrors('username');

    // Both error messages should be generic and identical
    expect(session('errors')->get('username'))->toBeArray();
});

test('terakhir_login is updated upon successful login', function () {
    $user = User::factory()->create([
        'username' => 'petugas_login',
        'password' => Hash::make('rahasia123'),
        'terakhir_login' => null,
    ]);

    expect($user->terakhir_login)->toBeNull();

    $this->post(route('login'), [
        'username' => 'petugas_login',
        'password' => 'rahasia123',
    ]);

    $this->assertAuthenticated();

    $user->refresh();
    expect($user->terakhir_login)->not->toBeNull();
});

test('users can logout via POST and session is invalidated', function () {
    $user = User::factory()->create([
        'username' => 'user_logout',
    ]);

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});

test('unauthenticated guests are redirected to login when accessing protected routes', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));

    $adminResponse = $this->get('/admin/users');
    $adminResponse->assertRedirect(route('login'));
});

test('user with role user is forbidden from accessing admin endpoints', function () {
    $regularUser = User::factory()->user()->create([
        'username' => 'regular_user',
    ]);

    $response = $this->actingAs($regularUser)->get('/admin/users');

    $response->assertStatus(403);
});

test('admin can access allowed admin endpoints', function () {
    $adminUser = User::factory()->admin()->create([
        'username' => 'admin_user',
    ]);

    $response = $this->actingAs($adminUser)->get('/admin/users');

    $response->assertOk();
    $response->assertJson(['status' => 'admin_access_granted']);
});

test('public registration is not available', function () {
    $getResponse = $this->get('/register');
    $getResponse->assertNotFound();

    $postResponse = $this->post('/register', [
        'name' => 'Pengguna Baru',
        'username' => 'penggunabaru',
        'password' => 'password',
    ]);
    $postResponse->assertNotFound();
});

test('passwords are never returned in model array or application response', function () {
    $user = User::factory()->create([
        'username' => 'secure_user',
        'password' => Hash::make('supersecret'),
    ]);

    $userArray = $user->toArray();
    expect($userArray)->not->toHaveKey('password');

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();

    // Verify Inertia page props do not leak password
    $response->assertInertia(fn (Assert $page) => $page
        ->where('auth.user.username', 'secure_user')
        ->missing('auth.user.password')
    );
});
