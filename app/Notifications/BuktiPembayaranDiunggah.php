<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk dokter ketika pasien yang ia periksa mengunggah bukti pembayaran.
 */
class BuktiPembayaranDiunggah extends Notification
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
        $namaPasien = $this->periksa->daftarPoli?->pasien?->nama ?? 'Pasien';

        return [
            'periksa_id' => $this->periksa->id,
            'judul' => 'Bukti Pembayaran Diunggah',
            'pesan' => sprintf(
                '%s telah mengunggah bukti pembayaran untuk pemeriksaan tanggal %s.',
                $namaPasien,
                $this->periksa->tgl_periksa?->translatedFormat('d M Y') ?? '-',
            ),
            'url' => route('periksa-pasien.index'),
        ];
    }
}
