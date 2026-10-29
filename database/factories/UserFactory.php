<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'no_ktp' => fake()->unique()->numerify('################'),
            'no_hp' => fake()->numerify('08##########'),
            'role' => 'pasien',
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'admin',
        ]);
    }

    public function dokter(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'dokter',
        ]);
    }

    public function pasien(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'pasien',
            'no_rm' => now()->format('Ym').'-'.fake()->unique()->numerify('###'),
        ]);
    }
}
