<?php

namespace App\Models;

use Database\Factories\LaporanIzinTinggalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $bulan
 * @property string $kategori
 * @property string $layanan
 * @property int $disetujui_l
 * @property int $disetujui_p
 * @property int $ditolak_l
 * @property int $ditolak_p
 * @property string|null $keterangan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $total_disetujui
 * @property-read int $total_ditolak
 * @property-read int $total
 */
#[Fillable([
    'bulan',
    'kategori',
    'layanan',
    'disetujui_l',
    'disetujui_p',
    'ditolak_l',
    'ditolak_p',
    'keterangan',
])]
class LaporanIzinTinggal extends Model
{
    /** @use HasFactory<LaporanIzinTinggalFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laporan_izin_tinggal';

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'total_disetujui',
        'total_ditolak',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bulan' => 'date:Y-m-d',
            'disetujui_l' => 'integer',
            'disetujui_p' => 'integer',
            'ditolak_l' => 'integer',
            'ditolak_p' => 'integer',
        ];
    }

    /**
     * Scope query to filter records matching category or service.
     *
     * @param  Builder<LaporanIzinTinggal>  $query
     * @return Builder<LaporanIzinTinggal>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $cleanTerm = trim($term);

        return $query->where(function (Builder $subQuery) use ($cleanTerm): void {
            $subQuery->where('kategori', 'like', "%{$cleanTerm}%")
                ->orWhere('layanan', 'like', "%{$cleanTerm}%")
                ->orWhere('keterangan', 'like', "%{$cleanTerm}%");
        });
    }

    /**
     * Total approved count (L + P).
     *
     * @return Attribute<int, never>
     */
    protected function totalDisetujui(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->disetujui_l + $this->disetujui_p
        );
    }

    /**
     * Total rejected count (L + P).
     *
     * @return Attribute<int, never>
     */
    protected function totalDitolak(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->ditolak_l + $this->ditolak_p
        );
    }

    /**
     * Grand total count (Approved + Rejected).
     *
     * @return Attribute<int, never>
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->total_disetujui + $this->total_ditolak
        );
    }
}
