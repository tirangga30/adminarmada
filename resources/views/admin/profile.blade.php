@extends('layouts.admin')

@section('title', 'Profil Akun')
@section('page_title', 'Profil & Pengaturan Akun')
@section('page_subtitle', 'Kelola informasi identitas akun, kontak, dan keamanan kata sandi Anda')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-6">
        <div class="flex items-center gap-4 pb-4 border-b border-slate-800">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-400 text-white flex items-center justify-center font-extrabold text-2xl shadow-xl shadow-blue-500/25">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                <div class="inline-block mt-1 px-2.5 py-0.5 rounded-full bg-blue-500/15 border border-blue-500/30 text-blue-400 text-[10px] font-bold uppercase">
                    {{ $user->role }}
                </div>
            </div>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email (Permanen)</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full px-3.5 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-xs text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx"
                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="pt-4 border-t border-slate-800 space-y-3">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Ganti Kata Sandi (Opsional)</span>
                
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Password Lama</label>
                    <input type="password" name="current_password" placeholder="Masukkan password saat ini..."
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Password Baru (Min. 8 karakter)</label>
                        <input type="password" name="new_password" placeholder="Password baru..."
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" placeholder="Ulangi password baru..."
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-800">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/25 flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan Profil</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
