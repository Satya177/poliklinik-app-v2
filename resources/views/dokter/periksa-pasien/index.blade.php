<x-layouts.app title="Periksa Pasien">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Daftar Pasien Menunggu</h2>
                <p class="text-sm text-slate-500">Antrean pasien yang terdaftar pada jadwal periksa Anda.</p>
            </div>
            <a href="{{ route('dokter.export.riwayat-pasien') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition self-start">
                <i class="fas fa-file-excel text-xs"></i>
                Export Riwayat Pasien
            </a>
        </div>

        {{-- Table Card --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="py-4 px-6 text-center">No. Antrean</th>
                            <th class="py-4 px-6">Pasien</th>
                            <th class="py-4 px-6">Jadwal Periksa</th>
                            <th class="py-4 px-6">Keluhan</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelAntreanDokterBody" class="text-sm divide-y divide-slate-100">
                        @forelse($daftarPasien as $item)
                            @php
                                $sudahDiperiksa = $item->periksas->count() > 0 || $item->isSelesai();
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 font-black text-indigo-700 text-lg shadow-sm">
                                        {{ $item->no_antrian }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-800">{{ $item->pasien->nama ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">No. RM: <span class="font-semibold text-primary">{{ $item->pasien->no_rm ?? '-' }}</span></p>
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-600">
                                    <p class="font-medium text-slate-700">{{ $item->jadwalPeriksa->hari ?? '-' }}</p>
                                    <p>{{ substr($item->jadwalPeriksa->jam_mulai ?? '', 0, 5) }} - {{ substr($item->jadwalPeriksa->jam_selesai ?? '', 0, 5) }}</p>
                                </td>
                                <td class="py-4 px-6 text-slate-600 max-w-[200px] text-xs">
                                    {{ $item->keluhan ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($sudahDiperiksa)
                                        <span class="badge badge-success gap-1 text-white text-xs py-2 px-3">
                                            <i class="fas fa-check-circle"></i> Selesai
                                        </span>
                                    @elseif($item->status === \App\Models\DaftarPoli::STATUS_DIPERIKSA)
                                        <span class="badge badge-info gap-1 text-white text-xs py-2 px-3">
                                            <i class="fas fa-bullhorn"></i> Sedang Dilayani
                                        </span>
                                    @else
                                        <span class="badge badge-warning gap-1 text-slate-800 text-xs py-2 px-3">
                                            <i class="fas fa-hourglass-half"></i> Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    @if($sudahDiperiksa)
                                        <span class="text-xs text-slate-400 italic">Sudah diperiksa</span>
                                    @else
                                        <div class="inline-flex items-center gap-2">
                                            @if($item->status === \App\Models\DaftarPoli::STATUS_MENUNGGU)
                                                <form action="{{ route('periksa-pasien.panggil', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                                                        <i class="fas fa-bullhorn"></i>
                                                        Panggil
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('periksa-pasien.create', $item->id) }}"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                                                <i class="fas fa-stethoscope"></i>
                                                Periksa
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <i class="fas fa-user-check text-4xl mb-3 block text-slate-300"></i>
                                    Tidak ada pasien dalam antrean saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
        <script type="module">
            async function muatUlangTabelAntrean() {
                try {
                    const response = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (!response.ok) return;

                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const tbodyBaru = doc.getElementById('tabelAntreanDokterBody');
                    const tbodyLama = document.getElementById('tabelAntreanDokterBody');

                    if (tbodyBaru && tbodyLama && tbodyBaru.innerHTML !== tbodyLama.innerHTML) {
                        tbodyLama.innerHTML = tbodyBaru.innerHTML;
                    }
                } catch (e) {
                    // Abaikan gangguan jaringan sementara
                }
            }

            if (window.Echo) {
                window.Echo.channel('antrian')
                    .listen('.antrian.diperbarui', () => {
                        muatUlangTabelAntrean();
                    });
            }

            setInterval(muatUlangTabelAntrean, 3000);
        </script>
    @endpush

</x-layouts.app>
