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

test('whatsapp message generates contextual text for each urgency status', function () {
    $refDate = Carbon::parse('2026-10-23')->startOfDay();

    // 1. Sangat Mendesak (merah, e.g. 7 days remaining)
    $wnaMerah = Wna::factory()->make([
        'nama' => 'John Doe',
        'no_paspor' => 'A1234567',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'masa_berlaku' => '2026-10-30',
        'nomor_telepon' => '081234567890',
    ]);
    $msgMerah = $wnaMerah->generateWhatsappMessage($refDate);
    expect($msgMerah)->toContain('per tanggal 23 Oktober 2026')
        ->and($msgMerah)->toContain('John Doe')
        ->and($msgMerah)->toContain('A1234567')
        ->and($msgMerah)->toContain('ITAS')
        ->and($msgMerah)->toContain('30 Oktober 2026')
        ->and($msgMerah)->toContain('Status: SANGAT MENDESAK')
        ->and($msgMerah)->toContain('tersisa 7 hari lagi');

    // 2. Sangat Mendesak - Expiring Today (0 days remaining)
    $wnaHariIni = Wna::factory()->make([
        'nama' => 'Jane Doe',
        'no_paspor' => 'B7654321',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAP,
        'masa_berlaku' => '2026-10-23',
        'nomor_telepon' => '081234567891',
    ]);
    $msgHariIni = $wnaHariIni->generateWhatsappMessage($refDate);
    expect($msgHariIni)->toContain('Status: SANGAT MENDESAK')
        ->and($msgHariIni)->toContain('berakhir hari ini (23 Oktober 2026)');

    // 3. Mendesak (kuning, e.g. 20 days remaining)
    $wnaKuning = Wna::factory()->make([
        'nama' => 'Bob Smith',
        'no_paspor' => 'C9876543',
        'jenis_izin_tinggal' => JenisIzinTinggal::VOA,
        'masa_berlaku' => '2026-11-12',
        'nomor_telepon' => '081234567892',
    ]);
    $msgKuning = $wnaKuning->generateWhatsappMessage($refDate);
    expect($msgKuning)->toContain('Status: MENDESAK')
        ->and($msgKuning)->toContain('tersisa 20 hari lagi');

    // 4. Perhatian (biru, e.g. 45 days remaining)
    $wnaBiru = Wna::factory()->make([
        'nama' => 'Alice Wonder',
        'no_paspor' => 'D5555555',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITK,
        'masa_berlaku' => '2026-12-07',
        'nomor_telepon' => '081234567893',
    ]);
    $msgBiru = $wnaBiru->generateWhatsappMessage($refDate);
    expect($msgBiru)->toContain('Status: PERHATIAN')
        ->and($msgBiru)->toContain('tersisa 45 hari lagi');

    // 5. Kedaluwarsa (overdue, e.g. 5 days ago)
    $wnaKedaluwarsa = Wna::factory()->make([
        'nama' => 'Charlie Late',
        'no_paspor' => 'E1111111',
        'jenis_izin_tinggal' => JenisIzinTinggal::ITAS,
        'masa_berlaku' => '2026-10-18',
        'nomor_telepon' => '081234567894',
    ]);
    $msgKedaluwarsa = $wnaKedaluwarsa->generateWhatsappMessage($refDate);
    expect($msgKedaluwarsa)->toContain('Status: KEDALUWARSA')
        ->and($msgKedaluwarsa)->toContain('telah lewat 5 hari (berakhir pada 18 Oktober 2026)');
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
            ->component('early-warning/Index')
            ->has('wna.data.0.whatsapp_url')
        );
});

test('early warning page remains strictly protected for admin only (CR-012 preserved)', function () {
    $petugas = User::factory()->create(['role' => UserRole::User]);

    $this->get('/early-warning')->assertRedirect('/login');
    $this->actingAs($petugas)->get('/early-warning')->assertForbidden();
});
