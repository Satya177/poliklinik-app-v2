<?php

namespace Database\Factories;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obat>
 */
class ObatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_obat' => ucfirst(fake()->unique()->word()),
            'kemasan' => fake()->randomElement(['Strip', 'Botol', 'Tube', 'Box']),
            'stok' => fake()->numberBetween(0, 200),
            'harga' => fake()->numberBetween(1, 100) * 1000,
        ];
    }
}
