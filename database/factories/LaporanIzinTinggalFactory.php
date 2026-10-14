<?php

namespace Database\Factories;

use App\Models\LaporanIzinTinggal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaporanIzinTinggal>
 */
class LaporanIzinTinggalFactory extends Factory
{
    protected $model = LaporanIzinTinggal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori' => fake()->randomElement(['Izin Tinggal Kunjungan', 'Izin Tinggal Terbatas', 'Izin Tinggal Tetap']),
            'layanan' => fake()->randomElement(['Perpanjangan', 'Pemberian Baru', 'Alih Status']),
            'disetujui_l' => fake()->numberBetween(5, 50),
            'disetujui_p' => fake()->numberBetween(5, 50),
            'ditolak_l' => fake()->numberBetween(0, 5),
            'ditolak_p' => fake()->numberBetween(0, 5),
            'keterangan' => fake()->sentence(),
        ];
    }
}
