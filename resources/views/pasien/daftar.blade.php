<x-layouts.app title="Daftar Poli">

    <div class="space-y-6">

        {{-- Page Title --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Daftar Pemeriksaan Poli</h2>
            <p class="text-sm text-slate-500">Pilih poli dan jadwal dokter untuk mendapatkan nomor antrean periksa.</p>
        </div>

        @if(!empty($antrianAktif))
            <div class="alert alert-warning rounded-2xl shadow-sm">
                <i class="fas fa-triangle-exclamation"></i>
                <span>
                    Anda masih memiliki antrian aktif (Nomor Antrian <strong>#{{ $antrianAktif->no_antrian }}</strong>
                    pada <strong>{{ $antrianAktif->jadwalPeriksa?->dokter?->poli?->nama_poli ?? 'Poli' }}</strong>).
                    Pendaftaran poli baru hanya dapat dilakukan setelah proses pemeriksaan sebelumnya selesai.
                </span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom Kiri: Form Pendaftaran --}}
            <div class="lg:col-span-1 space-y-6">

                {{-- Info Pasien --}}
                <div class="card bg-gradient-to-br from-[#1e2d6b] to-[#2d4499] text-white shadow-md rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-200">Nomor Rekam Medis</p>
                            <p class="font-extrabold text-lg tracking-wider text-amber-300">{{ $user->no_rm ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="border-t border-white/10 pt-3 text-xs space-y-1 text-indigo-100">
                        <p><span class="opacity-70">Nama:</span> <span class="font-semibold">{{ $user->nama }}</span></p>
                        <p><span class="opacity-70">No. KTP:</span> {{ $user->no_ktp ?? '-' }}</p>
                        <p><span class="opacity-70">No. HP:</span> {{ $user->no_hp ?? '-' }}</p>
                    </div>
                </div>

                {{-- Form Card --}}
                <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-6">
                    <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-plus text-primary"></i>
                        Formulir Pendaftaran
                    </h3>

                    <form action="{{ route('pasien.daftar.submit') }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Pilih Poli --}}
                        <div>
                            <label class="label pb-1" for="selectPoli">
                                <span class="text-xs font-semibold text-slate-700">Pilih Poli <span class="text-red-500">*</span></span>
                            </label>
                            <select name="id_poli" id="selectPoli" class="select select-bordered w-full rounded-xl text-sm" required onchange="filterJadwal()" @disabled(!empty($antrianAktif))>
                                <option value="" disabled {{ old('id_poli') ? '' : 'selected' }}>-- Pilih Poli Tujuan --</option>
                                @foreach($polis as $poli)
                                    <option value="{{ $poli->id }}" {{ (string) old('id_poli') === (string) $poli->id ? 'selected' : '' }}>
                                        {{ $poli->nama_poli }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_poli')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pilih Jadwal Dokter --}}
                        <div>
                            <label class="label pb-1" for="selectJadwal">
                                <span class="text-xs font-semibold text-slate-700">Pilih Dokter & Jadwal <span class="text-red-500">*</span></span>
                            </label>
                            <select name="id_jadwal" id="selectJadwal" class="select select-bordered w-full rounded-xl text-sm" required @disabled(!empty($antrianAktif))>
                                <option value="" disabled selected>-- Pilih poli tujuan terlebih dahulu --</option>
                                @foreach($jadwals as $jadwal)
                                    <option value="{{ $jadwal->id }}" data-poli="{{ $jadwal->dokter->id_poli ?? '' }}" class="jadwal-option">
                                        dr. {{ $jadwal->dokter->nama ?? 'Dokter' }} ({{ $jadwal->hari }}, {{ substr((string)$jadwal->jam_mulai, 0, 5) }} - {{ substr((string)$jadwal->jam_selesai, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_jadwal')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror

                            {{-- Peringatan jika belum ada dokter/jadwal pada poli yang dipilih --}}
                            <div id="noScheduleNotice" class="hidden alert alert-warning text-xs mt-2 py-2.5 px-3 rounded-xl border border-amber-300">
                                <i class="fas fa-circle-exclamation text-amber-700"></i>
                                <span class="text-amber-800 font-medium">Belum ada dokter atau jadwal periksa yang tersedia pada poli ini. Silakan pilih poli lain.</span>
                            </div>
                        </div>

                        {{-- Keluhan --}}
                        <div>
                            <label class="label pb-1" for="keluhan">
                                <span class="text-xs font-semibold text-slate-700">Keluhan Penyakit <span class="text-red-500">*</span></span>
                            </label>
                            <textarea id="keluhan" name="keluhan" rows="3" class="textarea textarea-bordered w-full rounded-xl text-sm" placeholder="Tuliskan gejala atau keluhan yang dirasakan..." required @disabled(!empty($antrianAktif))>{{ old('keluhan') }}</textarea>
                            @error('keluhan')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" id="submitDaftarBtn" class="btn btn-primary w-full rounded-xl text-white font-semibold shadow-sm" @disabled(!empty($antrianAktif))>
                            <i class="fas fa-paper-plane mr-1"></i>
                            Daftar Sekarang
                        </button>
                    </form>
                </div>

            </div>

            {{-- Kolom Kanan: Riwayat Pendaftaran Pasien --}}
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fas fa-clock-rotate-left text-primary"></i>
                            Riwayat Pendaftaran Poli
                        </h3>
                        <a href="{{ route('pasien.riwayat.index') }}" class="text-xs font-semibold text-primary hover:underline">
                            Lihat Halaman Riwayat
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="py-3 px-4">No. Antrean</th>
                                    <th class="py-3 px-4">Poli & Dokter</th>
                                    <th class="py-3 px-4">Hari & Jam</th>
                                    <th class="py-3 px-4">Keluhan</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-100">
                                @forelse($riwayatDaftar as $riwayat)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-50 font-extrabold text-indigo-700 text-base shadow-sm">
                                                {{ $riwayat->no_antrian }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <p class="font-semibold text-slate-800">{{ $riwayat->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli Umum' }}</p>
                                            <p class="text-xs text-slate-500">dr. {{ $riwayat->jadwalPeriksa->dokter->nama ?? 'Dokter' }}</p>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-600">
                                            <p class="font-medium text-slate-700">{{ $riwayat->jadwalPeriksa->hari ?? '-' }}</p>
                                            <p>{{ substr((string)$riwayat->jadwalPeriksa->jam_mulai, 0, 5) }} - {{ substr((string)$riwayat->jadwalPeriksa->jam_selesai, 0, 5) }}</p>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-600 max-w-[180px] truncate">
                                            {{ $riwayat->keluhan ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($riwayat->periksa)
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-12 text-slate-400">
                                            <i class="fas fa-calendar-xmark text-3xl mb-2 block"></i>
                                            Belum ada riwayat pendaftaran.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        const allJadwals = @json($jadwals);
        const oldJadwalId = @json(old('id_jadwal'));
        const hasActiveAntrian = @json(!empty($antrianAktif));

        function filterJadwal() {
            if (hasActiveAntrian) return;

            const selectPoli = document.getElementById('selectPoli');
            const selectJadwal = document.getElementById('selectJadwal');
            const noScheduleNotice = document.getElementById('noScheduleNotice');
            const submitBtn = document.getElementById('submitDaftarBtn');

            if (!selectPoli || !selectJadwal) return;

            const idPoli = selectPoli.value;
            selectJadwal.innerHTML = '';

            if (!idPoli) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.selected = true;
                opt.textContent = '-- Pilih poli tujuan terlebih dahulu --';
                selectJadwal.appendChild(opt);
                selectJadwal.disabled = true;
                if (noScheduleNotice) noScheduleNotice.classList.add('hidden');
                return;
            }

            // Filter data jadwal berdasarkan id_poli dokter
            const filtered = allJadwals.filter(item => {
                return item.dokter && String(item.dokter.id_poli) === String(idPoli);
            });

            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.selected = true;
                opt.textContent = '-- Belum ada dokter/jadwal untuk poli ini --';
                selectJadwal.appendChild(opt);
                selectJadwal.disabled = true;
                if (noScheduleNotice) noScheduleNotice.classList.remove('hidden');
            } else {
                selectJadwal.disabled = false;
                if (noScheduleNotice) noScheduleNotice.classList.add('hidden');

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.disabled = true;
                defaultOpt.selected = true;
                defaultOpt.textContent = '-- Pilih Dokter & Jadwal Periksa --';
                selectJadwal.appendChild(defaultOpt);

                let hasSelectedOld = false;
                filtered.forEach(j => {
                    const opt = document.createElement('option');
                    opt.value = j.id;

                    const namaDokter = j.dokter ? j.dokter.nama : 'Dokter';
                    const jamMulai = j.jam_mulai ? String(j.jam_mulai).substring(0, 5) : '';
                    const jamSelesai = j.jam_selesai ? String(j.jam_selesai).substring(0, 5) : '';
                    opt.textContent = `dr. ${namaDokter} (${j.hari}, ${jamMulai} - ${jamSelesai})`;

                    if (oldJadwalId && String(j.id) === String(oldJadwalId)) {
                        opt.selected = true;
                        defaultOpt.selected = false;
                        hasSelectedOld = true;
                    }

                    selectJadwal.appendChild(opt);
                });
            }
        }

        // Jalankan saat halaman siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', filterJadwal);
        } else {
            filterJadwal();
        }
    </script>
    @endpush

</x-layouts.app>
