<?php

namespace Database\Factories;

use App\Models\Penerbitan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Penerbitan>
 */
class PenerbitanFactory extends Factory
{
    protected $model = Penerbitan::class;

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
            'layanan' => fake()->randomElement(['Penerbitan ITAS', 'Penerbitan ITAP', 'Penerbitan Kartu Affidavit']),
            'tanggal' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'kabupaten' => fake()->randomElement(['Wonosobo', 'Banjarnegara', 'Purbalingga', 'Kebumen']),
            'keterangan' => fake()->sentence(),
        ];
    }
}
