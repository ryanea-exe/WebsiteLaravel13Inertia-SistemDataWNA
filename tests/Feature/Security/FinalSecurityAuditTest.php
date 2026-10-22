<?php

use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\User;
use App\Models\Wna;
use App\Services\CsvSanitizer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('cross-module authorization: petugas has read-only access to layanan and rekap, but is forbidden from mutations and admin modules', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $permohonan = Permohonan::factory()->create();
    $laporan = LaporanIzinTinggal::factory()->create();
    $penerbitan = Penerbitan::factory()->create();
    $pnbp = RealisasiPnbp::factory()->create();
    $targetUser = User::factory()->create();

    // Permohonan (CR-010)
    $this->actingAs($petugas)->get('/permohonan')->assertOk();
    $this->actingAs($petugas)->get('/permohonan/'.$permohonan->id)->assertOk();
    $this->actingAs($petugas)->get('/permohonan/print-list')->assertForbidden();
    $this->actingAs($petugas)->post('/permohonan', [])->assertForbidden();
    $this->actingAs($petugas)->put('/permohonan/'.$permohonan->id, [])->assertForbidden();
    $this->actingAs($petugas)->delete('/permohonan/'.$permohonan->id)->assertForbidden();

    // Laporan Izin Tinggal (CR-010)
    $this->actingAs($petugas)->get('/laporan-izin-tinggal')->assertOk();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/'.$laporan->id)->assertOk();
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/print-list')->assertForbidden();
    $this->actingAs($petugas)->post('/laporan-izin-tinggal', [])->assertForbidden();
    $this->actingAs($petugas)->put('/laporan-izin-tinggal/'.$laporan->id, [])->assertForbidden();
    $this->actingAs($petugas)->delete('/laporan-izin-tinggal/'.$laporan->id)->assertForbidden();

    // Penerbitan (CR-010)
    $this->actingAs($petugas)->get('/penerbitan')->assertOk();
    $this->actingAs($petugas)->get('/penerbitan/'.$penerbitan->id)->assertOk();
    $this->actingAs($petugas)->get('/penerbitan/print-list')->assertForbidden();
    $this->actingAs($petugas)->post('/penerbitan', [])->assertForbidden();
    $this->actingAs($petugas)->put('/penerbitan/'.$penerbitan->id, [])->assertForbidden();
    $this->actingAs($petugas)->delete('/penerbitan/'.$penerbitan->id)->assertForbidden();

    // Realisasi PNBP (CR-010)
    $this->actingAs($petugas)->get('/realisasi-pnbp')->assertOk();
    $this->actingAs($petugas)->get('/realisasi-pnbp/'.$pnbp->id)->assertOk();
    $this->actingAs($petugas)->get('/realisasi-pnbp/print-list')->assertForbidden();
    $this->actingAs($petugas)->post('/realisasi-pnbp', [])->assertForbidden();
    $this->actingAs($petugas)->put('/realisasi-pnbp/'.$pnbp->id, [])->assertForbidden();
    $this->actingAs($petugas)->delete('/realisasi-pnbp/'.$pnbp->id)->assertForbidden();

    // Early Warning (CR-012)
    $this->actingAs($petugas)->get('/early-warning')->assertForbidden();

    // Data User
    $this->actingAs($petugas)->get('/data-user')->assertForbidden();
    $this->actingAs($petugas)->post('/data-user', [])->assertForbidden();
    $this->actingAs($petugas)->put('/data-user/'.$targetUser->id, [])->assertForbidden();
    $this->actingAs($petugas)->delete('/data-user/'.$targetUser->id)->assertForbidden();

    // Pengaturan
    $this->actingAs($petugas)->get('/pengaturan')->assertForbidden();
    $this->actingAs($petugas)->post('/pengaturan', [])->assertForbidden();
});

test('unauthenticated guests are redirected to login across all private endpoints', function () {
    $wna = Wna::factory()->create();
    $targetUser = User::factory()->create();

    $protectedUrls = [
        '/dashboard',
        '/wna',
        '/wna/'.$wna->id.'/card',
        '/wna/'.$wna->id.'/photo',
        '/abg',
        '/affidavit',
        '/early-warning',
        '/permohonan',
        '/laporan-izin-tinggal',
        '/penerbitan',
        '/realisasi-pnbp',
        '/data-user',
        '/data-user/'.$targetUser->id.'/photo',
        '/pengaturan',
        '/settings/profile',
    ];

    foreach ($protectedUrls as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});

test('path traversal and jail bypass attempts on photo endpoints are completely blocked', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // WNA with malicious path
    $wna = Wna::factory()->create(['foto' => 'wna/photos/../../etc/passwd']);
    $this->actingAs($admin)->get('/wna/'.$wna->id.'/photo')->assertNotFound();

    // ABG with null byte
    $abg = Abg::factory()->create(['foto' => "abg/photos/image.jpg\0.png"]);
    $this->actingAs($admin)->get('/abg/'.$abg->id.'/photo')->assertNotFound();

    // Affidavit with outside path
    $affidavit = Affidavit::factory()->create(['foto' => 'other/folder/secret.jpg']);
    $this->actingAs($admin)->get('/affidavit/'.$affidavit->id.'/photo')->assertNotFound();

    // User with traversal path
    $user = User::factory()->create(['foto' => 'users/photos/../secret.env']);
    $this->actingAs($admin)->get('/data-user/'.$user->id.'/photo')->assertNotFound();
});

test('csv formula injection is safely escaped across exports', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Formula injection payloads
    $dangerousNames = [
        '=1+2',
        '-5+2',
        '+cmd|',
        '@SUM(1,2)',
    ];

    foreach ($dangerousNames as $name) {
        $escaped = CsvSanitizer::sanitize($name);
        expect(str_starts_with($escaped, "'"))->toBeTrue();
    }
});

test('anti-lockout controls protect admin accounts from self-demotion and last admin deletion', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Admin cannot demote self
    $demoteSelfRes = $this->actingAs($admin)->put('/data-user/'.$admin->id, [
        'nama' => $admin->nama,
        'username' => $admin->username,
        'role' => 'user',
    ]);
    $demoteSelfRes->assertForbidden();

    // 2. Admin cannot delete self
    $deleteSelfRes = $this->actingAs($admin)->delete('/data-user/'.$admin->id);
    $deleteSelfRes->assertForbidden();

    // 3. Create second admin to test last active admin protection
    $admin2 = User::factory()->create(['role' => UserRole::Admin]);

    // With 2 admins, deleting one is permitted
    $deleteAdmin2 = $this->actingAs($admin)->delete('/data-user/'.$admin2->id);
    $deleteAdmin2->assertRedirect('/data-user');

    // Now admin is the only active admin remaining
    // Attempting to demote the last remaining admin fails with 422
    $lastDemoteRes = $this->actingAs($admin)->put('/data-user/'.$admin->id, [
        'nama' => $admin->nama,
        'username' => $admin->username,
        'role' => 'user',
    ]);
    $lastDemoteRes->assertForbidden();
});

test('inertia props on major pages strictly exclude sensitive attributes', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'password' => Hash::make('Secret123!'),
        'remember_token' => 'sensitive_token_value',
    ]);

    $pagesToAudit = [
        route('dashboard'),
        route('wna.index'),
        route('early-warning.index'),
        route('profile.edit'),
        route('pengaturan.index'),
    ];

    foreach ($pagesToAudit as $url) {
        $response = $this->actingAs($admin)->get($url);
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->missing('auth.user.password')
            ->missing('auth.user.remember_token')
            ->missing('auth.user.two_factor_secret')
            ->missing('auth.user.two_factor_recovery_codes')
        );
    }
});
