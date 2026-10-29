<?php

namespace App\Exports;

use App\Models\Periksa;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class RiwayatPasienExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
{
    private int $nomor = 0;

    public function __construct(private int $idDokter) {}

    /**
     * Hanya pemeriksaan yang dilakukan oleh dokter yang sedang login.
     *
     * @return Builder<Periksa>
     */
    public function query(): Builder
    {
        return Periksa::query()
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa', 'obats'])
            ->whereHas('daftarPoli.jadwalPeriksa', fn (Builder $query) => $query->where('id_dokter', $this->idDokter))
            ->latest('tgl_periksa');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Periksa',
            'No. RM',
            'Nama Pasien',
            'No. Antrian',
            'Keluhan',
            'Catatan Dokter',
            'Obat',
            'Total Biaya (Rp)',
            'Status Pembayaran',
        ];
    }

    /**
     * @param  Periksa  $periksa
     * @return array<int, string|int|null>
     */
    public function map($periksa): array
    {
        return [
            ++$this->nomor,
            $periksa->tgl_periksa?->format('d-m-Y H:i'),
            $periksa->daftarPoli?->pasien?->no_rm,
            $periksa->daftarPoli?->pasien?->nama,
            $periksa->daftarPoli?->no_antrian,
            $periksa->daftarPoli?->keluhan,
            $periksa->catatan,
            $periksa->obats->pluck('nama_obat')->implode(', ') ?: '-',
            (int) $periksa->biaya_periksa,
            $periksa->statusBayarLabel(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Riwayat Pasien';
    }
}
