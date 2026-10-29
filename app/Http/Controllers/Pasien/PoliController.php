<?php

namespace App\Http\Controllers\Pasien;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $polis = Poli::all();
        $jadwals = JadwalPeriksa::with(['dokter.poli'])->get();
        $riwayatDaftar = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksa'])
            ->where('id_pasien', $user->id)
            ->latest()
            ->get();
        $antrianAktif = $riwayatDaftar->first(fn (DaftarPoli $daftar): bool => ! $daftar->isSelesai());

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwals,
            'riwayatDaftar' => $riwayatDaftar,
            'antrianAktif' => $antrianAktif,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
        ]);

        $pasienId = Auth::id();

        $jadwal = JadwalPeriksa::whereKey($request->id_jadwal)
            ->whereHas('dokter', fn ($query) => $query->where('id_poli', $request->id_poli))
            ->first();

        if ($jadwal === null) {
            return back()
                ->withInput()
                ->withErrors(['id_jadwal' => 'Jadwal yang dipilih tidak sesuai dengan poli tujuan.']);
        }

        $nomorAntrian = DB::transaction(function () use ($request, $pasienId): ?int {
            User::whereKey($pasienId)->lockForUpdate()->first();
            JadwalPeriksa::whereKey($request->id_jadwal)->lockForUpdate()->first();

            $punyaAntrianAktif = DaftarPoli::where('id_pasien', $pasienId)
                ->aktif()
                ->exists();

            if ($punyaAntrianAktif) {
                return null;
            }

            $jumlahSudahDaftar = DaftarPoli::where('id_jadwal', $request->id_jadwal)->count();

            $daftarPoli = DaftarPoli::create([
                'id_pasien' => $pasienId,
                'id_jadwal' => $request->id_jadwal,
                'keluhan' => $request->filled('keluhan') ? $request->keluhan : '-',
                'no_antrian' => $jumlahSudahDaftar + 1,
                'status' => DaftarPoli::STATUS_MENUNGGU,
            ]);

            return $daftarPoli->no_antrian;
        });

        if ($nomorAntrian === null) {
            return back()
                ->withInput()
                ->with('error', 'Anda masih memiliki antrian yang belum selesai diperiksa. Silakan daftar kembali setelah pemeriksaan selesai.');
        }

        AntrianDiperbarui::kirimUntukJadwal($jadwal);

        return redirect()
            ->back()
            ->with('success', 'Berhasil Mendaftar ke Poli! Nomor antrean Anda: '.$nomorAntrian);
    }
}
