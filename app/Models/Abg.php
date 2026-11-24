<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\AbgFactory;
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
 * @property string $nama
 * @property string|null $tempat_lahir
 * @property Carbon|null $tanggal_lahir
 * @property Gender $jenis_kelamin
 * @property string $kewarganegaraan
 * @property string|null $nama_ayah
 * @property string|null $kewarganegaraan_ayah
 * @property string|null $nama_ibu
 * @property string|null $kewarganegaraan_ibu
 * @property string|null $nomor_telepon
 * @property string|null $alamat
 * @property string|null $foto
 * @property string|null $foto_url
 * @property string|null $whatsapp_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'no_register',
    'nama',
    'tempat_lahir',
    'tanggal_lahir',
    'jenis_kelamin',
    'kewarganegaraan',
    'nama_ayah',
    'kewarganegaraan_ayah',
    'nama_ibu',
    'kewarganegaraan_ibu',
    'nomor_telepon',
    'alamat',
    'kabupaten',
    'foto',
])]
class Abg extends Model
{
    /** @use HasFactory<AbgFactory> */
    use HasFactory;

    /**
     * Supported regencies / kabupaten for ABG.
     */
    public const KABUPATEN_OPTIONS = ['Ponorogo', 'Pacitan', 'Trenggalek'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'abg';

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'foto_url',
        'umur',
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
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * URL for authenticated and authorized photo streaming.
     *
     * @return Attribute<string|null, never>
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->foto !== null && $this->foto !== ''
                ? route('abg.photo', $this->id)
                : null
        );
    }

    /**
     * Scope query to filter records matching search keyword.
     *
     * @param  Builder<Abg>  $query
     * @return Builder<Abg>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $cleanTerm = trim($term);

        return $query->where(function (Builder $subQuery) use ($cleanTerm): void {
            $subQuery->where('nama', 'like', "%{$cleanTerm}%")
                ->orWhere('no_register', 'like', "%{$cleanTerm}%")
                ->orWhere('kewarganegaraan', 'like', "%{$cleanTerm}%")
                ->orWhere('nama_ayah', 'like', "%{$cleanTerm}%")
                ->orWhere('nama_ibu', 'like', "%{$cleanTerm}%");
        });
    }

    /**
     * Scope query to filter ABG by early warning category.
     *
     * @param  Builder<Abg>  $query
     * @param  string|null  $kategori  'kurang_18'|'kurang_21'|'lewat_21'|'semua'
     * @return Builder<Abg>
     */
    public function scopeKategoriPeringatan(
        Builder $query,
        ?string $kategori = null,
        DateTimeInterface|string|null $baseDate = null
    ): Builder {
        $today = $baseDate !== null
            ? Carbon::parse($baseDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        $from18 = $today->copy()->subYears(18)->toDateString();
        $to18 = $today->copy()->subYears(18)->addMonthsNoOverflow(6)->toDateString();

        $from21 = $today->copy()->subYears(21)->toDateString();
        $to21 = $today->copy()->subYears(21)->addMonthsNoOverflow(6)->toDateString();

        return match ($kategori) {
            'kurang_18' => $query->whereNotNull('tanggal_lahir')->whereBetween('tanggal_lahir', [$from18, $to18]),
            'kurang_21' => $query->whereNotNull('tanggal_lahir')->whereBetween('tanggal_lahir', [$from21, $to21]),
            'lewat_21', 'kedaluwarsa' => $query->whereNotNull('tanggal_lahir')->where('tanggal_lahir', '<', $from21),
            default => $query->whereNotNull('tanggal_lahir')->where(function (Builder $subQuery) use ($from18, $to18, $from21, $to21): void {
                $subQuery->whereBetween('tanggal_lahir', [$from18, $to18])
                    ->orWhereBetween('tanggal_lahir', [$from21, $to21])
                    ->orWhere('tanggal_lahir', '<', $from21);
            }),
        };
    }

    /**
     * Get summary counts for all early warning ABG categories.
     *
     * @return array{semua: int, kurang_18: int, kurang_21: int, lewat_21: int}
     */
    public static function getEarlyWarningCounts(DateTimeInterface|string|null $baseDate = null): array
    {
        $today = $baseDate !== null
            ? Carbon::parse($baseDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        return [
            'semua' => static::query()->kategoriPeringatan('semua', $today)->count(),
            'kurang_18' => static::query()->kategoriPeringatan('kurang_18', $today)->count(),
            'kurang_21' => static::query()->kategoriPeringatan('kurang_21', $today)->count(),
            'lewat_21' => static::query()->kategoriPeringatan('lewat_21', $today)->count(),
        ];
    }

    /**
     * Get the early warning category key for this instance.
     *
     * @return 'kurang_18'|'kurang_21'|'lewat_21'|null
     */
    public function getEarlyWarningStatus(DateTimeInterface|string|null $baseDate = null): ?string
    {
        if ($this->tanggal_lahir === null) {
            return null;
        }

        $today = $baseDate !== null
            ? Carbon::parse($baseDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        $dob = Carbon::parse($this->tanggal_lahir)->timezone('Asia/Jakarta')->startOfDay();

        $from18 = $today->copy()->subYears(18)->startOfDay();
        $to18 = $today->copy()->subYears(18)->addMonthsNoOverflow(6)->startOfDay();
        $from21 = $today->copy()->subYears(21)->startOfDay();
        $to21 = $today->copy()->subYears(21)->addMonthsNoOverflow(6)->startOfDay();

        if ($dob->lt($from21)) {
            return 'lewat_21';
        }

        if ($dob->gte($from21) && $dob->lte($to21)) {
            return 'kurang_21';
        }

        if ($dob->gte($from18) && $dob->lte($to18)) {
            return 'kurang_18';
        }

        return null;
    }

    /**
     * Formatted current age.
     *
     * @return Attribute<string, never>
     */
    protected function umur(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->tanggal_lahir === null) {
                    return '-';
                }

                $today = Carbon::now('Asia/Jakarta')->startOfDay();
                $dob = Carbon::parse($this->tanggal_lahir)->timezone('Asia/Jakarta')->startOfDay();
                $years = (int) $dob->diffInYears($today);
                $days = (int) $dob->copy()->addYears($years)->diffInDays($today);

                return "{$years} tahun {$days} hari";
            }
        );
    }

    /**
     * Normalize various phone number formats into an international digit format.
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
     * Generate the standardized early warning WhatsApp message for ABG.
     */
    public function generateWhatsappMessage(?Carbon $referenceDate = null): ?string
    {
        if ($this->tanggal_lahir === null) {
            return null;
        }

        $today = $referenceDate !== null
            ? Carbon::parse($referenceDate)->timezone('Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        $dob = Carbon::parse($this->tanggal_lahir)->timezone('Asia/Jakarta')->startOfDay();
        $urgensi = $this->getEarlyWarningStatus($today);

        $target = match ($urgensi) {
            'kurang_18' => $dob->copy()->addYears(18),
            'kurang_21', 'lewat_21' => $dob->copy()->addYears(21),
            default => $dob->diffInYears($today) < 18 ? $dob->copy()->addYears(18) : $dob->copy()->addYears(21),
        };

        $tanggalFormatted = $target->locale('id')->translatedFormat('d F Y');
        $namaAbg = $this->nama ?? '-';

        return "PEMBERITAHUAN\n"
            ."LAYANAN KEWARGANEGARAAN GANDA (ABG)\n\n"
            ."Kepada {$namaAbg}\n"
            ."Dengan ini kami menginformasikan bahwa masa berlaku Fasilitas Keimigrasian Anak Berkewarganegaraan Ganda (ABG) akan segera berakhir pada tanggal {$tanggalFormatted}. Silakan melakukan permohonan melalui laman resmi *evisa.imigrasi.go.id*, sebelum masa berlaku berakhir.\n"
            ."Untuk informasi lebih lanjut, silakan menghubungi layanan informasi kami dengan nomor WhatsApp 08113558555.\n"
            ."Terima kasih atas perhatian dan kerja samanya.\n\n"
            ."Kantor Imigrasi Kelas II Non TPI Ponorogo\n"
            .'Jalan Sekar Putih Timur, Kel. Tonatan, Ponorogo';
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
