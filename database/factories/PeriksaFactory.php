<?php

namespace Database\Factories;

use App\Models\DaftarPoli;
use App\Models\Periksa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Periksa>
 */
class PeriksaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_daftar_poli' => DaftarPoli::factory()->selesai(),
            'tgl_periksa' => now(),
            'catatan' => fake()->sentence(),
            'biaya_periksa' => Periksa::BIAYA_JASA_DOKTER,
            'status_bayar' => Periksa::BAYAR_BELUM,
        ];
    }

    public function menungguVerifikasi(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_bayar' => Periksa::BAYAR_MENUNGGU_VERIFIKASI,
            'bukti_bayar' => 'bukti-bayar/contoh.jpg',
            'tgl_upload_bukti' => now(),
        ]);
    }

    public function lunas(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_bayar' => Periksa::BAYAR_LUNAS,
            'bukti_bayar' => 'bukti-bayar/contoh.jpg',
            'tgl_upload_bukti' => now(),
            'tgl_verifikasi' => now(),
        ]);
    }
}
