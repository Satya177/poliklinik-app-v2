<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class PasienExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    private int $nomor = 0;

    /**
     * @return Builder<User>
     */
    public function query(): Builder
    {
        return User::query()
            ->where('role', 'pasien')
            ->orderBy('nama');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['No', 'No. RM', 'Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Tanggal Daftar'];
    }

    /**
     * @param  User  $pasien
     * @return array<int, string|int|null>
     */
    public function map($pasien): array
    {
        return [
            ++$this->nomor,
            $pasien->no_rm,
            $pasien->nama,
            $pasien->email,
            $pasien->no_ktp,
            $pasien->no_hp,
            $pasien->alamat,
            $pasien->created_at?->format('d-m-Y'),
        ];
    }

    public function title(): string
    {
        return 'Data Pasien';
    }
}
