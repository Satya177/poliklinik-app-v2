<?php

namespace App\Exports;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ObatExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
{
    private int $nomor = 0;

    /**
     * @return Builder<Obat>
     */
    public function query(): Builder
    {
        return Obat::query()->orderBy('nama_obat');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['No', 'Nama Obat', 'Kemasan', 'Stok', 'Harga (Rp)'];
    }

    /**
     * @param  Obat  $obat
     * @return array<int, string|int|null>
     */
    public function map($obat): array
    {
        return [
            ++$this->nomor,
            $obat->nama_obat,
            $obat->kemasan,
            (int) $obat->stok,
            (int) $obat->harga,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Data Obat';
    }
}
