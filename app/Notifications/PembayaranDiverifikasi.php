<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk pasien ketika admin memverifikasi (melunasi) pembayarannya.
 */
class PembayaranDiverifikasi extends Notification
{
    use Queueable;

    public function __construct(public Periksa $periksa) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{periksa_id: int, judul: string, pesan: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $poli = $this->periksa->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli ?? 'Poli';

        return [
            'periksa_id' => $this->periksa->id,
            'judul' => 'Pembayaran Lunas',
            'pesan' => sprintf(
                'Pembayaran pemeriksaan %s sebesar Rp %s telah diverifikasi dan dinyatakan lunas.',
                $poli,
                number_format($this->periksa->biaya_periksa, 0, ',', '.'),
            ),
            'url' => route('pasien.pembayaran.index'),
        ];
    }
}
