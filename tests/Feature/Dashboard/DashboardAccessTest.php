<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated admin can view dashboard', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin_tester',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('auth.user.username', 'admin_tester')
        ->where('auth.user.role', 'admin')
    );
});

test('authenticated petugas can view dashboard', function () {
    $petugas = User::factory()->user()->create([
        'username' => 'petugas_tester',
    ]);

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('auth.user.username', 'petugas_tester')
        ->where('auth.user.role', 'user')
    );
});

test('guest is redirected to login when visiting dashboard', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('logout is performed strictly via POST method and terminates session', function () {
    $user = User::factory()->create([
        'username' => 'user_logout_test',
    ]);

    // GET request to logout should not be allowed
    $getResponse = $this->actingAs($user)->get(route('logout'));
    $getResponse->assertStatus(405);

    // POST request cleanly logs out user
    $postResponse = $this->actingAs($user)->post(route('logout'));
    $this->assertGuest();
    $postResponse->assertRedirect(route('home'));
});
