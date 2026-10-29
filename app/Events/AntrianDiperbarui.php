<?php

namespace App\Events;

use App\Models\JadwalPeriksa;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Dikirim setiap kali nomor antrian yang sedang dilayani pada suatu jadwal berubah.
 */
class AntrianDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const CHANNEL = 'antrian';

    public function __construct(
        public int $idJadwal,
        public ?int $nomorDilayani,
    ) {}

    /**
     * Broadcast nomor antrian terbaru untuk jadwal. Kegagalan koneksi WebSocket
     * dilaporkan tanpa menggagalkan request yang sedang berjalan.
     */
    public static function kirimUntukJadwal(JadwalPeriksa $jadwal): void
    {
        try {
            static::dispatch($jadwal->id, $jadwal->nomorDilayani());
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel(self::CHANNEL)];
    }

    public function broadcastAs(): string
    {
        return 'antrian.diperbarui';
    }

    /**
     * @return array{id_jadwal: int, nomor_dilayani: int|null}
     */
    public function broadcastWith(): array
    {
        return [
            'id_jadwal' => $this->idJadwal,
            'nomor_dilayani' => $this->nomorDilayani,
        ];
    }
}
