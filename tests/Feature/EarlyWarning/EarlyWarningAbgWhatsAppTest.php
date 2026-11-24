<?php

use App\Enums\UserRole;
use App\Models\Abg;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('phone number normalization handles local, international, and invalid inputs for abg safely', function () {
    // Indonesian format with leading 0
    expect(Abg::normalizePhoneNumber('081234567890'))->toBe('6281234567890')
        ->and(Abg::normalizePhoneNumber('085712345678'))->toBe('6285712345678');

    // Numbers with leading '+'
    expect(Abg::normalizePhoneNumber('+6281234567890'))->toBe('6281234567890')
        ->and(Abg::normalizePhoneNumber('+17208678540'))->toBe('17208678540');

    // Numbers with formatting symbols (spaces, dashes, parentheses, dots)
    expect(Abg::normalizePhoneNumber('(0812) 345-67890'))->toBe('6281234567890')
        ->and(Abg::normalizePhoneNumber('628.972.2034'))->toBe('6289722034')
        ->and(Abg::normalizePhoneNumber('+1.865.535.1078'))->toBe('18655351078');

    // Numbers already with country code 62
    expect(Abg::normalizePhoneNumber('6281234567890'))->toBe('6281234567890');

    // Invalid or empty numbers
    expect(Abg::normalizePhoneNumber(null))->toBeNull()
        ->and(Abg::normalizePhoneNumber(''))->toBeNull()
        ->and(Abg::normalizePhoneNumber('   '))->toBeNull()
        ->and(Abg::normalizePhoneNumber('abcdef'))->toBeNull()
        ->and(Abg::normalizePhoneNumber('12345'))->toBeNull() // Too short (< 8)
        ->and(Abg::normalizePhoneNumber('12345678901234567890'))->toBeNull(); // Too long (> 16)
});

test('abg whatsapp message generates official notification text in indonesian only', function () {
    $refDate = Carbon::parse('2026-11-23')->startOfDay();

    // Subject 1: Approaching 18 (DOB 2009-02-01 -> 18th birthday is 2027-02-01)
    $abg18 = Abg::factory()->make([
        'nama' => 'Maya Tanaka',
        'tanggal_lahir' => '2009-02-01',
        'nomor_telepon' => '081234567890',
    ]);

    $msg18 = $abg18->generateWhatsappMessage($refDate);

    expect($msg18)->toContain("PEMBERITAHUAN\nLAYANAN KEWARGANEGARAAN GANDA (ABG)")
        ->and($msg18)->toContain('Kepada Maya Tanaka')
        ->and($msg18)->toContain('masa berlaku Fasilitas Keimigrasian Anak Berkewarganegaraan Ganda (ABG) akan segera berakhir pada tanggal 01 Februari 2027')
        ->and($msg18)->toContain('*evisa.imigrasi.go.id*')
        ->and($msg18)->toContain('WhatsApp 08113558555')
        ->and($msg18)->toContain('Kantor Imigrasi Kelas II Non TPI Ponorogo')
        ->and($msg18)->toContain('Jalan Sekar Putih Timur, Kel. Tonatan, Ponorogo')
        ->and($msg18)->not->toContain('IMMIGRATION SERVICE NOTIFICATION'); // Indonesian only

    // Subject 2: Approaching 21 (DOB 2006-02-01 -> 21st birthday is 2027-02-01)
    $abg21 = Abg::factory()->make([
        'nama' => 'Kenzo Suzuki',
        'tanggal_lahir' => '2006-02-01',
        'nomor_telepon' => '081234567891',
    ]);

    $msg21 = $abg21->generateWhatsappMessage($refDate);

    expect($msg21)->toContain('Kepada Kenzo Suzuki')
        ->and($msg21)->toContain('pada tanggal 01 Februari 2027')
        ->and($msg21)->not->toContain('IMMIGRATION SERVICE NOTIFICATION');

    // Null tanggal_lahir returns null
    $abgNullDob = Abg::factory()->make(['tanggal_lahir' => null]);
    expect($abgNullDob->generateWhatsappMessage($refDate))->toBeNull();
});

test('abg whatsapp_url accessor produces a properly encoded wa.me url or null if phone is missing/invalid', function () {
    $abgValid = Abg::factory()->create([
        'nama' => 'Ahmad Smith',
        'nomor_telepon' => '081234567890',
        'tanggal_lahir' => '2009-02-01',
    ]);

    expect($abgValid->whatsapp_url)->not->toBeNull()
        ->and($abgValid->whatsapp_url)->toStartWith('https://wa.me/6281234567890?text=')
        ->and($abgValid->whatsapp_url)->toContain(rawurlencode('Ahmad Smith'));

    $abgNullPhone = Abg::factory()->create([
        'nomor_telepon' => null,
    ]);
    expect($abgNullPhone->whatsapp_url)->toBeNull();

    $abgInvalidPhone = Abg::factory()->create([
        'nomor_telepon' => 'not-a-phone',
    ]);
    expect($abgInvalidPhone->whatsapp_url)->toBeNull();
});

test('early warning abg page displays whatsapp_url in abg items for authenticated admin', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Abg::factory()->create([
        'nama' => 'Early Target ABG',
        'nomor_telepon' => '081234567899',
        'tanggal_lahir' => Carbon::now('Asia/Jakarta')->subYears(18)->addMonths(2)->format('Y-m-d'),
    ]);

    $this->actingAs($admin)
        ->get('/early-warning/abg')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('early-warning-abg/Index')
            ->has('abg.data.0.whatsapp_url')
        );
});

