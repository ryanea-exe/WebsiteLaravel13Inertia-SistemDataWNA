<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Abg;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Abg>
 */
class AbgFactory extends Factory
{
    protected $model = Abg::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_register' => 'REG-ABG-'.fake()->unique()->numerify('#####'),
            'no_paspor' => fake()->bothify('?#######'),
            'nama' => fake()->name(),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-18 years', '-1 year')->format('Y-m-d'),
            'jenis_kelamin' => fake()->randomElement([Gender::LakiLaki, Gender::Perempuan]),
            'kewarganegaraan' => 'Indonesia / '.fake()->country(),
            'nama_ayah' => fake()->name('male'),
            'kewarganegaraan_ayah' => fake()->country(),
            'nama_ibu' => fake()->name('female'),
            'kewarganegaraan_ibu' => 'Indonesia',
            'nomor_telepon' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
            'foto' => null,
        ];
    }
}
