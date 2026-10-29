<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function index(Request $request): View
    {
        $riwayats = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksa'])
            ->where('id_pasien', $request->user()->id)
            ->latest()
            ->latest('id')
            ->get();

        return view('pasien.riwayat.index', [
            'riwayats' => $riwayats,
        ]);
    }

    public function show(Request $request, DaftarPoli $daftarPoli): View
    {
        abort_unless($daftarPoli->id_pasien === $request->user()->id, 403);

        $daftarPoli->load(['jadwalPeriksa.dokter.poli', 'periksa.obats']);

        abort_if($daftarPoli->periksa === null, 404, 'Pemeriksaan belum selesai.');

        return view('pasien.riwayat.show', [
            'daftarPoli' => $daftarPoli,
            'periksa' => $daftarPoli->periksa,
        ]);
    }
}
