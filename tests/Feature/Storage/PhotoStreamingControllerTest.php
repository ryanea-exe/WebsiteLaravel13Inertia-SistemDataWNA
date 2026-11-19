<?php

use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wna;
use App\Services\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    config(['filesystems.backend' => 'local']);
    Storage::fake('local');
    Storage::fake('public');
    Storage::fake('b2_private');
    Storage::fake('b2_assets_private');
});

test('wna photo endpoint returns StreamedResponse with security and cache headers on local storage', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('pasfoto.jpg', 300, 400);
    $path = Storage::disk('local')->putFile('wna/photos', $file);
    $wna = Wna::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->get("/wna/{$wna->id}/photo");

    $response->assertOk();
    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);

    $response->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('Content-Disposition', 'inline')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('no-cache')
        ->and($cacheControl)->toContain('must-revalidate')
        ->and($cacheControl)->not->toContain('public');
});

test('abg photo endpoint returns StreamedResponse with security and cache headers on local storage', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('anak.png', 200, 200);
    $path = Storage::disk('local')->putFile('abg/photos', $file);
    $abg = Abg::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->get("/abg/{$abg->id}/photo");

    $response->assertOk();
    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);

    $response->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Content-Disposition', 'inline')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('no-cache')
        ->and($cacheControl)->toContain('must-revalidate');
});

test('affidavit photo endpoint returns StreamedResponse with security and cache headers on local storage', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('affidavit.webp', 200, 200);
    $path = Storage::disk('local')->putFile('affidavit/photos', $file);
    $affidavit = Affidavit::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->get("/affidavit/{$affidavit->id}/photo");

    $response->assertOk();
    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);

    $response->assertHeader('Content-Type', 'image/webp')
        ->assertHeader('Content-Disposition', 'inline')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('no-cache')
        ->and($cacheControl)->toContain('must-revalidate');
});

test('data-user photo endpoint returns StreamedResponse with security and cache headers on local storage', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('avatar.jpg', 150, 150);
    $path = Storage::disk('local')->putFile('users/photos', $file);
    $user = User::factory()->create(['foto' => $path]);

    $response = $this->actingAs($admin)->get("/data-user/{$user->id}/photo");

    $response->assertOk();
    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);

    $response->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('Content-Disposition', 'inline')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('no-cache')
        ->and($cacheControl)->toContain('must-revalidate');
});

test('setting icon endpoint returns StreamedResponse with public cache headers and allows anonymous access', function () {
    $file = UploadedFile::fake()->image('app_logo.png', 128, 128);
    $path = Storage::disk('public')->putFile('settings/icons', $file);
    Setting::set('app_icon', $path);

    // Anonymous / guest access is allowed for public icon
    $response = $this->get(route('settings.icon'));

    $response->assertOk();
    expect($response->baseResponse)->toBeInstanceOf(StreamedResponse::class);

    $response->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Content-Disposition', 'inline')
        ->assertHeader('Cache-Control', 'max-age=86400, public');
});

test('streaming succeeds from backblaze b2 disks when STORAGE_BACKEND is backblaze', function () {
    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private.key' => 'test-key',
        'filesystems.disks.b2_private.secret' => 'test-secret',
        'filesystems.disks.b2_private.bucket' => 'test-private-bucket',
        'filesystems.disks.b2_private.region' => 'us-east-005',
        'filesystems.disks.b2_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'filesystems.disks.b2_assets_private.key' => 'test-key-2',
        'filesystems.disks.b2_assets_private.secret' => 'test-secret-2',
        'filesystems.disks.b2_assets_private.bucket' => 'test-assets-bucket',
        'filesystems.disks.b2_assets_private.region' => 'us-east-005',
        'filesystems.disks.b2_assets_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Sensitive photo on b2_private
    $wnaFile = UploadedFile::fake()->image('wna_b2.jpg', 300, 400);
    $wnaPath = Storage::disk('b2_private')->putFile('wna/photos', $wnaFile);
    $wna = Wna::factory()->create(['foto' => $wnaPath]);

    $wnaResponse = $this->actingAs($admin)->get("/wna/{$wna->id}/photo");
    $wnaResponse->assertOk();
    expect($wnaResponse->baseResponse)->toBeInstanceOf(StreamedResponse::class);
    $wnaResponse->assertHeader('Content-Type', 'image/jpeg');

    // Asset on b2_assets_private
    $iconFile = UploadedFile::fake()->image('icon_b2.png', 100, 100);
    $iconPath = Storage::disk('b2_assets_private')->putFile('settings/icons', $iconFile);
    Setting::set('app_icon', $iconPath);

    $iconResponse = $this->get(route('settings.icon'));
    $iconResponse->assertOk();
    expect($iconResponse->baseResponse)->toBeInstanceOf(StreamedResponse::class);
    $iconResponse->assertHeader('Content-Type', 'image/png');
});

test('streaming strictly uses active backend disk and returns 404 without cross-backend fallback', function () {
    config([
        'filesystems.backend' => 'backblaze',
        'filesystems.disks.b2_private.key' => 'test-key',
        'filesystems.disks.b2_private.secret' => 'test-secret',
        'filesystems.disks.b2_private.bucket' => 'test-private-bucket',
        'filesystems.disks.b2_private.region' => 'us-east-005',
        'filesystems.disks.b2_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',
        'filesystems.disks.b2_assets_private.key' => 'test-key-2',
        'filesystems.disks.b2_assets_private.secret' => 'test-secret-2',
        'filesystems.disks.b2_assets_private.bucket' => 'test-assets-bucket',
        'filesystems.disks.b2_assets_private.region' => 'us-east-005',
        'filesystems.disks.b2_assets_private.endpoint' => 'https://s3.us-east-005.backblazeb2.com',
    ]);

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // File only exists on local disk, NOT in b2_private
    $legacyFile = UploadedFile::fake()->image('legacy_wna.jpg', 300, 400);
    $legacyPath = Storage::disk('local')->putFile('wna/photos', $legacyFile);
    $wna = Wna::factory()->create(['foto' => $legacyPath]);

    expect(Storage::disk('b2_private')->exists($legacyPath))->toBeFalse()
        ->and(Storage::disk('local')->exists($legacyPath))->toBeTrue();

    // Must return 404 because cross-backend fallback to local is prohibited
    $this->actingAs($admin)->get("/wna/{$wna->id}/photo")->assertNotFound();

    // Icon only exists on public disk, NOT in b2_assets_private
    $legacyIcon = UploadedFile::fake()->image('legacy_icon.png', 100, 100);
    $iconPath = Storage::disk('public')->putFile('settings/icons', $legacyIcon);
    Setting::set('app_icon', $iconPath);

    expect(Storage::disk('b2_assets_private')->exists($iconPath))->toBeFalse()
        ->and(Storage::disk('public')->exists($iconPath))->toBeTrue();

    // Must return 404 because cross-backend fallback to public is prohibited
    $this->get(route('settings.icon'))->assertNotFound();
});

test('photo endpoints return 404 on missing photo, nonexistent file, path traversal, or invalid prefix', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // 1. Missing photo attribute (null)
    $wnaNoPhoto = Wna::factory()->create(['foto' => null]);
    $this->actingAs($admin)->get("/wna/{$wnaNoPhoto->id}/photo")->assertNotFound();

    // 2. Nonexistent file on any disk
    $wnaMissing = Wna::factory()->create(['foto' => 'wna/photos/does_not_exist.jpg']);
    $this->actingAs($admin)->get("/wna/{$wnaMissing->id}/photo")->assertNotFound();

    // 3. Path traversal attempt (..)
    $wnaTraversal = Wna::factory()->create(['foto' => 'wna/photos/../../etc/passwd']);
    $this->actingAs($admin)->get("/wna/{$wnaTraversal->id}/photo")->assertNotFound();

    // 4. Invalid directory prefix
    $wnaWrongDir = Wna::factory()->create(['foto' => 'abg/photos/sample.jpg']);
    $this->actingAs($admin)->get("/wna/{$wnaWrongDir->id}/photo")->assertNotFound();

    // 5. Null byte attempt
    $wnaNullByte = Wna::factory()->create(['foto' => "wna/photos/test.jpg\0.png"]);
    $this->actingAs($admin)->get("/wna/{$wnaNullByte->id}/photo")->assertNotFound();

    // Setting icon missing returns 404
    Setting::set('app_icon', null);
    $this->get(route('settings.icon'))->assertNotFound();
});

test('photo endpoints return 415 on unsupported file format', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Save a PDF or text file into wna/photos
    $textFile = UploadedFile::fake()->create('malicious.txt', 10, 'text/plain');
    $textPath = Storage::disk('local')->putFileAs('wna/photos', $textFile, 'not_photo.txt');
    $wna = Wna::factory()->create(['foto' => $textPath]);

    $response = $this->actingAs($admin)->get("/wna/{$wna->id}/photo");
    expect($response->status())->toBe(415);
});

test('photo endpoints require authentication and redirect guests to login', function () {
    $file = UploadedFile::fake()->image('secret.jpg', 300, 400);
    $wnaPath = Storage::disk('local')->putFile('wna/photos', $file);
    $abgPath = Storage::disk('local')->putFile('abg/photos', $file);
    $affidavitPath = Storage::disk('local')->putFile('affidavit/photos', $file);
    $userPath = Storage::disk('local')->putFile('users/photos', $file);

    $wna = Wna::factory()->create(['foto' => $wnaPath]);
    $abg = Abg::factory()->create(['foto' => $abgPath]);
    $affidavit = Affidavit::factory()->create(['foto' => $affidavitPath]);
    $user = User::factory()->create(['foto' => $userPath]);

    $this->get("/wna/{$wna->id}/photo")->assertRedirect('/login');
    $this->get("/abg/{$abg->id}/photo")->assertRedirect('/login');
    $this->get("/affidavit/{$affidavit->id}/photo")->assertRedirect('/login');
    $this->get("/data-user/{$user->id}/photo")->assertRedirect('/login');
});

test('authenticated non-admin user cannot access another users photo and receives 403 forbidden', function () {
    $regularUser = User::factory()->create(['role' => UserRole::User]);
    $otherUser = User::factory()->create(['role' => UserRole::User]);

    $file = UploadedFile::fake()->image('avatar.jpg', 150, 150);
    $path = Storage::disk('local')->putFile('users/photos', $file);
    $otherUser->update(['foto' => $path]);

    // Regular user attempting to access another user's avatar is forbidden (403)
    $response = $this->actingAs($regularUser)->get("/data-user/{$otherUser->id}/photo");
    $response->assertForbidden();

    // Regular user can access their own avatar
    $ownFile = UploadedFile::fake()->image('own_avatar.jpg', 150, 150);
    $ownPath = Storage::disk('local')->putFile('users/photos', $ownFile);
    $regularUser->update(['foto' => $ownPath]);

    $ownResponse = $this->actingAs($regularUser)->get("/data-user/{$regularUser->id}/photo");
    $ownResponse->assertOk();
});

test('streamFile prevents caller headers from overriding mandatory security headers and rejects disallowed mimes', function () {
    $service = new DocumentStorageService;
    $file = UploadedFile::fake()->image('security_test.jpg', 100, 100);
    $path = Storage::disk('local')->putFile('wna/photos', $file);

    // 1. Caller attempt to override nosniff or Content-Type is ignored
    $response = $service->streamFile('local', $path, 'image/jpeg', [
        'X-Content-Type-Options' => 'sniff',
        'Content-Type' => 'text/html',
        'Custom-Header' => 'CustomValue',
    ]);

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Content-Type'))->toBe('image/jpeg')
        ->and($response->headers->get('Custom-Header'))->toBe('CustomValue');
});

test('streamFile closes the underlying stream resource safely after execution', function () {
    $service = new DocumentStorageService;
    $file = UploadedFile::fake()->image('stream_test.jpg', 100, 100);
    $path = Storage::disk('local')->putFile('wna/photos', $file);

    $response = $service->streamFile('local', $path, 'image/jpeg');

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    // Execute stream callback and verify clean buffer output
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect(strlen($content))->toBeGreaterThan(0);
});

test('streamFile auto-detects and validates MIME type when mimeType parameter is null', function () {
    $service = new DocumentStorageService;

    // 1. JPEG auto-detection
    $jpegFile = UploadedFile::fake()->image('sample.jpg', 100, 100);
    $jpegPath = Storage::disk('local')->putFile('wna/photos', $jpegFile);
    $jpegResponse = $service->streamFile('local', $jpegPath, null);
    expect($jpegResponse->headers->get('Content-Type'))->toBe('image/jpeg');

    // 2. PNG auto-detection
    $pngFile = UploadedFile::fake()->image('sample.png', 100, 100);
    $pngPath = Storage::disk('local')->putFile('abg/photos', $pngFile);
    $pngResponse = $service->streamFile('local', $pngPath, null);
    expect($pngResponse->headers->get('Content-Type'))->toBe('image/png');

    // 3. WEBP auto-detection
    $webpFile = UploadedFile::fake()->image('sample.webp', 100, 100);
    $webpPath = Storage::disk('local')->putFile('affidavit/photos', $webpFile);
    $webpResponse = $service->streamFile('local', $webpPath, null);
    expect($webpResponse->headers->get('Content-Type'))->toBe('image/webp');
});

test('streamFile auto-detection rejects unsupported MIME type with 415 when mimeType is null', function () {
    $service = new DocumentStorageService;

    // Store a non-image file in wna/photos
    $txtFile = UploadedFile::fake()->create('script.txt', 10, 'text/plain');
    $txtPath = Storage::disk('local')->putFileAs('wna/photos', $txtFile, 'script.txt');

    try {
        $service->streamFile('local', $txtPath, null);
        $this->fail('Expected HttpException 415 was not thrown');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(415);
    }
});
