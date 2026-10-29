<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DokterExport;
use App\Exports\ObatExport;
use App\Exports\PasienExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function dokter(): BinaryFileResponse
    {
        return Excel::download(new DokterExport, 'data-dokter-'.now()->format('Ymd-His').'.xlsx');
    }

    public function pasien(): BinaryFileResponse
    {
        return Excel::download(new PasienExport, 'data-pasien-'.now()->format('Ymd-His').'.xlsx');
    }

    public function obat(): BinaryFileResponse
    {
        return Excel::download(new ObatExport, 'data-obat-'.now()->format('Ymd-His').'.xlsx');
    }
}
