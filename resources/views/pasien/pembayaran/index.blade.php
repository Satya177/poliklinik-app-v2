<x-layouts.app title="Pembayaran">

    <div class="space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Tagihan & Pembayaran Pemeriksaan</h2>
            <p class="text-sm text-slate-500">
                Lihat rincian tagihan dari hasil pemeriksaan Anda dan unggah foto bukti pembayaran.
            </p>
        </div>

        <div class="space-y-4">
            @forelse($tagihans as $tagihan)
                @php
                    $daftar = $tagihan->daftarPoli;
                    $jadwal = $daftar?->jadwalPeriksa;
                    $dokter = $jadwal?->dokter;
                    $poli = $dokter?->poli;
                @endphp
                <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">

                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-lg text-slate-800">
                                    {{ $poli?->nama_poli ?? 'Poli Umum' }}
                                </span>
                                @if($tagihan->status_bayar === \App\Models\Periksa::BAYAR_LUNAS)
                                    <span class="badge badge-success text-white text-xs gap-1 py-2.5 px-3">
                                        <i class="fas fa-check-circle"></i> Lunas
                                    </span>
                                @elseif($tagihan->status_bayar === \App\Models\Periksa::BAYAR_MENUNGGU_VERIFIKASI)
                                    <span class="badge badge-info text-white text-xs gap-1 py-2.5 px-3">
                                        <i class="fas fa-clock"></i> Menunggu Verifikasi
                                    </span>
                                @else
                                    <span class="badge badge-warning text-slate-800 text-xs gap-1 py-2.5 px-3">
                                        <i class="fas fa-exclamation-circle"></i> Belum Bayar
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500">
                                Dokter: <strong class="text-slate-700">dr. {{ $dokter?->nama ?? '-' }}</strong>
                                &bull; Tanggal Periksa: <strong class="text-slate-700">{{ $tagihan->tgl_periksa?->format('d M Y, H:i') }}</strong>
                            </p>

                            @if($tagihan->catatan)
                                <p class="text-xs text-slate-600 bg-slate-50 rounded-xl p-3 border border-slate-100">
                                    <span class="font-semibold text-slate-700">Catatan:</span> {{ $tagihan->catatan }}
                                </p>
                            @endif

                            @if($tagihan->obats->isNotEmpty())
                                <div class="text-xs text-slate-600">
                                    <span class="font-semibold">Obat:</span>
                                    {{ $tagihan->obats->pluck('nama_obat')->implode(', ') }}
                                </div>
                            @endif
                        </div>

                        <div class="lg:text-right space-y-3 shrink-0 lg:min-w-72">
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-semibold">Total Tagihan</p>
                                <p class="text-2xl font-extrabold text-primary">
                                    Rp {{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}
                                </p>
                            </div>

                            @if($tagihan->status_bayar === \App\Models\Periksa::BAYAR_BELUM)
                                <form action="{{ route('pasien.pembayaran.upload', $tagihan->id) }}"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    class="space-y-2">
                                    @csrf
                                    <input type="file"
                                        name="bukti_bayar"
                                        accept="image/*"
                                        required
                                        class="file-input file-input-bordered file-input-sm w-full rounded-xl text-xs">
                                    @error('bukti_bayar')
                                        <p class="text-xs text-red-500 text-left">{{ $message }}</p>
                                    @enderror
                                    <button type="submit"
                                        class="btn btn-primary btn-sm w-full rounded-xl text-white font-semibold">
                                        <i class="fas fa-upload mr-1"></i>
                                        Unggah Bukti Pembayaran
                                    </button>
                                </form>
                            @elseif($tagihan->bukti_bayar)
                                <div class="flex lg:justify-end items-center gap-2">
                                    <a href="{{ route('pembayaran.bukti', $tagihan->id) }}"
                                        target="_blank"
                                        class="btn btn-outline btn-xs rounded-lg">
                                        <i class="fas fa-image mr-1"></i>
                                        Lihat Bukti Bayar
                                    </a>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="card bg-base-100 shadow-sm rounded-2xl p-12 text-center border border-slate-100 text-slate-400">
                    <i class="fas fa-file-invoice text-4xl mb-3 block text-slate-300"></i>
                    Belum ada tagihan pemeriksaan.
                </div>
            @endforelse
        </div>

    </div>

</x-layouts.app>

