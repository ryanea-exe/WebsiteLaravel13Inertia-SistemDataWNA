<?php

namespace App\Models;

use Database\Factories\RealisasiPnbpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bulan' => 'date',
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
}
