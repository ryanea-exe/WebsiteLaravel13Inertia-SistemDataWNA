<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $user = User::factory()->create();

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('dashboard includes pending invitations for the authenticated user', function () {
    $this->markTestSkipped('Team invitations are not used on dashboard in Sistem Data WNA.');
});

test('dashboard does not include accepted invitations', function () {
    $this->markTestSkipped('Team invitations are not used on dashboard in Sistem Data WNA.');
});

test('dashboard does not include expired invitations and does not delete them', function () {
    $this->markTestSkipped('Team invitations are not used on dashboard in Sistem Data WNA.');
});

test('dashboard does not include or delete other users invitations', function () {
    $this->markTestSkipped('Team invitations are not used on dashboard in Sistem Data WNA.');
});
