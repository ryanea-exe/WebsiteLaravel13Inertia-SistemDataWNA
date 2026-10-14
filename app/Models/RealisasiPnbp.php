<?php

namespace App\Models;

use Database\Factories\RealisasiPnbpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $bulan
 * @property string $target
 * @property string $realisasi
 * @property string|null $keterangan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read float $persentase
 * @property-read float $selisih
 */
#[Fillable([
    'bulan',
    'target',
    'realisasi',
    'keterangan',
])]
class RealisasiPnbp extends Model
{
    /** @use HasFactory<RealisasiPnbpFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'realisasi_pnbp';

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'persentase',
        'selisih',
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
            'target' => 'decimal:2',
            'realisasi' => 'decimal:2',
        ];
    }

    /**
     * Normalize bulan to the first day of the month.
     *
     * @return Attribute<Carbon, Carbon|string>
     */
    protected function bulan(): Attribute
    {
        return Attribute::make(
            set: fn (Carbon|string $value): string => Carbon::parse($value)->startOfMonth()->toDateString()
        );
    }

    /**
     * Calculate percentage of realisasi against target.
     *
     * @return Attribute<float, never>
     */
    protected function persentase(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $targetVal = (float) $this->target;

                if ($targetVal <= 0) {
                    return 0.0;
                }

                $realisasiVal = (float) $this->realisasi;

                return round(($realisasiVal / $targetVal) * 100, 2);
            }
        );
    }

    /**
     * Calculate selisih (realisasi - target).
     *
     * @return Attribute<float, never>
     */
    protected function selisih(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $targetVal = (float) ($this->target ?? 0);
                $realisasiVal = (float) ($this->realisasi ?? 0);

                return round($realisasiVal - $targetVal, 2);
            }
        );
    }

    /**
     * Scope query to filter records matching search keyword.
     *
     * @param  Builder<RealisasiPnbp>  $query
     * @return Builder<RealisasiPnbp>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $cleanTerm = trim($term);

        return $query->where(function (Builder $q) use ($cleanTerm): void {
            $q->where('bulan', 'like', "%{$cleanTerm}%")
                ->orWhere('keterangan', 'like', "%{$cleanTerm}%");
        });
    }

    /**
     * Scope query to filter records by specific year.
     *
     * @param  Builder<RealisasiPnbp>  $query
     * @return Builder<RealisasiPnbp>
     */
    public function scopeTahun(Builder $query, ?string $tahun): Builder
    {
        if (empty($tahun)) {
            return $query;
        }

        return $query->whereYear('bulan', $tahun);
    }
}
