<?php

namespace App\Models;

use Database\Factories\DaftarPoliFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarPoli extends Model
{
    /** @use HasFactory<DaftarPoliFactory> */
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DIPERIKSA = 'diperiksa';

    public const STATUS_SELESAI = 'selesai';

    protected $table = 'daftar_poli';

    protected $fillable = [
        'id_pasien',
        'id_jadwal',
        'keluhan',
        'no_antrian',
        'status',
    ];

    public function pasien()
    {
        return $this->belongsTo(User::class, 'id_pasien');
    }

    public function jadwalPeriksa()
    {
        return $this->belongsTo(JadwalPeriksa::class, 'id_jadwal');
    }

    public function periksas()
    {
        return $this->hasMany(Periksa::class, 'id_daftar_poli');
    }

    public function periksa()
    {
        return $this->hasOne(Periksa::class, 'id_daftar_poli');
    }

    /**
     * Scope pendaftaran yang belum selesai diperiksa (masih menunggu atau sedang diperiksa).
     *
     * @param  Builder<DaftarPoli>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->whereIn('status', [self::STATUS_MENUNGGU, self::STATUS_DIPERIKSA]);
    }

    public function isSelesai(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }
}
