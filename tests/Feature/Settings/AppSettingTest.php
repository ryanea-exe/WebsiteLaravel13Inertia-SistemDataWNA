<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

test('guest is redirected to login when accessing settings page or updating settings', function () {
    $this->get(route('pengaturan.index'))->assertRedirect(route('login'));
    $this->post(route('pengaturan.update'), [
        'app_name' => 'New Name',
    ])->assertRedirect(route('login'));
});

test('petugas is forbidden from viewing or modifying application settings', function () {
    $petugas = User::factory()->user()->create();

    $this->actingAs($petugas)
        ->get(route('pengaturan.index'))
        ->assertForbidden();

    $this->actingAs($petugas)
        ->post(route('pengaturan.update'), [
            'app_name' => 'Attempted Change',
        ])
        ->assertForbidden();
});

test('admin can view application settings page with fallback defaults', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('pengaturan.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('settings/App')
        ->where('settings.app_name', 'Sistem Data WNA')
        ->where('settings.instansi_name', 'Kantor Imigrasi')
        ->where('settings.app_description', 'Layanan Keimigrasian')
        ->where('settings.app_icon', null)
        ->where('settings.app_icon_url', null)
        ->where('can.update', true)
    );
});

test('admin can update application settings persistently', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Pengawasan Keimigrasian WNA',
        'instansi_name' => 'Kantor Imigrasi Kelas I TPI Jakarta Barat',
        'app_description' => 'Portal Terpadu Pengawasan WNA',
    ]);

    $response->assertRedirect(route('pengaturan.index'));
    $response->assertSessionHas('success', 'Pengaturan aplikasi berhasil disimpan.');

    expect(Setting::get('app_name'))->toBe('Sistem Pengawasan Keimigrasian WNA')
        ->and(Setting::get('instansi_name'))->toBe('Kantor Imigrasi Kelas I TPI Jakarta Barat')
        ->and(Setting::get('app_description'))->toBe('Portal Terpadu Pengawasan WNA');

    // Verify shared props in subsequent request
    $pageResponse = $this->actingAs($admin)->get(route('pengaturan.index'));
    $pageResponse->assertInertia(fn (Assert $page) => $page
        ->where('name', 'Sistem Pengawasan Keimigrasian WNA')
        ->where('app_settings.app_name', 'Sistem Pengawasan Keimigrasian WNA')
        ->where('app_settings.instansi_name', 'Kantor Imigrasi Kelas I TPI Jakarta Barat')
        ->where('app_settings.app_description', 'Portal Terpadu Pengawasan WNA')
    );
});

test('validation rejects invalid text inputs for settings', function () {
    $admin = User::factory()->admin()->create();

    // 1. Missing app_name
    $resMissing = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => '',
    ]);
    $resMissing->assertSessionHasErrors(['app_name']);

    // 2. Overly long strings
    $resLong = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => str_repeat('A', 101),
        'instansi_name' => str_repeat('B', 151),
        'app_description' => str_repeat('C', 256),
    ]);
    $resLong->assertSessionHasErrors(['app_name', 'instansi_name', 'app_description']);
});

test('admin can upload valid app icon and stream it via endpoint', function () {
    $admin = User::factory()->admin()->create();

    $iconFile = UploadedFile::fake()->image('logo.png', 128, 128);

    $response = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA',
        'app_icon' => $iconFile,
    ]);

    $response->assertRedirect(route('pengaturan.index'));
    $response->assertSessionHas('success');

    $savedPath = Setting::get('app_icon');
    expect($savedPath)->not->toBeNull();
    expect(Storage::disk('public')->exists($savedPath))->toBeTrue();

    // Test streaming endpoint
    $streamResponse = $this->get(route('settings.icon'));
    $streamResponse->assertOk();
    $streamResponse->assertHeader('Content-Type', 'image/png');
    $streamResponse->assertHeader('Cache-Control', 'max-age=86400, public');
});

test('validation rejects invalid icon files', function () {
    $admin = User::factory()->admin()->create();

    // 1. Non-image file (PDF)
    $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    $responsePdf = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA',
        'app_icon' => $pdfFile,
    ]);
    $responsePdf->assertSessionHasErrors(['app_icon']);

    // 2. Image larger than 1MB (1025 KB)
    $largeImage = UploadedFile::fake()->image('large.png')->size(1025);
    $responseLarge = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA',
        'app_icon' => $largeImage,
    ]);
    $responseLarge->assertSessionHasErrors(['app_icon']);
});

test('admin can remove icon using remove_icon flag', function () {
    $admin = User::factory()->admin()->create();

    // First upload an icon
    $iconFile = UploadedFile::fake()->image('logo.png', 100, 100);
    $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA',
        'app_icon' => $iconFile,
    ]);

    $oldPath = Setting::get('app_icon');
    expect($oldPath)->not->toBeNull();
    expect(Storage::disk('public')->exists($oldPath))->toBeTrue();

    // Now remove icon
    $removeResponse = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA',
        'remove_icon' => '1',
    ]);

    $removeResponse->assertRedirect(route('pengaturan.index'));
    expect(Setting::get('app_icon'))->toBeNull();
    expect(Storage::disk('public')->exists($oldPath))->toBeFalse();

    // Streaming endpoint returns 404 when no icon is set
    $this->get(route('settings.icon'))->assertNotFound();
});

test('icon endpoint rejects invalid or path traversal attempts', function () {
    // Attempt with empty setting
    Setting::set('app_icon', null);
    $this->get(route('settings.icon'))->assertNotFound();

    // Attempt with path traversal
    Setting::set('app_icon', 'settings/icons/../../etc/passwd');
    $this->get(route('settings.icon'))->assertNotFound();

    // Attempt with path outside settings/icons
    Setting::set('app_icon', 'photos/sample.png');
    $this->get(route('settings.icon'))->assertNotFound();
});
