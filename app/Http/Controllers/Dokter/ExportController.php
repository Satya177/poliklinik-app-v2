<?php

namespace App\Http\Controllers\Dokter;

use App\Exports\JadwalPeriksaExport;
use App\Exports\RiwayatPasienExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function jadwalPeriksa(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new JadwalPeriksaExport($request->user()->id),
            'jadwal-periksa-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function riwayatPasien(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new RiwayatPasienExport($request->user()->id),
            'riwayat-pasien-'.now()->format('Ymd-His').'.xlsx',
        );
    }
}
