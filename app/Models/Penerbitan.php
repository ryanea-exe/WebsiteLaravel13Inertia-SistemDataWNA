<?php

namespace App\Models;

use Database\Factories\PenerbitanFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $no_paspor
 * @property string $nama
 * @property string $kewarganegaraan
 * @property string $layanan
 * @property Carbon|null $tanggal
 * @property string|null $kabupaten
 * @property string|null $keterangan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'no_paspor',
    'nama',
    'kewarganegaraan',
    'layanan',
    'tanggal',
    'kabupaten',
    'keterangan',
])]
class Penerbitan extends Model
{
    /** @use HasFactory<PenerbitanFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penerbitan';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
        ];
    }

    /**
     * Mutator to ensure tanggal is consistently stored as Y-m-d across database drivers.
     */
    public function setTanggalAttribute(DateTimeInterface|string|null $value): void
    {
        $this->attributes['tanggal'] = $value !== null
            ? Carbon::parse($value)->toDateString()
            : null;
    }

    /**
     * Scope query to filter records matching search keyword.
     *
     * @param  Builder<Penerbitan>  $query
     * @return Builder<Penerbitan>
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
                ->orWhere('layanan', 'like', "%{$cleanTerm}%")
                ->orWhere('kewarganegaraan', 'like', "%{$cleanTerm}%")
                ->orWhere('kabupaten', 'like', "%{$cleanTerm}%")
                ->orWhere('keterangan', 'like', "%{$cleanTerm}%");
        });
    }
}
