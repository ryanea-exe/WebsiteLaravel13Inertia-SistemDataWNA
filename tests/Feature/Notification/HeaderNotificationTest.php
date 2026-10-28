<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wna;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('admin receives expired wna notifications in inertia shared props', function () {
    $now = Carbon::parse('2026-10-28 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($now);

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Active WNA (expires in 10 days)
    Wna::factory()->create([
        'nama' => 'WNA Masih Berlaku',
        'masa_berlaku' => '2026-11-07',
    ]);

    // Expired WNA (expired 5 days ago)
    Wna::factory()->create([
        'nama' => 'WNA Kedaluwarsa 5 Hari',
        'masa_berlaku' => '2026-10-23',
    ]);

    // Expired WNA (expired 12 days ago)
    Wna::factory()->create([
        'nama' => 'WNA Kedaluwarsa 12 Hari',
        'masa_berlaku' => '2026-10-16',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.count', 2)
            ->has('notifications.items', 2)
            ->where('notifications.items.0.nama', 'WNA Kedaluwarsa 5 Hari')
            ->where('notifications.items.0.lewat_hari', 5)
            ->where('notifications.items.0.keterangan', 'Lewat 5 hari')
            ->where('notifications.items.1.nama', 'WNA Kedaluwarsa 12 Hari')
            ->where('notifications.items.1.lewat_hari', 12)
            ->where('notifications.items.1.keterangan', 'Lewat 12 hari')
        );

    Carbon::setTestNow();
});

test('user role (petugas) receives null notifications in inertia shared props', function () {
    $now = Carbon::parse('2026-10-28 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($now);

    $petugas = User::factory()->create(['role' => UserRole::User]);

    Wna::factory()->create([
        'nama' => 'WNA Kedaluwarsa',
        'masa_berlaku' => '2026-10-20',
    ]);

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications', null)
        );

    Carbon::setTestNow();
});
