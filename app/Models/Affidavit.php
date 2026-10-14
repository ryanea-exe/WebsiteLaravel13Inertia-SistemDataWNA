<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\AffidavitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $no_register
 * @property string $no_paspor
 * @property string $nama
 * @property string|null $tempat_lahir
 * @property Carbon|null $tanggal_lahir
 * @property Gender $jenis_kelamin
 * @property string $kewarganegaraan
 * @property Carbon|null $masa_berlaku
 * @property string|null $nama_ayah
 * @property string|null $kewarganegaraan_ayah
 * @property string|null $nama_ibu
 * @property string|null $kewarganegaraan_ibu
 * @property string|null $nomor_telepon
 * @property string|null $alamat
 * @property string|null $foto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'no_register',
    'no_paspor',
    'nama',
    'tempat_lahir',
    'tanggal_lahir',
    'jenis_kelamin',
    'kewarganegaraan',
    'masa_berlaku',
    'nama_ayah',
    'kewarganegaraan_ayah',
    'nama_ibu',
    'kewarganegaraan_ibu',
    'nomor_telepon',
    'alamat',
    'foto',
])]
class Affidavit extends Model
{
    /** @use HasFactory<AffidavitFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'affidavit';

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
            'masa_berlaku' => 'date',
        ];
    }

    /**
     * Scope query to filter records matching search keyword.
     *
     * @param  Builder<Affidavit>  $query
     * @return Builder<Affidavit>
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
                ->orWhere('nama_ayah', 'like', "%{$cleanTerm}%")
                ->orWhere('nama_ibu', 'like', "%{$cleanTerm}%");
        });
    }
}
