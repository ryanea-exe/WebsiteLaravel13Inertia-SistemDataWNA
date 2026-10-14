<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Models\Wna;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wna>
 */
class WnaFactory extends Factory
{
    protected $model = Wna::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_register' => 'REG-WNA-'.fake()->unique()->numerify('#####'),
            'no_paspor' => fake()->bothify('?#######'),
            'nama' => fake()->name(),
            'jenis_kelamin' => fake()->randomElement([Gender::LakiLaki, Gender::Perempuan]),
            'kewarganegaraan' => fake()->country(),
            'jenis_izin_tinggal' => fake()->randomElement([JenisIzinTinggal::ITAS, JenisIzinTinggal::ITAP, JenisIzinTinggal::VOA]),
            'izin_tinggal_ke' => '1 (Pertama)',
            'masa_berlaku' => fake()->dateTimeBetween('+10 days', '+1 year')->format('Y-m-d'),
            'penjamin' => fake()->company(),
            'alamat_penjamin' => fake()->address(),
            'nomor_telepon' => fake()->phoneNumber(),
            'foto' => null,
        ];
    }

    /**
     * State for early warning (expiring within 14 days - red urgency).
     */
    public function urgentRed(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku' => now()->addDays(7)->toDateString(),
        ]);
    }

    /**
     * State for early warning (expiring within 30 days - yellow urgency).
     */
    public function urgentYellow(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku' => now()->addDays(20)->toDateString(),
        ]);
    }

    /**
     * State for early warning (expiring within 3 months - blue urgency).
     */
    public function urgentBlue(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku' => now()->addMonths(2)->toDateString(),
        ]);
    }

    /**
     * State for expired permit.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku' => now()->subDays(5)->toDateString(),
        ]);
    }
}
