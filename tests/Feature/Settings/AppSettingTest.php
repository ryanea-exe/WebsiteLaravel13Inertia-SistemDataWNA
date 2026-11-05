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
        ->where('settings.app_name', 'Sistem Data WNA Kanim Ponorogo')
        ->where('settings.instansi_name', 'Kantor Imigrasi Kelas II Non TPI Ponorogo')
        ->where('settings.app_description', 'Sistem Informasi Data Warga Negara Asing')
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
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
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
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
        'app_icon' => $pdfFile,
    ]);
    $responsePdf->assertSessionHasErrors(['app_icon']);

    // 2. Image larger than 1MB (1025 KB)
    $largeImage = UploadedFile::fake()->image('large.png')->size(1025);
    $responseLarge = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
        'app_icon' => $largeImage,
    ]);
    $responseLarge->assertSessionHasErrors(['app_icon']);
});

test('admin can remove icon using remove_icon flag', function () {
    $admin = User::factory()->admin()->create();

    // First upload an icon
    $iconFile = UploadedFile::fake()->image('logo.png', 100, 100);
    $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
        'app_icon' => $iconFile,
    ]);

    $oldPath = Setting::get('app_icon');
    expect($oldPath)->not->toBeNull();
    expect(Storage::disk('public')->exists($oldPath))->toBeTrue();

    // Now remove icon
    $removeResponse = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
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

test('settings route redirects admin to pengaturan and regular user to profile', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/settings')
        ->assertRedirect(route('pengaturan.index'));

    $petugas = User::factory()->user()->create();
    $this->actingAs($petugas)
        ->get('/settings')
        ->assertRedirect(route('profile.edit'));
});

test('admin can upload all three logo slots and select a primary logo', function () {
    $admin = User::factory()->admin()->create();

    $logo1 = UploadedFile::fake()->image('logo1.png', 100, 100);
    $logo2 = UploadedFile::fake()->image('logo2.png', 120, 120);
    $logo3 = UploadedFile::fake()->image('logo3.png', 140, 140);

    $response = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
        'app_logo_1' => $logo1,
        'app_logo_2' => $logo2,
        'app_logo_3' => $logo3,
        'primary_logo' => 2,
    ]);

    $response->assertRedirect(route('pengaturan.index'));
    $response->assertSessionHas('success');

    $path1 = Setting::getLogoPath(1);
    $path2 = Setting::getLogoPath(2);
    $path3 = Setting::getLogoPath(3);

    expect($path1)->not->toBeNull()
        ->and($path2)->not->toBeNull()
        ->and($path3)->not->toBeNull();

    expect(Storage::disk('public')->exists($path1))->toBeTrue()
        ->and(Storage::disk('public')->exists($path2))->toBeTrue()
        ->and(Storage::disk('public')->exists($path3))->toBeTrue();

    expect(Setting::getPrimaryLogoSlot())->toBe(2);
    expect(Setting::getPrimaryLogoPath())->toBe($path2);

    $availableLogos = Setting::getAvailableLogos();
    expect($availableLogos)->toHaveCount(3);
    expect($availableLogos[0]['slot'])->toBe(1);
    expect($availableLogos[1]['slot'])->toBe(2);
    expect($availableLogos[1]['is_primary'])->toBeTrue();
    expect($availableLogos[2]['slot'])->toBe(3);

    // Primary icon endpoint serves slot 2
    $this->get(route('settings.icon'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    // Slot specific icon endpoints serve respective logos
    $this->get(route('settings.icon', ['slot' => 1]))
        ->assertOk();
    $this->get(route('settings.icon', ['slot' => 3]))
        ->assertOk();
});

test('admin can remove a specific logo slot without affecting other slots', function () {
    $admin = User::factory()->admin()->create();

    $logo1 = UploadedFile::fake()->image('logo1.png', 80, 80);
    $logo2 = UploadedFile::fake()->image('logo2.png', 80, 80);
    $logo3 = UploadedFile::fake()->image('logo3.png', 80, 80);

    $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
        'app_logo_1' => $logo1,
        'app_logo_2' => $logo2,
        'app_logo_3' => $logo3,
        'primary_logo' => 1,
    ]);

    $path2 = Setting::getLogoPath(2);
    expect(Storage::disk('public')->exists($path2))->toBeTrue();

    // Remove slot 2 only
    $response = $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'Sistem Data WNA Kanim Ponorogo',
        'remove_logo_2' => '1',
    ]);

    $response->assertRedirect(route('pengaturan.index'));

    expect(Setting::getLogoPath(2))->toBeNull();
    expect(Storage::disk('public')->exists($path2))->toBeFalse();

    // Slot 1 and Slot 3 remain intact
    expect(Setting::getLogoPath(1))->not->toBeNull();
    expect(Setting::getLogoPath(3))->not->toBeNull();

    $available = Setting::getAvailableLogos();
    expect($available)->toHaveCount(2);
    expect(array_column($available, 'slot'))->toBe([1, 3]);

    // Slot 2 stream endpoint returns 404
    $this->get(route('settings.icon', ['slot' => 2]))->assertNotFound();
});

test('empty slot cannot be set as primary logo', function () {
    $admin = User::factory()->admin()->create();

    $logo1 = UploadedFile::fake()->image('logo1.png', 80, 80);

    // Upload only slot 1, but attempt to set slot 2 as primary
    $this->actingAs($admin)->post(route('pengaturan.update'), [
        'app_name' => 'SIDAWA',
        'app_logo_1' => $logo1,
        'primary_logo' => 2,
    ]);

    // Slot 2 has no logo, so primary_logo should remain 1
    expect(Setting::getPrimaryLogoSlot())->toBe(1);
    expect(Setting::get('app_name'))->toBe('SIDAWA');
});
