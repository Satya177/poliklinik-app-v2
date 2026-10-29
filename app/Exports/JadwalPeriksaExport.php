<?php

namespace App\Exports;

use App\Models\JadwalPeriksa;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class JadwalPeriksaExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    private int $nomor = 0;

    public function __construct(private int $idDokter) {}

    /**
     * @return Builder<JadwalPeriksa>
     */
    public function query(): Builder
    {
        return JadwalPeriksa::query()
            ->with('dokter.poli')
            ->withCount('daftarPolis')
            ->where('id_dokter', $this->idDokter)
            ->orderBy('hari')
            ->orderBy('jam_mulai');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['No', 'Dokter', 'Poli', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Jumlah Pendaftar'];
    }

    /**
     * @param  JadwalPeriksa  $jadwal
     * @return array<int, string|int|null>
     */
    public function map($jadwal): array
    {
        return [
            ++$this->nomor,
            $jadwal->dokter?->nama,
            $jadwal->dokter?->poli?->nama_poli ?? '-',
            $jadwal->hari,
            substr((string) $jadwal->jam_mulai, 0, 5),
            substr((string) $jadwal->jam_selesai, 0, 5),
            (int) $jadwal->daftar_polis_count,
        ];
    }

    public function title(): string
    {
        return 'Jadwal Periksa';
    }
}
