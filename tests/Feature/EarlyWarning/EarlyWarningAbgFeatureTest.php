<?php

use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing early warning abg', function () {
    $this->get('/early-warning/abg')->assertRedirect('/login');
});

test('admin can view early warning abg index while petugas is forbidden', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $responseAdmin = $this->actingAs($admin)->get('/early-warning/abg');
    $responseAdmin->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('early-warning-abg/Index')
            ->has('abg.data')
            ->has('counts')
            ->has('filters')
            ->has('kewarganegaraanOptions')
            ->has('kabupatenOptions')
        );

    $this->actingAs($petugas)->get('/early-warning/abg')->assertForbidden();
});

test('early warning abg filters by category correctly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $now = Carbon::parse('2026-11-23 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($now);

    // Subject 1: Approaching 18 (17y 9m old -> DOB 2009-02-01)
    $sub18 = Abg::factory()->create([
        'nama' => 'ABG Menjelang 18 Tahun',
        'tanggal_lahir' => '2009-02-01',
    ]);

    // Subject 2: Approaching 21 (20y 9m old -> DOB 2006-02-01)
    $sub21 = Abg::factory()->create([
        'nama' => 'ABG Menjelang 21 Tahun',
        'tanggal_lahir' => '2006-02-01',
    ]);

    // Subject 3: Past 21 / Expired (22y old -> DOB 2004-11-20)
    $expired = Abg::factory()->create([
        'nama' => 'ABG Kedaluwarsa Lewat 21',
        'tanggal_lahir' => '2004-11-20',
    ]);

    // Subject 4: Normal age child (10y old -> DOB 2016-01-01), should NOT appear
    $normalChild = Abg::factory()->create([
        'nama' => 'Anak Kecil Normal',
        'tanggal_lahir' => '2016-01-01',
    ]);

    // Subject 5: Normal young adult (19y old -> DOB 2007-06-01), should NOT appear
    $youngAdult = Abg::factory()->create([
        'nama' => 'Pemuda Normal 19 Tahun',
        'tanggal_lahir' => '2007-06-01',
    ]);

    // Default visit (all 3 warning categories)
    $resDefault = $this->actingAs($admin)->get('/early-warning/abg');
    $resDefault->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('abg.total', 3)
            ->where('counts.semua', 3)
            ->where('counts.kurang_18', 1)
            ->where('counts.kurang_21', 1)
            ->where('counts.lewat_21', 1)
        );

    // Filter kurang_18
    $res18 = $this->actingAs($admin)->get('/early-warning/abg?kategori=kurang_18');
    $res18->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('abg.total', 1)
            ->where('abg.data.0.nama', 'ABG Menjelang 18 Tahun')
            ->where('abg.data.0.urgensi', 'kurang_18')
            ->where('abg.data.0.umur', '17 tahun 295 hari')
        );

    // Filter kurang_21
    $res21 = $this->actingAs($admin)->get('/early-warning/abg?kategori=kurang_21');
    $res21->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('abg.total', 1)
            ->where('abg.data.0.nama', 'ABG Menjelang 21 Tahun')
            ->where('abg.data.0.urgensi', 'kurang_21')
        );

    // Filter lewat_21
    $resExpired = $this->actingAs($admin)->get('/early-warning/abg?kategori=lewat_21');
    $resExpired->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('abg.total', 1)
            ->where('abg.data.0.nama', 'ABG Kedaluwarsa Lewat 21')
            ->where('abg.data.0.urgensi', 'lewat_21')
        );

    Carbon::setTestNow();
});

test('early warning abg search filters correctly by name and register number', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $now = Carbon::parse('2026-11-23 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($now);

    Abg::factory()->create([
        'nama' => 'Dewi Sandra ABG',
        'no_register' => 'REG-ABG-99901',
        'tanggal_lahir' => '2009-02-01',
    ]);

    Abg::factory()->create([
        'nama' => 'Budi Santoso ABG',
        'no_register' => 'REG-ABG-99902',
        'tanggal_lahir' => '2006-02-01',
    ]);

    $res = $this->actingAs($admin)->get('/early-warning/abg?search=Dewi');
    $res->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('abg.total', 1)
            ->where('abg.data.0.nama', 'Dewi Sandra ABG')
        );

    $resReg = $this->actingAs($admin)->get('/early-warning/abg?search=99902');
    $resReg->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('abg.total', 1)
            ->where('abg.data.0.no_register', 'REG-ABG-99902')
        );

    Carbon::setTestNow();
});

test('admin can view early warning abg print list', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $resAdmin = $this->actingAs($admin)->get('/early-warning/abg/print-list');
    $resAdmin->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('early-warning-abg/PrintList')
            ->has('abg')
            ->has('counts')
            ->has('filters')
        );

    $this->actingAs($petugas)->get('/early-warning/abg/print-list')->assertForbidden();
});
