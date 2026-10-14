<?php

namespace Database\Factories;

use App\Models\Permohonan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permohonan>
 */
class PermohonanFactory extends Factory
{
    protected $model = Permohonan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_paspor' => fake()->bothify('?#######'),
            'nama' => fake()->name(),
            'kewarganegaraan' => fake()->country(),
            'layanan' => fake()->randomElement(['Perpanjangan ITAS', 'Alih Status ITAP', 'Pemberian EPO', 'Pendaftaran ABG']),
            'tanggal' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'kabupaten' => fake()->randomElement(['Wonosobo', 'Banjarnegara', 'Purbalingga', 'Kebumen']),
            'permohonan' => fake()->sentence(),
        ];
    }
}
