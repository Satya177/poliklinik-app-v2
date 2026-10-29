<x-layouts.app title="Dashboard Pasien">

    <div class="space-y-6">

        {{-- Page Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Selamat Datang, {{ auth()->user()->nama }}
                </h2>
                <p class="text-sm text-slate-500">
                    Pantau status antrean pemeriksaan Anda dan jadwal poliklinik secara real-time.
                </p>
            </div>
            <a href="{{ route('pasien.daftar') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition shadow-sm self-start">
                <i class="fas fa-clipboard-list"></i>
                Daftar Poli
            </a>
        </div>

        {{-- Container Banner Antrian Aktif --}}
        <div id="containerBannerAntrian">
            @if($antrianAktif)
                @php
                    $jadwalAktif = $antrianAktif->jadwalPeriksa;
                    $dokterAktif = $jadwalAktif?->dokter;
                    $poliAktif = $dokterAktif?->poli;
                    $sedangDilayani = $jadwalAktif ? ($nomorDilayani[$jadwalAktif->id] ?? null) : null;
                    $giliranPasien = $sedangDilayani !== null && (int) $sedangDilayani === (int) $antrianAktif->no_antrian;
                @endphp
                <div id="bannerAntrianAktif"
                    data-jadwal-id="{{ $jadwalAktif?->id }}"
                    data-no-antrian-pasien="{{ $antrianAktif->no_antrian }}"
                    class="card bg-gradient-to-r from-[#1e2d6b] to-[#2d4499] text-white shadow-lg rounded-2xl p-6 border border-indigo-400/20">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="space-y-2">
                            <div id="bannerStatusBadge"
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $giliranPasien ? 'bg-emerald-400/25 text-emerald-200' : 'bg-amber-400/20 text-amber-300' }} text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full {{ $giliranPasien ? 'bg-emerald-300' : 'bg-amber-300' }} animate-pulse"></span>
                                <span id="bannerStatusText">
                                    {{ $giliranPasien ? 'Giliran Anda Sedang Dilayani' : 'Antrian Aktif Anda' }}
                                </span>
                            </div>
                            <h3 id="bannerNamaPoli" class="text-2xl font-extrabold tracking-tight">
                                {{ $poliAktif?->nama_poli ?? 'Poli Umum' }}
                            </h3>
                            <p class="text-sm text-indigo-100 flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span>
                                    <i class="fas fa-user-doctor mr-1.5 text-indigo-300"></i>
                                    dr. <span id="bannerNamaDokter">{{ $dokterAktif?->nama ?? '-' }}</span>
                                </span>
                                <span>
                                    <i class="fas fa-calendar-day mr-1.5 text-indigo-300"></i>
                                    <span id="bannerJadwal">
                                        {{ $jadwalAktif?->hari ?? '-' }},
                                        {{ substr((string) ($jadwalAktif?->jam_mulai ?? ''), 0, 5) }} - {{ substr((string) ($jadwalAktif?->jam_selesai ?? ''), 0, 5) }}
                                    </span>
                                </span>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:gap-6 shrink-0">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-4 text-center border border-white/15">
                                <p class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Nomor Antrian Anda</p>
                                <p id="bannerNomorPasien" class="text-3xl font-black text-amber-300 mt-1">
                                    {{ $antrianAktif->no_antrian }}
                                </p>
                            </div>

                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-4 text-center border border-white/15">
                                <p class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Sedang Dilayani</p>
                                <p id="bannerNomorDilayani"
                                    data-jadwal-dilayani="{{ $jadwalAktif?->id }}"
                                    class="text-3xl font-black text-emerald-300 mt-1 transition-transform duration-300">
                                    {{ $sedangDilayani ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div id="emptyAntrianBanner"
                    class="card bg-base-100 shadow-sm rounded-2xl p-6 border border-dashed border-slate-300 text-center">
                    <div class="max-w-md mx-auto space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-primary flex items-center justify-center mx-auto">
                            <i class="fas fa-ticket text-xl"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-base">Belum Ada Antrian Aktif</h3>
                        <p class="text-xs text-slate-500">
                            Saat ini Anda tidak memiliki antrian pemeriksaan yang sedang berjalan. Silakan mendaftar ke poliklinik untuk mendapatkan nomor antrian.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tabel Jadwal Poliklinik & Dokter --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fas fa-calendar-check text-primary"></i>
                        Jadwal Poliklinik & Dokter
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Nomor antrian yang sedang dilayani diperbarui secara otomatis.
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Update
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="py-3.5 px-4 w-16 text-center">No</th>
                            <th class="py-3.5 px-4">Poli</th>
                            <th class="py-3.5 px-4">Dokter</th>
                            <th class="py-3.5 px-4">Hari</th>
                            <th class="py-3.5 px-4">Jam Periksa</th>
                            <th class="py-3.5 px-4 text-center">Nomor Sedang Dilayani</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($jadwals as $index => $jadwal)
                            @php
                                $nomorJadwal = $nomorDilayani[$jadwal->id] ?? null;
                            @endphp
                            <tr class="hover:bg-slate-50 transition" data-row-jadwal="{{ $jadwal->id }}">
                                <td class="py-3.5 px-4 text-center font-semibold text-slate-600">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $jadwal->dokter?->poli?->nama_poli ?? 'Poli Umum' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    dr. {{ $jadwal->dokter?->nama ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="badge badge-ghost font-medium">{{ $jadwal->hari }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ substr((string) $jadwal->jam_mulai, 0, 5) }} - {{ substr((string) $jadwal->jam_selesai, 0, 5) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span data-jadwal-dilayani="{{ $jadwal->id }}"
                                        class="inline-flex items-center justify-center min-w-10 h-10 px-3 rounded-xl bg-indigo-50 font-extrabold text-indigo-700 text-base shadow-sm transition-all duration-300">
                                        {{ $nomorJadwal ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <i class="fas fa-calendar-xmark text-3xl mb-2 block"></i>
                                    Belum ada jadwal periksa yang tersedia.
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
            const statusEndpoint = @json(route('pasien.antrian.status'));

            function formatNomor(nilai) {
                return (nilai === null || nilai === undefined || nilai === '') ? '-' : String(nilai);
            }

            function perbaruiStatusGiliranBanner(nomorDilayani) {
                const banner = document.getElementById('bannerAntrianAktif');
                if (!banner) return;

                const noPasien = Number(banner.getAttribute('data-no-antrian-pasien'));
                const badgeText = document.getElementById('bannerStatusText');
                if (!badgeText) return;

                if (nomorDilayani !== null && nomorDilayani !== undefined && Number(nomorDilayani) === noPasien) {
                    badgeText.textContent = 'Giliran Anda Sedang Dilayani';
                } else {
                    badgeText.textContent = 'Antrian Aktif Anda';
                }
            }

            function perbaruiNomorAntrian(idJadwal, nomorDilayani) {
                const teksNomor = formatNomor(nomorDilayani);

                document.querySelectorAll(`[data-jadwal-dilayani="${idJadwal}"]`).forEach((el) => {
                    if (el.textContent.trim() !== teksNomor) {
                        el.textContent = teksNomor;
                        el.classList.add('scale-110');
                        setTimeout(() => el.classList.remove('scale-110'), 300);
                    }
                });

                const banner = document.getElementById('bannerAntrianAktif');
                if (banner && String(banner.getAttribute('data-jadwal-id')) === String(idJadwal)) {
                    perbaruiStatusGiliranBanner(nomorDilayani);
                }
            }

            function renderEmptyBanner() {
                const container = document.getElementById('containerBannerAntrian');
                if (!container || document.getElementById('emptyAntrianBanner')) return;

                container.innerHTML = `
                    <div id="emptyAntrianBanner"
                        class="card bg-base-100 shadow-sm rounded-2xl p-6 border border-dashed border-slate-300 text-center">
                        <div class="max-w-md mx-auto space-y-2">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-base">Pemeriksaan Selesai / Belum Ada Antrian Aktif</h3>
                            <p class="text-xs text-slate-500">
                                Saat ini Anda tidak memiliki antrian pemeriksaan yang sedang berjalan. Silakan mendaftar ke poliklinik untuk mendapatkan nomor antrian baru.
                            </p>
                        </div>
                    </div>
                `;
            }

            function renderActiveBanner(aktif) {
                const container = document.getElementById('containerBannerAntrian');
                if (!container) return;

                const existingBanner = document.getElementById('bannerAntrianAktif');
                const teksDilayani = formatNomor(aktif.nomor_dilayani);

                if (!existingBanner) {
                    const wrapper = document.createElement('div');
                    wrapper.id = 'bannerAntrianAktif';
                    wrapper.className = 'card bg-gradient-to-r from-[#1e2d6b] to-[#2d4499] text-white shadow-lg rounded-2xl p-6 border border-indigo-400/20';
                    wrapper.innerHTML = `
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <div class="space-y-2">
                                <div id="bannerStatusBadge" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                                    <span id="bannerStatusText">Antrian Aktif Anda</span>
                                </div>
                                <h3 id="bannerNamaPoli" class="text-2xl font-extrabold tracking-tight"></h3>
                                <p class="text-sm text-indigo-100 flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <span><i class="fas fa-user-doctor mr-1.5 text-indigo-300"></i>dr. <span id="bannerNamaDokter"></span></span>
                                    <span><i class="fas fa-calendar-day mr-1.5 text-indigo-300"></i><span id="bannerJadwal"></span></span>
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 sm:gap-6 shrink-0">
                                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-4 text-center border border-white/15">
                                    <p class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Nomor Antrian Anda</p>
                                    <p id="bannerNomorPasien" class="text-3xl font-black text-amber-300 mt-1"></p>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-4 text-center border border-white/15">
                                    <p class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Sedang Dilayani</p>
                                    <p id="bannerNomorDilayani" data-jadwal-dilayani="${aktif.id_jadwal ?? ''}" class="text-3xl font-black text-emerald-300 mt-1">${teksDilayani}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    container.replaceChildren(wrapper);
                }

                const banner = document.getElementById('bannerAntrianAktif');
                banner.setAttribute('data-jadwal-id', aktif.id_jadwal ?? '');
                banner.setAttribute('data-no-antrian-pasien', aktif.no_antrian);

                const elPoli = document.getElementById('bannerNamaPoli');
                const elDokter = document.getElementById('bannerNamaDokter');
                const elJadwal = document.getElementById('bannerJadwal');
                const elNoPasien = document.getElementById('bannerNomorPasien');
                const elNoDilayani = document.getElementById('bannerNomorDilayani');

                if (elPoli) elPoli.textContent = aktif.nama_poli;
                if (elDokter) elDokter.textContent = aktif.nama_dokter;
                if (elJadwal) elJadwal.textContent = `${aktif.hari}, ${aktif.jam_mulai} - ${aktif.jam_selesai}`;
                if (elNoPasien) elNoPasien.textContent = String(aktif.no_antrian);
                if (elNoDilayani) {
                    elNoDilayani.setAttribute('data-jadwal-dilayani', aktif.id_jadwal ?? '');
                    elNoDilayani.textContent = teksDilayani;
                }

                perbaruiStatusGiliranBanner(aktif.nomor_dilayani);
            }

            async function sinkronkanStatusAntrian() {
                try {
                    const response = await fetch(statusEndpoint, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    const peta = data.nomor_dilayani_per_jadwal || {};

                    document.querySelectorAll('[data-row-jadwal]').forEach((row) => {
                        const idJadwal = row.getAttribute('data-row-jadwal');
                        const nomor = Object.prototype.hasOwnProperty.call(peta, idJadwal) ? peta[idJadwal] : null;
                        perbaruiNomorAntrian(idJadwal, nomor);
                    });

                    if (data.antrian_aktif) {
                        renderActiveBanner(data.antrian_aktif);
                    } else {
                        renderEmptyBanner();
                    }
                } catch (e) {
                    // Abaikan gangguan jaringan sementara
                }
            }

            window.perbaruiNomorAntrian = perbaruiNomorAntrian;

            function daftarkanEchoListener() {
                if (window.Echo) {
                    window.Echo.channel('antrian')
                        .listen('.antrian.diperbarui', (payload) => {
                            if (payload && payload.id_jadwal !== undefined) {
                                perbaruiNomorAntrian(payload.id_jadwal, payload.nomor_dilayani);
                                sinkronkanStatusAntrian();
                            }
                        });
                    return true;
                }
                return false;
            }

            if (!daftarkanEchoListener()) {
                window.addEventListener('load', daftarkanEchoListener);
            }

            // Sinkronisasi berkala memastikan tampilan selalu real-time
            setInterval(sinkronkanStatusAntrian, 2000);
        </script>
    @endpush

</x-layouts.app>
