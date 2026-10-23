<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use Database\Factories\WnaFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $no_register
 * @property string $no_paspor
 * @property string $nama
 * @property Gender $jenis_kelamin
 * @property string $kewarganegaraan
 * @property JenisIzinTinggal $jenis_izin_tinggal
 * @property string|null $izin_tinggal_ke
 * @property Carbon|null $masa_berlaku
 * @property string|null $penjamin
 * @property string|null $alamat_penjamin
 * @property string|null $nomor_telepon
 * @property string $kabupaten
 * @property string|null $foto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $sisa_hari
 * @property-read string $urgensi
 * @property-read string $urgensi_label
 * @property-read string|null $foto_url
 * @property-read string|null $whatsapp_url
 */
#[Fillable([
    'no_register',
    'no_paspor',
    'nama',
    'jenis_kelamin',
    'kewarganegaraan',
    'jenis_izin_tinggal',
    'izin_tinggal_ke',
    'masa_berlaku',
    'penjamin',
    'alamat_penjamin',
    'nomor_telepon',
    'kabupaten',
    'foto',
])]
class Wna extends Model
{
    /** @use HasFactory<WnaFactory> */
    use HasFactory;

    /**
     * Supported regencies / kabupaten for WNA.
     *
     * @var array<int, string>
     */
    public const KABUPATEN_OPTIONS = [
        'Ponorogo',
        'Pacitan',
        'Trenggalek',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wna';

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'sisa_hari',
        'urgensi',
        'urgensi_label',
        'foto_url',
        'whatsapp_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_kelamin' => Gender::class,
            'jenis_izin_tinggal' => JenisIzinTinggal::class,
            'masa_berlaku' => 'date:Y-m-d',
            'kabupaten' => 'string',
        ];
    }

    /**
     * Scope query to WNA within the early warning period (today to 3 calendar months).
     *
     * @param  Builder<Wna>  $query
     * @return Builder<Wna>
     */
    public function scopeEarlyWarning(Builder $query, DateTimeInterface|string|null $baseDate = null): Builder
    {
        return $query->kategoriPeringatan('semua', $baseDate);
    }

    /**
     * Scope query to filter WNA by early warning category.
     *
     * @param  Builder<Wna>  $query
     * @param  string|null  $kategori  'merah'|'kuning'|'biru'|'kedaluwarsa'|'semua'
     * @return Builder<Wna>
     */
    public function scopeKategoriPeringatan(
        Builder $query,
        ?string $kategori = null,
        DateTimeInterface|string|null $baseDate = null
    ): Builder {
        $today = $baseDate !== null
            ? Carbon::parse($baseDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        return match ($kategori) {
            'merah' => $query->whereBetween('masa_berlaku', [
                $today->toDateString(),
                $today->copy()->addDays(14)->toDateString(),
            ]),
            'kuning' => $query->whereBetween('masa_berlaku', [
                $today->copy()->addDays(15)->toDateString(),
                $today->copy()->addDays(30)->toDateString(),
            ]),
            'biru' => $query->whereBetween('masa_berlaku', [
                $today->copy()->addDays(31)->toDateString(),
                $today->copy()->addMonthsNoOverflow(3)->toDateString(),
            ]),
            'kedaluwarsa' => $query->where('masa_berlaku', '<', $today->toDateString()),
            default => $query->whereBetween('masa_berlaku', [
                $today->toDateString(),
                $today->copy()->addMonthsNoOverflow(3)->toDateString(),
            ]),
        };
    }

    /**
     * Get summary counts for all early warning categories.
     *
     * @return array{semua: int, merah: int, kuning: int, biru: int, kedaluwarsa: int}
     */
    public static function getEarlyWarningCounts(DateTimeInterface|string|null $baseDate = null): array
    {
        $today = $baseDate !== null
            ? Carbon::parse($baseDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        return [
            'semua' => static::query()->kategoriPeringatan('semua', $today)->count(),
            'merah' => static::query()->kategoriPeringatan('merah', $today)->count(),
            'kuning' => static::query()->kategoriPeringatan('kuning', $today)->count(),
            'biru' => static::query()->kategoriPeringatan('biru', $today)->count(),
            'kedaluwarsa' => static::query()->kategoriPeringatan('kedaluwarsa', $today)->count(),
        ];
    }

    /**
     * Scope query to filter records matching search keyword.
     *
     * @param  Builder<Wna>  $query
     * @return Builder<Wna>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $cleanTerm = trim($term);

        return $query->where(function (Builder $subQuery) use ($cleanTerm): void {
            $subQuery->where('nama', 'like', "%{$cleanTerm}%")
                ->orWhere('no_paspor', 'like', "%{$cleanTerm}%")
                ->orWhere('no_register', 'like', "%{$cleanTerm}%")
                ->orWhere('kewarganegaraan', 'like', "%{$cleanTerm}%")
                ->orWhere('kabupaten', 'like', "%{$cleanTerm}%");
        });
    }

    /**
     * Mutator to ensure masa_berlaku is consistently stored as Y-m-d across database drivers.
     */
    public function setMasaBerlakuAttribute(DateTimeInterface|string|null $value): void
    {
        $this->attributes['masa_berlaku'] = $value !== null
            ? Carbon::parse($value)->toDateString()
            : null;
    }

    /**
     * Remaining days until permit expiration.
     *
     * @return Attribute<int, never>
     */
    protected function sisaHari(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if ($this->masa_berlaku === null) {
                    return -1;
                }

                $today = Carbon::now('Asia/Jakarta')->startOfDay();
                $target = Carbon::parse($this->masa_berlaku)->timezone('Asia/Jakarta')->startOfDay();

                return (int) $today->diffInDays($target, false);
            }
        );
    }

    /**
     * Urgency level based on remaining days.
     *
     * @return Attribute<string, never>
     */
    protected function urgensi(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $days = $this->sisa_hari;

                if ($days < 0) {
                    return 'kedaluwarsa';
                }

                if ($days <= 14) {
                    return 'merah';
                }

                if ($days <= 30) {
                    return 'kuning';
                }

                return 'biru';
            }
        );
    }

    /**
     * Human-readable urgency label.
     *
     * @return Attribute<string, never>
     */
    protected function urgensiLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->urgensi) {
                'kedaluwarsa' => 'Sudah Kedaluwarsa',
                'merah' => 'Sangat Mendesak (<= 14 Hari)',
                'kuning' => 'Mendesak (<= 30 Hari)',
                default => 'Perhatian (> 30 Hari)',
            }
        );
    }

    /**
     * Get the authenticated streaming URL for the WNA photo.
     *
     * @return Attribute<string|null, never>
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->foto && $this->id ? route('wna.photo', $this->id) : null
        );
    }

    /**
     * Normalize a phone number into an international digit sequence suitable for WhatsApp URL.
     */
    public static function normalizePhoneNumber(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $trimmed = trim($number);
        if ($trimmed === '') {
            return null;
        }

        // Strip non-digit characters except leading '+'
        $cleaned = preg_replace('/[^\d+]/', '', $trimmed) ?? '';
        if ($cleaned === '') {
            return null;
        }

        // Remove leading '+'
        if (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        }

        // Replace leading Indonesian '0' with '62'
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        }

        // Validate that it consists solely of digits with a length between 8 and 16 digits
        if (! ctype_digit($cleaned) || strlen($cleaned) < 8 || strlen($cleaned) > 16) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Generate the standardized early warning WhatsApp message based on urgency status.
     */
    public function generateWhatsappMessage(?Carbon $referenceDate = null): ?string
    {
        $refDate = $referenceDate !== null
            ? Carbon::parse($referenceDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();
        $refDate->locale('id');
        $todayFormatted = $refDate->translatedFormat('d F Y');

        if ($this->masa_berlaku !== null) {
            $mb = Carbon::parse($this->masa_berlaku)->timezone('Asia/Jakarta')->startOfDay();
            $mb->locale('id');
            $masaBerlakuFormatted = $mb->translatedFormat('d F Y');
        } else {
            $masaBerlakuFormatted = '-';
        }

        $days = $this->sisa_hari;
        $jenisIzin = $this->jenis_izin_tinggal->value;

        $greeting = "Halo, pemberitahuan resmi dari Kantor Imigrasi Kelas II Non TPI Ponorogo terkait pemantauan masa berlaku izin tinggal WNA (per tanggal {$todayFormatted}):\n\n"
            ."• Nama: {$this->nama}\n"
            ."• No. Paspor: {$this->no_paspor}\n"
            ."• Jenis Izin: {$jenisIzin}\n"
            ."• Masa Berlaku: {$masaBerlakuFormatted}\n\n";

        $urgencyBody = match ($this->urgensi) {
            'kedaluwarsa' => 'Status: KEDALUWARSA. Masa berlaku izin tinggal telah lewat '.abs($days)." hari (berakhir pada {$masaBerlakuFormatted}). Mohon segera melapor ke Kantor Imigrasi Kelas II Non TPI Ponorogo untuk penanganan lebih lanjut.",
            'merah' => $days === 0
                ? "Status: SANGAT MENDESAK. Masa berlaku izin tinggal berakhir hari ini ({$masaBerlakuFormatted}). Mohon segera melapor atau mengurus perpanjangan izin tinggal ke Kantor Imigrasi Kelas II Non TPI Ponorogo hari ini."
                : "Status: SANGAT MENDESAK. Masa berlaku izin tinggal tersisa {$days} hari lagi (hingga {$masaBerlakuFormatted}). Mohon segera mengajukan perpanjangan izin tinggal sebelum masa berlaku habis.",
            'kuning' => "Status: MENDESAK. Masa berlaku izin tinggal tersisa {$days} hari lagi (hingga {$masaBerlakuFormatted}). Diimbau untuk segera mempersiapkan berkas perpanjangan izin tinggal.",
            default => "Status: PERHATIAN. Masa berlaku izin tinggal tersisa {$days} hari lagi (hingga {$masaBerlakuFormatted}). Harap memperhatikan batas waktu izin tinggal keimigrasian Anda.",
        };

        $closing = "\n\nTerima kasih atas perhatian dan kerja samanya.";

        return $greeting.$urgencyBody.$closing;
    }

    /**
     * Get the WhatsApp click-to-chat URL with pre-filled notification message.
     *
     * @return Attribute<string|null, never>
     */
    protected function whatsappUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $phone = static::normalizePhoneNumber($this->nomor_telepon);
                if ($phone === null) {
                    return null;
                }

                $message = $this->generateWhatsappMessage();
                if ($message === null) {
                    return null;
                }

                return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
            }
        );
    }
}
