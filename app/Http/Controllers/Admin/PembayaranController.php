<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $relasi = ['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'];

        $menungguVerifikasi = Periksa::with($relasi)
            ->where('status_bayar', Periksa::BAYAR_MENUNGGU_VERIFIKASI)
            ->oldest('tgl_upload_bukti')
            ->get();

        $sudahLunas = Periksa::with($relasi)
            ->where('status_bayar', Periksa::BAYAR_LUNAS)
            ->latest('tgl_verifikasi')
            ->limit(20)
            ->get();

        return view('admin.pembayaran.index', [
            'menungguVerifikasi' => $menungguVerifikasi,
            'sudahLunas' => $sudahLunas,
        ]);
    }

    public function verifikasi(Periksa $periksa): RedirectResponse
    {
        $berhasilDiverifikasi = Periksa::whereKey($periksa->id)
            ->where('status_bayar', Periksa::BAYAR_MENUNGGU_VERIFIKASI)
            ->update([
                'status_bayar' => Periksa::BAYAR_LUNAS,
                'tgl_verifikasi' => now(),
            ]);

        if ($berhasilDiverifikasi === 0) {
            return back()->with('error', 'Tagihan ini tidak dalam status menunggu verifikasi.');
        }

        $periksa->refresh()->load(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli']);
        $periksa->daftarPoli->pasien?->notify(new PembayaranDiverifikasi($periksa));

        return back()->with('success', 'Pembayaran berhasil diverifikasi. Status tagihan kini Lunas.');
    }
}
