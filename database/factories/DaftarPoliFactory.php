<?php

namespace Database\Factories;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DaftarPoli>
 */
class DaftarPoliFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pasien' => User::factory()->pasien(),
            'id_jadwal' => JadwalPeriksa::factory(),
            'keluhan' => fake()->sentence(),
            'no_antrian' => fake()->numberBetween(1, 50),
            'status' => DaftarPoli::STATUS_MENUNGGU,
        ];
    }

    public function diperiksa(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DaftarPoli::STATUS_DIPERIKSA,
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DaftarPoli::STATUS_SELESAI,
        ]);
    }
}
