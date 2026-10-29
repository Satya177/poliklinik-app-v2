<?php

namespace App\Models;

use Database\Factories\JadwalPeriksaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class JadwalPeriksa extends Model
{
    /** @use HasFactory<JadwalPeriksaFactory> */
    use HasFactory;

    protected $table = 'jadwal_periksa';

    protected $fillable = [
        'id_dokter',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function dokter()
    {
        return $this->belongsTo(User::class, 'id_dokter');
    }

    public function daftarPolis()
    {
        return $this->hasMany(DaftarPoli::class, 'id_jadwal');
    }

    /**
     * Nomor antrian yang sedang dilayani untuk jadwal ini:
     * 1. Pasien dengan status 'diperiksa' (sedang dipanggil/dilayani).
     * 2. Jika belum ada yang 'diperiksa', nomor antrian terakhir yang sudah 'selesai' diperiksa.
     */
    public function nomorDilayani(): ?int
    {
        $sedangDiperiksa = $this->daftarPolis()
            ->where('status', DaftarPoli::STATUS_DIPERIKSA)
            ->max('no_antrian');

        if ($sedangDiperiksa !== null) {
            return (int) $sedangDiperiksa;
        }

        $terakhirSelesai = $this->daftarPolis()
            ->where('status', DaftarPoli::STATUS_SELESAI)
            ->max('no_antrian');

        return $terakhirSelesai === null ? null : (int) $terakhirSelesai;
    }

    /**
     * Peta id jadwal => nomor antrian yang sedang dilayani, dioptimasi tanpa N+1 query.
     *
     * @return Collection<int, int>
     */
    public static function nomorDilayaniPerJadwal(): Collection
    {
        $sedangDiperiksa = DaftarPoli::query()
            ->where('status', DaftarPoli::STATUS_DIPERIKSA)
            ->selectRaw('id_jadwal, MAX(no_antrian) as nomor_dilayani')
            ->groupBy('id_jadwal')
            ->pluck('nomor_dilayani', 'id_jadwal')
            ->map(fn ($nomor): int => (int) $nomor);

        $terakhirSelesai = DaftarPoli::query()
            ->where('status', DaftarPoli::STATUS_SELESAI)
            ->selectRaw('id_jadwal, MAX(no_antrian) as nomor_dilayani')
            ->groupBy('id_jadwal')
            ->pluck('nomor_dilayani', 'id_jadwal')
            ->map(fn ($nomor): int => (int) $nomor);

        return $terakhirSelesai->replace($sedangDiperiksa);
    }
}
