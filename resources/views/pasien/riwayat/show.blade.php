<x-layouts.app title="Detail Pemeriksaan">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('pasien.riwayat.index') }}"
                class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Detail Hasil Pemeriksaan</h2>
                <p class="text-sm text-slate-500">
                    Ringkasan catatan dokter, daftar obat, dan total biaya pemeriksaan.
                </p>
            </div>
        </div>

        {{-- Informasi Pendaftaran --}}
        <div class="card bg-gradient-to-r from-[#1e2d6b] to-[#2d4499] text-white shadow-md rounded-2xl p-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-indigo-200">Poliklinik</p>
                    <p class="font-bold text-base mt-0.5">
                        {{ $daftarPoli->jadwalPeriksa?->dokter?->poli?->nama_poli ?? 'Poli Umum' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-indigo-200">Dokter Pemeriksa</p>
                    <p class="font-bold text-base mt-0.5">
                        dr. {{ $daftarPoli->jadwalPeriksa?->dokter?->nama ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-indigo-200">Tanggal Periksa</p>
                    <p class="font-bold text-base mt-0.5">
                        {{ $periksa->tgl_periksa?->format('d M Y, H:i') ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-indigo-200">Nomor Antrian</p>
                    <p class="font-extrabold text-xl text-amber-300 mt-0.5">
                        #{{ $daftarPoli->no_antrian }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Catatan Dokter --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-6 space-y-3">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fas fa-stethoscope text-primary"></i>
                Catatan Dokter
            </h3>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-700 leading-relaxed">
                {{ $periksa->catatan ?: 'Tidak ada catatan khusus.' }}
            </div>
        </div>

        {{-- Daftar Obat & Total Biaya --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i class="fas fa-pills text-primary"></i>
                    Daftar Obat & Rincian Biaya
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="py-3 px-5">No</th>
                            <th class="py-3 px-5">Nama Obat</th>
                            <th class="py-3 px-5">Kemasan</th>
                            <th class="py-3 px-5 text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($periksa->obats as $index => $obat)
                            <tr>
                                <td class="py-3 px-5 text-slate-600">{{ $index + 1 }}</td>
                                <td class="py-3 px-5 font-semibold text-slate-800">{{ $obat->nama_obat }}</td>
                                <td class="py-3 px-5 text-slate-600">{{ $obat->kemasan ?? '-' }}</td>
                                <td class="py-3 px-5 text-right font-medium text-slate-800">
                                    Rp {{ number_format($obat->harga, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                    Tidak ada resep obat pada pemeriksaan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 text-sm">
                        <tr class="border-t border-slate-200">
                            <td colspan="3" class="py-3 px-5 text-right text-slate-600 font-medium">Biaya Jasa Dokter</td>
                            <td class="py-3 px-5 text-right font-semibold text-slate-700">
                                Rp {{ number_format(\App\Models\Periksa::BIAYA_JASA_DOKTER, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="border-t border-slate-200">
                            <td colspan="3" class="py-4 px-5 text-right font-bold text-slate-800 text-base">Total Biaya</td>
                            <td class="py-4 px-5 text-right font-extrabold text-primary text-lg">
                                Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

</x-layouts.app>

