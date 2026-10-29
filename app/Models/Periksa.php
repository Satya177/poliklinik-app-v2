<?php

namespace App\Models;

use Database\Factories\PeriksaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periksa extends Model
{
    /** @use HasFactory<PeriksaFactory> */
    use HasFactory;

    public const BIAYA_JASA_DOKTER = 150000;

    public const BAYAR_BELUM = 'belum_bayar';

    public const BAYAR_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';

    public const BAYAR_LUNAS = 'lunas';

    protected $table = 'periksa';

    protected $fillable = [
        'id_daftar_poli',
        'tgl_periksa',
        'catatan',
        'biaya_periksa',
        'status_bayar',
        'bukti_bayar',
        'tgl_upload_bukti',
        'tgl_verifikasi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_periksa' => 'datetime',
            'tgl_upload_bukti' => 'datetime',
            'tgl_verifikasi' => 'datetime',
        ];
    }

    public function daftarPoli()
    {
        return $this->belongsTo(DaftarPoli::class, 'id_daftar_poli');
    }

    public function detailPeriksas()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_periksa');
    }

    public function obats()
    {
        return $this->belongsToMany(Obat::class, 'detail_periksa', 'id_periksa', 'id_obat');
    }

    public function statusBayarLabel(): string
    {
        return match ($this->status_bayar) {
            self::BAYAR_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::BAYAR_LUNAS => 'Lunas',
            default => 'Belum Bayar',
        };
    }
}
