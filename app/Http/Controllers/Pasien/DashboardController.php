<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $request->user()->id)
            ->aktif()
            ->latest()
            ->first();

        $jadwals = JadwalPeriksa::with(['dokter.poli'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $nomorDilayani = JadwalPeriksa::nomorDilayaniPerJadwal();

        return view('pasien.dashboard', [
            'antrianAktif' => $antrianAktif,
            'jadwals' => $jadwals,
            'nomorDilayani' => $nomorDilayani,
        ]);
    }

    /**
     * Endpoint JSON untuk sinkronisasi real-time status antrian aktif dan nomor yang sedang dilayani.
     */
    public function status(Request $request): JsonResponse
    {
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $request->user()->id)
            ->aktif()
            ->latest()
            ->first();

        $nomorDilayani = JadwalPeriksa::nomorDilayaniPerJadwal();

        $jadwalAktif = $antrianAktif?->jadwalPeriksa;
        $dokterAktif = $jadwalAktif?->dokter;
        $poliAktif = $dokterAktif?->poli;

        return response()->json([
            'nomor_dilayani_per_jadwal' => $nomorDilayani,
            'antrian_aktif' => $antrianAktif ? [
                'id' => $antrianAktif->id,
                'id_jadwal' => $jadwalAktif?->id,
                'no_antrian' => (int) $antrianAktif->no_antrian,
                'status' => $antrianAktif->status,
                'nama_poli' => $poliAktif?->nama_poli ?? 'Poli Umum',
                'nama_dokter' => $dokterAktif?->nama ?? '-',
                'hari' => $jadwalAktif?->hari ?? '-',
                'jam_mulai' => substr((string) ($jadwalAktif?->jam_mulai ?? ''), 0, 5),
                'jam_selesai' => substr((string) ($jadwalAktif?->jam_selesai ?? ''), 0, 5),
                'nomor_dilayani' => $jadwalAktif ? ($nomorDilayani[$jadwalAktif->id] ?? null) : null,
            ] : null,
        ]);
    }
}
