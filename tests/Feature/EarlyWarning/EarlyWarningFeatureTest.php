<?php

use App\Enums\JenisIzinTinggal;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wna;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing early warning page', function () {
    $this->get('/early-warning')->assertRedirect('/login');
});

test('admin and petugas can view early warning index', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $responseAdmin = $this->actingAs($admin)->get('/early-warning');
    $responseAdmin->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('early-warning/Index')
            ->has('wna.data')
            ->has('counts')
            ->has('filters')
            ->has('kewarganegaraanOptions')
            ->has('jenisIzinTinggalOptions')
        );

    $responsePetugas = $this->actingAs($petugas)->get('/early-warning');
    $responsePetugas->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('early-warning/Index')
            ->has('wna.data')
            ->has('counts')
        );
});

test('early warning separates active warnings from expired records', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $now = Carbon::parse('2026-10-14 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($now);

    // Active warning (expires in 10 days)
    $active = Wna::factory()->create([
        'nama' => 'Active Warning Subject',
        'masa_berlaku' => '2026-10-24',
    ]);

    // Expired record (expired 5 days ago)
    $expired = Wna::factory()->create([
        'nama' => 'Expired Subject',
        'masa_berlaku' => '2026-10-09',
    ]);

    // Default visit (active warnings only)
    $resDefault = $this->actingAs($admin)->get('/early-warning');
    $resDefault->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 1)
            ->where('wna.data.0.nama', 'Active Warning Subject')
        );

    // Filter by kedaluwarsa
    $resExpired = $this->actingAs($admin)->get('/early-warning?kategori=kedaluwarsa');
    $resExpired->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 1)
            ->where('wna.data.0.nama', 'Expired Subject')
            ->where('wna.data.0.urgensi', 'kedaluwarsa')
            ->where('wna.data.0.sisa_hari', -5)
        );

    Carbon::setTestNow();
});

test('early warning boundary audit for exact 0, 14, 15, 30, 31 days and 3 calendar months', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $base = Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($base);

    // Expired yesterday (-1 day)
    $wYesterday = Wna::factory()->create(['nama' => 'Kemarin', 'masa_berlaku' => '2026-10-13']);

    // Day 0: Today (merah)
    $w0 = Wna::factory()->create(['nama' => 'Hari Ini', 'masa_berlaku' => '2026-10-14']);

    // Day 14: Merah upper bound
    $w14 = Wna::factory()->create(['nama' => 'Batas Merah 14', 'masa_berlaku' => '2026-10-28']);

    // Day 15: Kuning lower bound
    $w15 = Wna::factory()->create(['nama' => 'Mulai Kuning 15', 'masa_berlaku' => '2026-10-29']);

    // Day 30: Kuning upper bound
    $w30 = Wna::factory()->create(['nama' => 'Batas Kuning 30', 'masa_berlaku' => '2026-11-13']);

    // Day 31: Biru lower bound
    $w31 = Wna::factory()->create(['nama' => 'Mulai Biru 31', 'masa_berlaku' => '2026-11-14']);

    // Exactly 3 calendar months: Biru upper bound
    $w3M = Wna::factory()->create(['nama' => 'Batas Tiga Bulan', 'masa_berlaku' => '2027-01-14']);

    // Far future: 3 calendar months + 1 day
    $wFar = Wna::factory()->create(['nama' => 'Jauh Ke Depan', 'masa_berlaku' => '2027-01-15']);

    // 1. Check Merah category
    $resMerah = $this->actingAs($admin)->get('/early-warning?kategori=merah');
    $resMerah->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 2)
            ->where('wna.data.0.nama', 'Hari Ini')
            ->where('wna.data.1.nama', 'Batas Merah 14')
        );

    // 2. Check Kuning category
    $resKuning = $this->actingAs($admin)->get('/early-warning?kategori=kuning');
    $resKuning->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 2)
            ->where('wna.data.0.nama', 'Mulai Kuning 15')
            ->where('wna.data.1.nama', 'Batas Kuning 30')
        );

    // 3. Check Biru category
    $resBiru = $this->actingAs($admin)->get('/early-warning?kategori=biru');
    $resBiru->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 2)
            ->where('wna.data.0.nama', 'Mulai Biru 31')
            ->where('wna.data.1.nama', 'Batas Tiga Bulan')
        );

    // 4. Default / Semua Peringatan Aktif (merah + kuning + biru = 6 items, excludes yesterday & far future)
    $resAll = $this->actingAs($admin)->get('/early-warning?kategori=semua');
    $resAll->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 6)
        );

    Carbon::setTestNow();
});

test('calendar month calculations accurately handle month ends, year transitions, and leap years', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Year transition: Nov 15 2026 -> 3 months is Feb 15 2027
    $yearNow = Carbon::parse('2026-11-15 08:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($yearNow);

    $wJan = Wna::factory()->create(['nama' => 'Next Year Jan', 'masa_berlaku' => '2027-01-10']);
    $wFeb = Wna::factory()->create(['nama' => 'Next Year Feb', 'masa_berlaku' => '2027-02-15']);
    $wMar = Wna::factory()->create(['nama' => 'Next Year Mar', 'masa_berlaku' => '2027-03-01']);

    $resYear = $this->actingAs($admin)->get('/early-warning?kategori=semua');
    $resYear->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 2)
        );

    // 2. Leap year: Nov 29 2023 -> 3 months is Feb 29 2024
    $leapNow = Carbon::parse('2023-11-29 08:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($leapNow);
    Wna::query()->delete();

    $wLeapDay = Wna::factory()->create(['nama' => 'Leap Day', 'masa_berlaku' => '2024-02-29']);
    $wPostLeap = Wna::factory()->create(['nama' => 'Post Leap', 'masa_berlaku' => '2024-03-01']);

    $resLeap = $this->actingAs($admin)->get('/early-warning?kategori=semua');
    $resLeap->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 1)
            ->where('wna.data.0.nama', 'Leap Day')
        );

    Carbon::setTestNow();
});

test('calculations strictly respect Asia/Jakarta timezone', function () {
    // 2026-10-14 18:00:00 UTC is 2026-10-15 01:00:00 WIB (Next day in Jakarta!)
    $utcNight = Carbon::parse('2026-10-14 18:00:00', 'UTC');
    Carbon::setTestNow($utcNight);

    $record = Wna::factory()->create([
        'masa_berlaku' => '2026-10-14',
    ]);

    // In Asia/Jakarta it is already Oct 15, so Oct 14 has expired (-1 day)
    expect($record->sisa_hari)->toBe(-1)
        ->and($record->urgensi)->toBe('kedaluwarsa');

    Carbon::setTestNow();
});

test('early warning search, filter, and whitelisted sorting work correctly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $base = Carbon::parse('2026-10-14 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($base);

    Wna::factory()->create([
        'nama' => 'Yuki Takahashi',
        'no_paspor' => 'JPN9911',
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'masa_berlaku' => '2026-10-20',
    ]);

    Wna::factory()->create([
        'nama' => 'Kenji Sato',
        'no_paspor' => 'JPN9922',
        'kewarganegaraan' => 'Jepang',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAP,
        'masa_berlaku' => '2026-10-25',
    ]);

    Wna::factory()->create([
        'nama' => 'Hans Schmidt',
        'no_paspor' => 'DEU8811',
        'kewarganegaraan' => 'Jerman',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'masa_berlaku' => '2026-10-18',
    ]);

    // Filter by ITAS and search Takahashi
    $res = $this->actingAs($admin)->get('/early-warning?search=Yuki&jenis_izin_tinggal=ITAS');
    $res->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 1)
            ->where('wna.data.0.nama', 'Yuki Takahashi')
        );

    // Sort by masa_berlaku asc (default)
    $resSort = $this->actingAs($admin)->get('/early-warning?sort_by=masa_berlaku&sort_order=asc');
    $resSort->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 3)
            ->where('wna.data.0.nama', 'Hans Schmidt') // Oct 18 is earliest
            ->where('wna.data.1.nama', 'Yuki Takahashi') // Oct 20
            ->where('wna.data.2.nama', 'Kenji Sato') // Oct 25
        );

    Carbon::setTestNow();
});

test('summary counts match actual database records per category', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $base = Carbon::parse('2026-10-14 10:00:00', 'Asia/Jakarta');
    Carbon::setTestNow($base);

    // 2 Merah (0-14 days)
    Wna::factory()->create(['masa_berlaku' => '2026-10-15']);
    Wna::factory()->create(['masa_berlaku' => '2026-10-20']);

    // 1 Kuning (15-30 days)
    Wna::factory()->create(['masa_berlaku' => '2026-11-05']);

    // 3 Biru (>30 days - 3 months)
    Wna::factory()->create(['masa_berlaku' => '2026-11-20']);
    Wna::factory()->create(['masa_berlaku' => '2026-12-01']);
    Wna::factory()->create(['masa_berlaku' => '2027-01-10']);

    // 2 Kedaluwarsa (<0 days)
    Wna::factory()->create(['masa_berlaku' => '2026-10-10']);
    Wna::factory()->create(['masa_berlaku' => '2026-09-01']);

    $response = $this->actingAs($admin)->get('/early-warning');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('counts.semua', 6)
            ->where('counts.merah', 2)
            ->where('counts.kuning', 1)
            ->where('counts.biru', 3)
            ->where('counts.kedaluwarsa', 2)
        );

    Carbon::setTestNow();
});

test('invalid query parameters are safely rejected by validation', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Invalid category
    $this->actingAs($admin)->get('/early-warning?kategori=hacked_category')
        ->assertSessionHasErrors(['kategori']);

    // Invalid sort_by column
    $this->actingAs($admin)->get('/early-warning?sort_by=password')
        ->assertSessionHasErrors(['sort_by']);

    // Invalid sort_order
    $this->actingAs($admin)->get('/early-warning?sort_order=sideways')
        ->assertSessionHasErrors(['sort_order']);
});

test('empty state is handled gracefully when no records exist', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get('/early-warning');
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('wna.total', 0)
            ->where('counts.semua', 0)
            ->where('counts.merah', 0)
            ->where('counts.kuning', 0)
            ->where('counts.biru', 0)
            ->where('counts.kedaluwarsa', 0)
        );
});
