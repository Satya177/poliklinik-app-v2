<x-layouts.app title="Notifikasi">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Daftar Notifikasi</h2>
                <p class="text-sm text-slate-500">
                    Semua pemberitahuan akun Anda diurutkan dari yang terbaru.
                </p>
            </div>

            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form action="{{ route('notifikasi.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="btn btn-primary btn-sm rounded-xl text-white font-semibold">
                        <i class="fas fa-check-double mr-1"></i>
                        Tandai Semua Sudah Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 overflow-hidden divide-y divide-slate-100">
            @forelse($notifikasis as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                @endphp
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition {{ $isUnread ? 'bg-indigo-50/70 border-l-4 border-primary' : 'bg-white opacity-80' }}">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            @if($isUnread)
                                <span class="badge badge-primary badge-xs text-white">Baru</span>
                            @else
                                <span class="badge badge-ghost badge-xs">Sudah Dibaca</span>
                            @endif
                            <h3 class="text-sm {{ $isUnread ? 'font-bold text-slate-900' : 'font-semibold text-slate-700' }}">
                                {{ $notif->data['judul'] ?? 'Notifikasi' }}
                            </h3>
                        </div>
                        <p class="text-sm text-slate-600">
                            {{ $notif->data['pesan'] ?? '' }}
                        </p>
                        <p class="text-xs text-slate-400">
                            <i class="far fa-clock mr-1"></i>
                            {{ $notif->created_at?->format('d M Y, H:i') }} ({{ $notif->created_at?->diffForHumans() }})
                        </p>
                    </div>

                    @if($isUnread)
                        <form action="{{ route('notifikasi.read', $notif->id) }}" method="POST" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline btn-primary btn-xs rounded-lg">
                                <i class="fas fa-check mr-1"></i>
                                Tandai Dibaca
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="py-12 px-6 text-center text-slate-400">
                    <i class="far fa-bell-slash text-4xl mb-3 block text-slate-300"></i>
                    Belum ada notifikasi.
                </div>
            @endforelse
        </div>

        <div>
            {{ $notifikasis->links() }}
        </div>

    </div>

</x-layouts.app>

