<x-layouts.app title="Form Pemeriksaan Pasien">

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header & Back --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Pemeriksaan Pasien</h2>
                <p class="text-sm text-slate-500">Isi catatan medis diagnosis dan tentukan obat untuk pasien.</p>
            </div>
            <a href="{{ route('periksa-pasien.index') }}" class="btn btn-ghost btn-sm gap-1 text-slate-600">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- Info Pasien Box --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Nama Pasien</span>
                    <span class="font-bold text-slate-800 text-base">{{ $daftarPoli->pasien->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">No. Rekam Medis</span>
                    <span class="font-semibold text-primary text-base">{{ $daftarPoli->pasien->no_rm ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Nomor Antrean</span>
                    <span class="badge badge-primary text-white font-bold">Antrean #{{ $daftarPoli->no_antrian }}</span>
                </div>
                <div class="sm:col-span-3 pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-400 block">Keluhan Utama:</span>
                    <p class="text-slate-700 bg-slate-50 p-3 rounded-xl mt-1 text-xs leading-relaxed italic">
                        "{{ $daftarPoli->keluhan ?? 'Tidak ada keluhan tertulis' }}"
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Pemeriksaan --}}
        <form action="{{ route('periksa-pasien.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="id_daftar_poli" value="{{ $id }}">
            <input type="hidden" name="biaya_periksa" id="biayaPeriksaInput" value="0">
            <input type="hidden" name="obat_json" id="obatJsonInput" value="[]">

            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-6 space-y-6">

                {{-- Catatan Pemeriksaan --}}
                <div>
                    <label class="label pb-1">
                        <span class="font-semibold text-slate-800 text-sm">Catatan Diagnosis & Tindakan Medis</span>
                    </label>
                    <textarea name="catatan" rows="4" class="textarea textarea-bordered w-full rounded-xl text-sm" placeholder="Contoh: Pasien didiagnosa flu ringan dan radang tenggorokan. Disarankan istirahat cukup..." required></textarea>
                </div>

                {{-- Resep Obat --}}
                <div>
                    <label class="label pb-1 flex justify-between">
                        <span class="font-semibold text-slate-800 text-sm">Pilih Obat (Opsional)</span>
                        <span class="text-xs text-slate-400">Centang obat yang diresepkan</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-3 border rounded-xl bg-slate-50">
                        @forelse($obats as $obat)
                            <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-200 hover:border-primary/50 cursor-pointer transition shadow-2xs">
                                <input type="checkbox" class="checkbox checkbox-primary checkbox-sm obat-checkbox" value="{{ $obat->id }}" data-harga="{{ $obat->harga }}" onchange="calculateTotal()">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-800 text-xs truncate">{{ $obat->nama_obat }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $obat->kemasan }}</p>
                                </div>
                                <span class="text-xs font-bold text-primary">Rp {{ number_format($obat->harga, 0, ',', '.') }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 col-span-2 text-center py-4">Belum ada data obat tersedia.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Ringkasan Biaya --}}
                <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-4">
                    <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wider mb-2">Ringkasan Biaya Pemeriksaan</h4>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Jasa Dokter (Tetap):</span>
                            <span class="font-semibold">Rp 150.000</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Total Harga Obat:</span>
                            <span class="font-semibold" id="totalObatText">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2 border-t border-indigo-200/60">
                            <span>Total Tagihan Pasien:</span>
                            <span class="text-primary text-base" id="totalGrandText">Rp 150.000</span>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('periksa-pasien.index') }}" class="btn btn-ghost rounded-xl">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-xl text-white font-semibold px-6">
                        <i class="fas fa-check mr-1"></i> Simpan Pemeriksaan
                    </button>
                </div>

            </div>
        </form>

    </div>

    @push('scripts')
    <script>
        function calculateTotal() {
            const checkboxes = document.querySelectorAll('.obat-checkbox');
            let totalObat = 0;
            const selectedIds = [];

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    totalObat += parseInt(cb.getAttribute('data-harga') || 0);
                    selectedIds.push(parseInt(cb.value));
                }
            });

            document.getElementById('biayaPeriksaInput').value = totalObat;
            document.getElementById('obatJsonInput').value = JSON.stringify(selectedIds);

            document.getElementById('totalObatText').innerText = 'Rp ' + totalObat.toLocaleString('id-ID');
            document.getElementById('totalGrandText').innerText = 'Rp ' + (totalObat + 150000).toLocaleString('id-ID');
        }
    </script>
    @endpush

</x-layouts.app>
