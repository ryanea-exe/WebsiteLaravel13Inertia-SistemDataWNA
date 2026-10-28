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
use Inertia\Testing\AssertableInertia as Assert;

test('role user can access print-list and export-pdf endpoints for the 4 covered modules (CR-011)', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $permohonan = Permohonan::factory()->create(['nama' => 'John Permohonan']);
    $laporan = LaporanIzinTinggal::factory()->create();
    $penerbitan = Penerbitan::factory()->create(['nama' => 'John Penerbitan']);
    $pnbp = RealisasiPnbp::factory()->create();

    // 1. Permohonan
    $this->actingAs($petugas)
        ->get('/permohonan')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/permohonan/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/PrintList')
            ->has('permohonanList')
        );

    $this->actingAs($petugas)
        ->get('/permohonan/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('permohonan/PrintList')
        );

    // 2. Laporan Izin Tinggal
    $this->actingAs($petugas)
        ->get('/laporan-izin-tinggal')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/laporan-izin-tinggal/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/PrintList')
            ->has('laporanList')
        );

    $this->actingAs($petugas)
        ->get('/laporan-izin-tinggal/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('laporan-izin-tinggal/PrintList')
        );

    // 3. Penerbitan
    $this->actingAs($petugas)
        ->get('/penerbitan')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/penerbitan/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/PrintList')
            ->has('penerbitanList')
        );

    $this->actingAs($petugas)
        ->get('/penerbitan/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('penerbitan/PrintList')
        );

    // 4. Realisasi PNBP
    $this->actingAs($petugas)
        ->get('/realisasi-pnbp')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/realisasi-pnbp/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/PrintList')
            ->has('realisasiList')
        );

    $this->actingAs($petugas)
        ->get('/realisasi-pnbp/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('realisasi-pnbp/PrintList')
        );
});

test('mutations remain strictly forbidden for role user on all 4 modules', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $permohonan = Permohonan::factory()->create();
    $laporan = LaporanIzinTinggal::factory()->create();
    $penerbitan = Penerbitan::factory()->create();
    $pnbp = RealisasiPnbp::factory()->create();

    // Permohonan mutations
    $this->actingAs($petugas)->get('/permohonan/create')->assertForbidden();
    $this->actingAs($petugas)->post('/permohonan', [])->assertForbidden();
    $this->actingAs($petugas)->get("/permohonan/{$permohonan->id}/edit")->assertForbidden();
    $this->actingAs($petugas)->put("/permohonan/{$permohonan->id}", [])->assertForbidden();
    $this->actingAs($petugas)->delete("/permohonan/{$permohonan->id}")->assertForbidden();

    // Laporan mutations
    $this->actingAs($petugas)->get('/laporan-izin-tinggal/create')->assertForbidden();
    $this->actingAs($petugas)->post('/laporan-izin-tinggal', [])->assertForbidden();
    $this->actingAs($petugas)->get("/laporan-izin-tinggal/{$laporan->id}/edit")->assertForbidden();
    $this->actingAs($petugas)->put("/laporan-izin-tinggal/{$laporan->id}", [])->assertForbidden();
    $this->actingAs($petugas)->delete("/laporan-izin-tinggal/{$laporan->id}")->assertForbidden();

    // Penerbitan mutations
    $this->actingAs($petugas)->get('/penerbitan/create')->assertForbidden();
    $this->actingAs($petugas)->post('/penerbitan', [])->assertForbidden();
    $this->actingAs($petugas)->get("/penerbitan/{$penerbitan->id}/edit")->assertForbidden();
    $this->actingAs($petugas)->put("/penerbitan/{$penerbitan->id}", [])->assertForbidden();
    $this->actingAs($petugas)->delete("/penerbitan/{$penerbitan->id}")->assertForbidden();

    // Realisasi PNBP mutations
    $this->actingAs($petugas)->get('/realisasi-pnbp/create')->assertForbidden();
    $this->actingAs($petugas)->post('/realisasi-pnbp', [])->assertForbidden();
    $this->actingAs($petugas)->get("/realisasi-pnbp/{$pnbp->id}/edit")->assertForbidden();
    $this->actingAs($petugas)->put("/realisasi-pnbp/{$pnbp->id}", [])->assertForbidden();
    $this->actingAs($petugas)->delete("/realisasi-pnbp/{$pnbp->id}")->assertForbidden();
});

test('dashboard has no print-list or export-pdf endpoint and is excluded from CR-011', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($petugas)->get('/dashboard/print-list')->assertNotFound();
    $this->actingAs($petugas)->get('/dashboard/export-pdf')->assertNotFound();
});

test('unauthenticated users are redirected to login for print-list and export-pdf endpoints', function () {
    $this->get('/permohonan/print-list')->assertRedirect('/login');
    $this->get('/permohonan/export-pdf')->assertRedirect('/login');

    $this->get('/laporan-izin-tinggal/print-list')->assertRedirect('/login');
    $this->get('/laporan-izin-tinggal/export-pdf')->assertRedirect('/login');

    $this->get('/penerbitan/print-list')->assertRedirect('/login');
    $this->get('/penerbitan/export-pdf')->assertRedirect('/login');

    $this->get('/realisasi-pnbp/print-list')->assertRedirect('/login');
    $this->get('/realisasi-pnbp/export-pdf')->assertRedirect('/login');
});

test('role user can access print-list and export-pdf endpoints for WNA, ABG, and Affidavit', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    Wna::factory()->create(['nama' => 'John WNA']);
    Abg::factory()->create(['nama' => 'John ABG']);
    Affidavit::factory()->create(['nama' => 'John Affidavit']);

    // 1. Data WNA
    $this->actingAs($petugas)
        ->get('/wna')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/wna/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/PrintList')
            ->has('wnaList')
        );

    $this->actingAs($petugas)
        ->get('/wna/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('wna/PrintList')
        );

    // 2. Pendaftaran ABG
    $this->actingAs($petugas)
        ->get('/abg')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/abg/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/PrintList')
            ->has('abgList')
        );

    $this->actingAs($petugas)
        ->get('/abg/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('abg/PrintList')
        );

    // 3. Pendaftaran Affidavit
    $this->actingAs($petugas)
        ->get('/affidavit')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/Index')
            ->where('can.printList', true)
            ->where('can.create', false)
        );

    $this->actingAs($petugas)
        ->get('/affidavit/print-list')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/PrintList')
            ->has('affidavitList')
        );

    $this->actingAs($petugas)
        ->get('/affidavit/export-pdf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('affidavit/PrintList')
        );
});
