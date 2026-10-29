<?php

namespace Database\Factories;

use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalPeriksa>
 */
class JadwalPeriksaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_dokter' => User::factory()->dokter(),
            'hari' => fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
        ];
    }
}
