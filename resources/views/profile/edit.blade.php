<x-layouts.app title="Profile & Ganti Password">

    <div class="max-w-3xl mx-auto space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Profile Pengguna</h2>
            <p class="text-sm text-slate-500">
                Informasi akun Anda dan pengaturan ganti password.
            </p>
        </div>

        {{-- Info Akun --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-primary text-white flex items-center justify-center font-extrabold text-xl">
                    {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800">{{ $user->nama }}</h3>
                    <p class="text-xs text-slate-500">{{ $user->email }} &bull; <span class="capitalize font-semibold text-primary">{{ $user->role }}</span></p>
                    @if($user->no_rm)
                        <p class="text-xs text-slate-500 mt-0.5">No. RM: <span class="font-semibold">{{ $user->no_rm }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Ganti Password --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                <i class="fas fa-key text-primary"></i>
                Ganti Password
            </h3>

            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="current_password">
                        Password Lama <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                        id="current_password"
                        name="current_password"
                        class="input input-bordered w-full rounded-xl text-sm @error('current_password') input-error @enderror"
                        placeholder="Masukkan password lama Anda"
                        required>
                    @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="password">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                        id="password"
                        name="password"
                        class="input input-bordered w-full rounded-xl text-sm @error('password') input-error @enderror"
                        placeholder="Minimal 8 karakter, berbeda dari password lama"
                        required>
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1" for="password_confirmation">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="input input-bordered w-full rounded-xl text-sm @error('password_confirmation') input-error @enderror"
                        placeholder="Ulangi password baru"
                        required>
                    @error('password_confirmation')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary rounded-xl text-white font-semibold px-6">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>

    </div>

</x-layouts.app>

