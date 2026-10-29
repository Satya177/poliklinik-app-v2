@php
    $currentUser = auth()->user();
    $unreadCount = $currentUser ? $currentUser->unreadNotifications()->count() : 0;
    $recentNotifications = $currentUser ? $currentUser->notifications()->latest()->limit(6)->get() : collect();
@endphp

<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i class="fas fa-bars w-5 h-5"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i class="fas fa-chevron-right text-xs text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Notifications Bell Dropdown --}}
    @if($currentUser)
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-square btn-ghost relative" aria-label="Notifikasi">
                <i class="fas fa-bell text-base"></i>
                @if($unreadCount > 0)
                    <span id="unreadNotificationBadge"
                        class="badge badge-error badge-xs text-white font-bold absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </div>

            <div tabindex="0"
                class="dropdown-content bg-base-100 rounded-2xl z-50 mt-3 w-80 sm:w-96 shadow-xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-slate-800">Notifikasi</span>
                        @if($unreadCount > 0)
                            <span class="badge badge-primary badge-sm text-white">{{ $unreadCount }} baru</span>
                        @endif
                    </div>

                    @if($unreadCount > 0)
                        <form action="{{ route('notifikasi.read-all') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs font-semibold text-primary hover:underline">
                                Tandai semua dibaca
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                    @forelse($recentNotifications as $notif)
                        @php
                            $isUnread = is_null($notif->read_at);
                        @endphp
                        <div class="p-3.5 transition {{ $isUnread ? 'bg-indigo-50/70 border-l-4 border-primary' : 'bg-white opacity-75' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        @if($isUnread)
                                            <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                                        @endif
                                        <p class="text-xs {{ $isUnread ? 'font-bold text-slate-900' : 'font-medium text-slate-600' }}">
                                            {{ $notif->data['judul'] ?? 'Notifikasi' }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        {{ $notif->data['pesan'] ?? '' }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        <i class="far fa-clock mr-1"></i>{{ $notif->created_at?->diffForHumans() }}
                                    </p>
                                </div>

                                @if($isUnread)
                                    <form action="{{ route('notifikasi.read', $notif->id) }}" method="POST" class="shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-ghost btn-xs text-primary hover:bg-indigo-100 rounded-lg"
                                            title="Tandai sudah dibaca">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-ghost badge-xs text-[10px]">Dibaca</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-10 px-4 text-center text-slate-400 text-xs">
                            <i class="far fa-bell-slash text-2xl mb-2 block text-slate-300"></i>
                            Belum ada notifikasi.
                        </div>
                    @endforelse
                </div>

                <div class="p-2.5 border-t border-slate-100 bg-slate-50 text-center">
                    <a href="{{ route('notifikasi.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        Lihat Semua Notifikasi
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- User Info --}}
    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 hover:opacity-80 transition">
        <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold leading-tight">
                {{ $currentUser->nama ?? 'Pengguna' }}
            </div>
            <div class="text-xs text-base-content/50 leading-tight capitalize">
                {{ $currentUser->role ?? 'Admin Sistem' }}
            </div>
        </div>

        <div class="avatar">
            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                <span class="text-sm font-semibold leading-none">
                    {{ strtoupper(substr($currentUser->nama ?? 'U', 0, 1)) }}
                </span>
            </div>
        </div>
    </a>

</header>
