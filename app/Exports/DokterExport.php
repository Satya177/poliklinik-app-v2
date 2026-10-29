<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DokterExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    private int $nomor = 0;

    /**
     * @return Builder<User>
     */
    public function query(): Builder
    {
        return User::query()
            ->with('poli')
            ->where('role', 'dokter')
            ->orderBy('nama');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['No', 'Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'];
    }

    /**
     * @param  User  $dokter
     * @return array<int, string|int|null>
     */
    public function map($dokter): array
    {
        return [
            ++$this->nomor,
            $dokter->nama,
            $dokter->email,
            $dokter->no_ktp,
            $dokter->no_hp,
            $dokter->alamat,
            $dokter->poli?->nama_poli ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Data Dokter';
    }
}
