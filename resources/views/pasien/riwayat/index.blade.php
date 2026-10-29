<x-layouts.app title="Riwayat Pendaftaran">

    <div class="space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h2>
                <p class="text-sm text-slate-500">
                    Daftar seluruh pendaftaran poliklinik Anda, diurutkan dari yang terbaru.
                </p>
            </div>
            <a href="{{ route('pasien.daftar') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition shadow-sm self-start">
                <i class="fas fa-plus"></i>
                Daftar Poli Baru
            </a>
        </div>

        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="py-3.5 px-4 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Poli</th>
                            <th class="py-3.5 px-4">Nama Dokter</th>
                            <th class="py-3.5 px-4">Tanggal & Jadwal</th>
                            <th class="py-3.5 px-4 text-center">Nomor Antrian</th>
                            <th class="py-3.5 px-4 text-center">Status Pemeriksaan</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($riwayats as $index => $riwayat)
                            @php
                                $jadwal = $riwayat->jadwalPeriksa;
                                $dokter = $jadwal?->dokter;
                                $poli = $dokter?->poli;
                                $selesai = $riwayat->periksa !== null;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 text-center font-semibold text-slate-600">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $poli?->nama_poli ?? 'Poli Umum' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    dr. {{ $dokter?->nama ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    <p class="font-semibold text-slate-700">
                                        {{ $riwayat->created_at?->format('d M Y') ?? '-' }}
                                        ({{ $jadwal?->hari ?? '-' }})
                                    </p>
                                    <p>
                                        {{ substr((string) ($jadwal?->jam_mulai ?? ''), 0, 5) }} - {{ substr((string) ($jadwal?->jam_selesai ?? ''), 0, 5) }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-50 font-extrabold text-indigo-700 text-base shadow-sm">
                                        {{ $riwayat->no_antrian }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($selesai)
                                        <span class="badge badge-success gap-1 text-white text-xs py-2 px-3">
                                            <i class="fas fa-check-circle"></i> Selesai
                                        </span>
                                    @elseif($riwayat->status === \App\Models\DaftarPoli::STATUS_DIPERIKSA)
                                        <span class="badge badge-info gap-1 text-white text-xs py-2 px-3">
                                            <i class="fas fa-stethoscope"></i> Sedang Diperiksa
                                        </span>
                                    @else
                                        <span class="badge badge-warning gap-1 text-slate-800 text-xs py-2 px-3">
                                            <i class="fas fa-hourglass-half"></i> Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($selesai)
                                        <a href="{{ route('pasien.riwayat.show', $riwayat->id) }}"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                                            <i class="fas fa-eye"></i>
                                            Detail
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <i class="fas fa-calendar-xmark text-3xl mb-2 block"></i>
                                    Belum ada riwayat pendaftaran poli.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-layouts.app>

