<?php

namespace App\Http\Controllers;

use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuktiPembayaranController extends Controller
{
    /**
     * Tampilkan foto bukti pembayaran. Hanya admin dan pasien pemilik tagihan yang boleh melihat.
     */
    public function __invoke(Request $request, Periksa $periksa): StreamedResponse
    {
        $user = $request->user();
        $periksa->loadMissing('daftarPoli');

        $bolehMelihat = $user->role === 'admin'
            || $periksa->daftarPoli?->id_pasien === $user->id;

        abort_unless($bolehMelihat, 403);
        abort_if($periksa->bukti_bayar === null || ! Storage::disk('local')->exists($periksa->bukti_bayar), 404);

        return Storage::disk('local')->response($periksa->bukti_bayar);
    }
}
