@php
    $baseLink = "flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200";
    $active = "bg-white/20 text-white shadow-sm font-semibold backdrop-blur-sm border-l-4 border-amber-400";
    $inactive = "text-indigo-100/70 hover:bg-white/10 hover:text-white";
    $role = auth()->user()->role ?? '';
@endphp

<aside class="h-full flex flex-col justify-between text-white">

    {{-- Brand Header --}}
    <div class="p-5 border-b border-white/10 flex items-center gap-3">
        <img src="{{ asset('images/logo-bengkot.png') }}" alt="Logo" class="w-10 h-10 rounded-xl object-cover bg-white/10 p-1 shadow">
        <div>
            <h1 class="font-extrabold text-lg leading-tight tracking-wide text-white">Poliklinik</h1>
            <p class="text-xs text-indigo-200/70 font-medium capitalize">{{ $role ? 'Portal ' . $role : 'Sistem Informasi' }}</p>
        </div>
    </div>

    {{-- Menu List --}}
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">

        {{-- ================= ADMIN ================= --}}
        @if($role === 'admin')
            <div>
                <p class="text-[11px] uppercase tracking-wider text-indigo-300 font-semibold px-3 mb-2">
                    Menu Admin
                </p>
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('admin.dashboard') ? $active : $inactive }}">
                        <i class="fas fa-chart-pie w-4 text-center"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('polis.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('polis.*') ? $active : $inactive }}">
                        <i class="fas fa-hospital w-4 text-center"></i>
                        Kelola Poli
                    </a>
                    <a href="{{ route('dokter.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('dokter.*') ? $active : $inactive }}">
                        <i class="fas fa-user-doctor w-4 text-center"></i>
                        Kelola Dokter
                    </a>
                    <a href="{{ route('pasien.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('pasien.*') ? $active : $inactive }}">
                        <i class="fas fa-users w-4 text-center"></i>
                        Kelola Pasien
                    </a>
                    <a href="{{ route('obat.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('obat.*') ? $active : $inactive }}">
                        <i class="fas fa-pills w-4 text-center"></i>
                        Kelola Obat
                    </a>
                    <a href="{{ route('admin.pembayaran.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('admin.pembayaran.*') ? $active : $inactive }}">
                        <i class="fas fa-file-invoice-dollar w-4 text-center"></i>
                        Verifikasi Pembayaran
                    </a>
                </div>
            </div>
        @endif

        {{-- ================= DOKTER ================= --}}
        @if($role === 'dokter')
            <div>
                <p class="text-[11px] uppercase tracking-wider text-indigo-300 font-semibold px-3 mb-2">
                    Menu Dokter
                </p>
                <div class="space-y-1">
                    <a href="{{ route('dokter.dashboard') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('dokter.dashboard') ? $active : $inactive }}">
                        <i class="fas fa-stethoscope w-4 text-center"></i>
                        Dashboard Dokter
                    </a>
                    <a href="{{ route('jadwal-periksa.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('jadwal-periksa.*') ? $active : $inactive }}">
                        <i class="fas fa-calendar-days w-4 text-center"></i>
                        Jadwal Periksa
                    </a>
                    <a href="{{ route('periksa-pasien.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('periksa-pasien.*') ? $active : $inactive }}">
                        <i class="fas fa-notes-medical w-4 text-center"></i>
                        Periksa Pasien
                    </a>
                </div>
            </div>
        @endif

        {{-- ================= PASIEN ================= --}}
        @if($role === 'pasien')
            <div>
                <p class="text-[11px] uppercase tracking-wider text-indigo-300 font-semibold px-3 mb-2">
                    Menu Pasien
                </p>
                <div class="space-y-1">
                    <a href="{{ route('pasien.dashboard') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('pasien.dashboard') ? $active : $inactive }}">
                        <i class="fas fa-house-chimney-medical w-4 text-center"></i>
                        Dashboard Pasien
                    </a>
                    <a href="{{ route('pasien.daftar') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('pasien.daftar*') ? $active : $inactive }}">
                        <i class="fas fa-clipboard-list w-4 text-center"></i>
                        Daftar Poli
                    </a>
                    <a href="{{ route('pasien.riwayat.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('pasien.riwayat.*') ? $active : $inactive }}">
                        <i class="fas fa-clock-rotate-left w-4 text-center"></i>
                        Riwayat Pendaftaran
                    </a>
                    <a href="{{ route('pasien.pembayaran.index') }}"
                        class="{{ $baseLink }} {{ request()->routeIs('pasien.pembayaran.*') ? $active : $inactive }}">
                        <i class="fas fa-credit-card w-4 text-center"></i>
                        Pembayaran
                    </a>
                </div>
            </div>
        @endif

        {{-- ================= AKUN ================= --}}
        <div>
            <p class="text-[11px] uppercase tracking-wider text-indigo-300 font-semibold px-3 mb-2">
                Akun
            </p>
            <div class="space-y-1">
                <a href="{{ route('notifikasi.index') }}"
                    class="{{ $baseLink }} {{ request()->routeIs('notifikasi.*') ? $active : $inactive }}">
                    <i class="fas fa-bell w-4 text-center"></i>
                    Notifikasi
                </a>
                <a href="{{ route('profile.edit') }}"
                    class="{{ $baseLink }} {{ request()->routeIs('profile.*') ? $active : $inactive }}">
                    <i class="fas fa-user-gear w-4 text-center"></i>
                    Profile
                </a>
            </div>
        </div>

    </div>

    {{-- ================= USER FOOTER & LOGOUT ================= --}}
    <div class="p-4 border-t border-white/10 bg-black/10">
        <div class="flex items-center gap-3 mb-3 px-1">
            <div class="w-8 h-8 rounded-full bg-indigo-500/50 flex items-center justify-center font-bold text-xs text-white">
                {{ strtoupper(substr(auth()->user()->nama ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->nama ?? 'Pengguna' }}</p>
                <p class="text-[10px] text-indigo-300 truncate capitalize">{{ auth()->user()->role ?? 'User' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-500/90 hover:bg-red-600 text-white text-xs font-semibold transition-all shadow-sm">
                <i class="fas fa-right-from-bracket w-3.5"></i>
                Keluar
            </button>
        </form>
    </div>

</aside>
