<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    /**
     * Panggil pasien: tandai antrian sebagai sedang diperiksa dan broadcast nomor terbaru.
     */
    public function panggil(DaftarPoli $daftarPoli): RedirectResponse
    {
        $daftarPoli->load('jadwalPeriksa');
        $this->pastikanMilikDokter($daftarPoli);

        if ($daftarPoli->isSelesai()) {
            return back()->with('error', 'Pasien ini sudah selesai diperiksa.');
        }

        $this->aktifkanAntrian($daftarPoli);

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Pasien nomor antrian #'.$daftarPoli->no_antrian.' sedang dipanggil / dilayani.');
    }

    public function create($id)
    {
        $daftarPoli = DaftarPoli::with(['pasien', 'jadwalPeriksa'])->findOrFail($id);
        $this->pastikanMilikDokter($daftarPoli);

        if ($daftarPoli->isSelesai()) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah selesai diperiksa.');
        }

        if ($daftarPoli->status !== DaftarPoli::STATUS_DIPERIKSA) {
            $this->aktifkanAntrian($daftarPoli);
        }

        $obats = Obat::all();

        return view('dokter.periksa-pasien.create', compact('obats', 'id', 'daftarPoli'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id',
            'obat_json' => 'nullable|json',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'nullable|integer',
        ]);

        $daftarPoli = DaftarPoli::with('jadwalPeriksa')->findOrFail($request->id_daftar_poli);
        $this->pastikanMilikDokter($daftarPoli);

        $obatIds = $request->filled('obat_json') ? json_decode($request->obat_json, true) : [];
        $obatIds = is_array($obatIds) ? array_values(array_filter($obatIds, 'is_numeric')) : [];
        $obats = Obat::whereIn('id', $obatIds)->get()->keyBy('id');

        $tersimpan = DB::transaction(function () use ($request, $daftarPoli, $obatIds, $obats): bool {
            $sudahDiperiksa = DaftarPoli::whereKey($daftarPoli->id)
                ->lockForUpdate()
                ->first()
                ->isSelesai();

            if ($sudahDiperiksa || Periksa::where('id_daftar_poli', $daftarPoli->id)->exists()) {
                return false;
            }

            $totalObat = collect($obatIds)->sum(fn ($idObat): int => (int) ($obats->get($idObat)?->harga ?? 0));

            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa' => now(),
                'catatan' => $request->catatan,
                'biaya_periksa' => $totalObat + Periksa::BIAYA_JASA_DOKTER,
                'status_bayar' => Periksa::BAYAR_BELUM,
            ]);

            foreach ($obatIds as $idObat) {
                if ($obats->has($idObat)) {
                    DetailPeriksa::create([
                        'id_periksa' => $periksa->id,
                        'id_obat' => $idObat,
                    ]);
                }
            }

            $daftarPoli->update(['status' => DaftarPoli::STATUS_SELESAI]);

            return true;
        });

        if (! $tersimpan) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah selesai diperiksa.');
        }

        AntrianDiperbarui::kirimUntukJadwal($daftarPoli->jadwalPeriksa);

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa pasien berhasil disimpan.');
    }

    /**
     * Tandai pendaftaran ini sebagai sedang diperiksa (dan kembalikan antrian lain pada jadwal yang sama ke menunggu),
     * lalu broadcast perubahan nomor antrian.
     */
    private function aktifkanAntrian(DaftarPoli $daftarPoli): void
    {
        DB::transaction(function () use ($daftarPoli): void {
            DaftarPoli::where('id_jadwal', $daftarPoli->id_jadwal)
                ->where('status', DaftarPoli::STATUS_DIPERIKSA)
                ->whereKeyNot($daftarPoli->id)
                ->update(['status' => DaftarPoli::STATUS_MENUNGGU]);

            $daftarPoli->update(['status' => DaftarPoli::STATUS_DIPERIKSA]);
        });

        AntrianDiperbarui::kirimUntukJadwal($daftarPoli->jadwalPeriksa);
    }

    /**
     * Pastikan pendaftaran berada pada jadwal milik dokter yang sedang login.
     */
    private function pastikanMilikDokter(DaftarPoli $daftarPoli): void
    {
        abort_unless($daftarPoli->jadwalPeriksa?->id_dokter === Auth::id(), 403);
    }
}
