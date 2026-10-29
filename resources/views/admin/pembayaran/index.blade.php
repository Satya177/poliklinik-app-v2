<x-layouts.app title="Verifikasi Pembayaran">

    <div class="space-y-8">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran Pasien</h2>
            <p class="text-sm text-slate-500">
                Periksa bukti pembayaran yang telah diunggah pasien dan konfirmasi pelunasan tagihan.
            </p>
        </div>

        {{-- Daftar Menunggu Verifikasi --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i class="fas fa-hourglass-half text-amber-500"></i>
                    Tagihan Menunggu Verifikasi
                </h3>
                <span class="badge badge-warning text-xs font-semibold">
                    {{ $menungguVerifikasi->count() }} Tagihan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="py-3.5 px-4">Pasien</th>
                            <th class="py-3.5 px-4">Poli & Dokter</th>
                            <th class="py-3.5 px-4">Tanggal Periksa</th>
                            <th class="py-3.5 px-4">Total Tagihan</th>
                            <th class="py-3.5 px-4 text-center">Bukti Pembayaran</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($menungguVerifikasi as $periksa)
                            @php
                                $pasien = $periksa->daftarPoli?->pasien;
                                $dokter = $periksa->daftarPoli?->jadwalPeriksa?->dokter;
                                $poli = $dokter?->poli;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800">{{ $pasien?->nama ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">No. RM: {{ $pasien?->no_rm ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-slate-800">{{ $poli?->nama_poli ?? 'Poli Umum' }}</p>
                                    <p class="text-xs text-slate-500">dr. {{ $dokter?->nama ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    {{ $periksa->tgl_periksa?->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-primary">
                                    Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($periksa->bukti_bayar)
                                        <a href="{{ route('pembayaran.bukti', $periksa->id) }}"
                                            target="_blank"
                                            class="inline-flex flex-col items-center gap-1 group">
                                            <img src="{{ route('pembayaran.bukti', $periksa->id) }}"
                                                alt="Bukti Pembayaran"
                                                class="w-16 h-16 object-cover rounded-lg border border-slate-200 shadow-xs group-hover:scale-105 transition">
                                            <span class="text-[11px] text-primary font-semibold group-hover:underline">
                                                Lihat Foto
                                            </span>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <form action="{{ route('admin.pembayaran.verifikasi', $periksa->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-success btn-sm rounded-xl text-white font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Konfirmasi Lunas
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    <i class="fas fa-check-double text-3xl mb-2 block text-slate-300"></i>
                                    Tidak ada tagihan yang menunggu verifikasi saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Riwayat Pembayaran Lunas --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i class="fas fa-circle-check text-emerald-600"></i>
                    Riwayat Pembayaran Terverifikasi (Lunas)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="py-3 px-4">Pasien</th>
                            <th class="py-3 px-4">Poli & Dokter</th>
                            <th class="py-3 px-4">Total Tagihan</th>
                            <th class="py-3 px-4">Waktu Verifikasi</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($sudahLunas as $periksa)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $periksa->daftarPoli?->pasien?->nama ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $periksa->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli ?? 'Poli' }}
                                    (dr. {{ $periksa->daftarPoli?->jadwalPeriksa?->dokter?->nama ?? '-' }})
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-500">
                                    {{ $periksa->tgl_verifikasi?->format('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-success text-white text-xs">Lunas</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400 text-xs">
                                    Belum ada riwayat pembayaran yang diverifikasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-layouts.app>

