<?php

namespace Database\Factories;

use App\Models\RealisasiPnbp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RealisasiPnbp>
 */
class RealisasiPnbpFactory extends Factory
{
    protected $model = RealisasiPnbp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->randomFloat(2, 50000000, 200000000);
        $realisasi = fake()->randomFloat(2, 40000000, 250000000);

        return [
            'bulan' => fake()->unique()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-01'),
            'target' => $target,
            'realisasi' => $realisasi,
            'keterangan' => fake()->sentence(),
        ];
    }
}
