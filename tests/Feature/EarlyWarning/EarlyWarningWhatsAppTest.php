<?php

use App\Enums\JenisIzinTinggal;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wna;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('phone number normalization handles local, international, and invalid inputs safely', function () {
    // Indonesian format with leading 0
    expect(Wna::normalizePhoneNumber('081234567890'))->toBe('6281234567890')
        ->and(Wna::normalizePhoneNumber('085712345678'))->toBe('6285712345678');

    // Numbers with leading '+'
    expect(Wna::normalizePhoneNumber('+6281234567890'))->toBe('6281234567890')
        ->and(Wna::normalizePhoneNumber('+17208678540'))->toBe('17208678540');

    // Numbers with formatting symbols (spaces, dashes, parentheses, dots)
    expect(Wna::normalizePhoneNumber('(0812) 345-67890'))->toBe('6281234567890')
        ->and(Wna::normalizePhoneNumber('628.972.2034'))->toBe('6289722034')
        ->and(Wna::normalizePhoneNumber('+1.865.535.1078'))->toBe('18655351078');

    // Numbers already with country code 62
    expect(Wna::normalizePhoneNumber('6281234567890'))->toBe('6281234567890');

    // Invalid or empty numbers
    expect(Wna::normalizePhoneNumber(null))->toBeNull()
        ->and(Wna::normalizePhoneNumber(''))->toBeNull()
        ->and(Wna::normalizePhoneNumber('   '))->toBeNull()
        ->and(Wna::normalizePhoneNumber('abcdef'))->toBeNull()
        ->and(Wna::normalizePhoneNumber('12345'))->toBeNull() // Too short (< 8)
        ->and(Wna::normalizePhoneNumber('12345678901234567890'))->toBeNull(); // Too long (> 16)
});

test('whatsapp message generates official bilingual notification text', function () {
    $refDate = Carbon::parse('2026-10-23')->startOfDay();

    $wna = Wna::factory()->make([
        'nama' => 'John Doe',
        'no_paspor' => 'A1234567',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'masa_berlaku' => '2026-10-30',
        'nomor_telepon' => '081234567890',
    ]);

    $msg = $wna->generateWhatsappMessage($refDate);

    // Indonesian section
    expect($msg)->toContain("PEMBERITAHUAN\nLAYANAN IZIN TINGGAL KEIMIGRASIAN")
        ->and($msg)->toContain('Kepada John Doe')
        ->and($msg)->toContain('masa berlaku Izin Tinggal ITAS akan segera berakhir pada tanggal 30 Oktober 2026')
        ->and($msg)->toContain('*evisa.imigrasi.go.id*')
        ->and($msg)->toContain('WhatsApp 08113558555')
        ->and($msg)->toContain('Kantor Imigrasi Kelas II Non TPI Ponorogo')
        ->and($msg)->toContain('Jalan Sekar Putih Timur, Kel. Tonatan, Ponorogo');

    // English section
    expect($msg)->toContain('IMMIGRATION SERVICE NOTIFICATION')
        ->and($msg)->toContain('To: John Doe')
        ->and($msg)->toContain('Stay Permit ITAS will expire soon on 30 October 2026')
        ->and($msg)->toContain('website at *evisa.imigrasi.go.id*, prior to the expiration date')
        ->and($msg)->toContain('WhatsApp at 08113558555')
        ->and($msg)->toContain('Ponorogo Immigration Office Class II Non-TPI')
        ->and($msg)->toContain('Sekar Putih Timur Street, Tonatan, Ponorogo');

    // Null masa_berlaku returns null
    $wnaNullMasaBerlaku = Wna::factory()->make(['masa_berlaku' => null]);
    expect($wnaNullMasaBerlaku->generateWhatsappMessage())->toBeNull();
});

test('whatsapp_url accessor produces a properly encoded wa.me url or null if phone is missing/invalid', function () {
    $wnaValid = Wna::factory()->create([
        'nama' => 'Valid Contact',
        'no_paspor' => 'V123456',
        'nomor_telepon' => '081234567890',
        'masa_berlaku' => Carbon::now('Asia/Jakarta')->addDays(10)->toDateString(),
    ]);

    expect($wnaValid->whatsapp_url)->not->toBeNull()
        ->and($wnaValid->whatsapp_url)->toStartWith('https://wa.me/6281234567890?text=')
        ->and($wnaValid->whatsapp_url)->toContain(rawurlencode('Valid Contact'));

    $wnaNullPhone = Wna::factory()->create([
        'nomor_telepon' => null,
    ]);
    expect($wnaNullPhone->whatsapp_url)->toBeNull();

    $wnaInvalidPhone = Wna::factory()->create([
        'nomor_telepon' => 'not-a-number',
    ]);
    expect($wnaInvalidPhone->whatsapp_url)->toBeNull();
});

test('early warning page displays whatsapp_url in wna items for authenticated admin', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Wna::factory()->create([
        'nama' => 'Warning Target',
        'nomor_telepon' => '081234567899',
        'masa_berlaku' => Carbon::now('Asia/Jakarta')->addDays(5)->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get('/early-warning')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('early-warning-wna/Index')
            ->has('wna.data.0.whatsapp_url')
        );
});

test('early warning page remains strictly protected for admin only (CR-012 preserved)', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $this->get('/early-warning')->assertRedirect('/login');
    $this->actingAs($petugas)->get('/early-warning')->assertForbidden();
});
