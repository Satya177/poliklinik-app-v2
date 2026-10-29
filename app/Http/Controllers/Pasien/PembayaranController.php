<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadBuktiPembayaranRequest;
use App\Models\Periksa;
use App\Notifications\BuktiPembayaranDiunggah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $tagihans = Periksa::with(['daftarPoli.jadwalPeriksa.dokter.poli', 'obats'])
            ->whereHas('daftarPoli', fn ($query) => $query->where('id_pasien', $request->user()->id))
            ->latest('tgl_periksa')
            ->get();

        return view('pasien.pembayaran.index', [
            'tagihans' => $tagihans,
        ]);
    }

    public function upload(UploadBuktiPembayaranRequest $request, Periksa $periksa): RedirectResponse
    {
        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'local');

        $berhasilDiperbarui = Periksa::whereKey($periksa->id)
            ->where('status_bayar', Periksa::BAYAR_BELUM)
            ->update([
                'status_bayar' => Periksa::BAYAR_MENUNGGU_VERIFIKASI,
                'bukti_bayar' => $path,
                'tgl_upload_bukti' => now(),
            ]);

        if ($berhasilDiperbarui === 0) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'Bukti pembayaran untuk tagihan ini sudah diunggah sebelumnya.');
        }

        $periksa->refresh()->load(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter']);
        $periksa->daftarPoli->jadwalPeriksa->dokter?->notify(new BuktiPembayaranDiunggah($periksa));

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }
}
