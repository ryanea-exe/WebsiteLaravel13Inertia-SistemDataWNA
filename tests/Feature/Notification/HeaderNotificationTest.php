<?php

use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\User;
use App\Models\Wna;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('admin receives expired wna and abg notifications in inertia shared props', function () {
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

    // ABG young (10 yo, not expired)
    Abg::factory()->create([
        'nama' => 'ABG Anak Kecil',
        'tanggal_lahir' => '2016-10-28',
    ]);

    // ABG approaching 18 (17y 9m)
    Abg::factory()->create([
        'nama' => 'ABG Menjelang 18',
        'tanggal_lahir' => '2009-01-28',
    ]);

    // ABG expired (21 years and 10 days old -> DOB = 2005-10-18)
    Abg::factory()->create([
        'nama' => 'ABG Lewat 21 Tahun',
        'tanggal_lahir' => '2005-10-18',
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
            ->where('notifications_abg.count', 1)
            ->has('notifications_abg.items', 1)
            ->where('notifications_abg.items.0.nama', 'ABG Lewat 21 Tahun')
            ->where('notifications_abg.items.0.lewat_hari', 10)
            ->where('notifications_abg.items.0.keterangan', 'Lewat 10 hari')
            ->where('notifications_abg.items.0.umur_detail', '21 tahun 10 hari')
        );

    Carbon::setTestNow();
});

test('user role (petugas) receives null notifications and notifications_abg in inertia shared props', function () {
    $now = Carbon::parse('2026-10-28 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($now);

    $petugas = User::factory()->create(['role' => UserRole::User]);

    Wna::factory()->create([
        'nama' => 'WNA Kedaluwarsa',
        'masa_berlaku' => '2026-10-20',
    ]);

    Abg::factory()->create([
        'nama' => 'ABG Lewat 21',
        'tanggal_lahir' => '2004-01-01',
    ]);

    $response = $this->actingAs($petugas)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications', null)
            ->where('notifications_abg', null)
        );

    Carbon::setTestNow();
});
