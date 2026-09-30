@extends('layouts.admin')

@section('title', 'Kelola Pengguna Sistem')
@section('page_title', 'Manajemen Pengguna & Hak Akses')
@section('page_subtitle', 'Kelola akun Administrator, Owner, dan Driver beserta kontrol akses (RBAC)')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-white text-base">Daftar Pengguna Sistem (RBAC)</h3>
            <p class="text-xs text-slate-400">Total terdaftar: <strong class="text-blue-400">{{ $users->total() }} Akun</strong></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, nomor..." 
                    class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                <select name="role" class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Peran</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="driver" {{ request('role') === 'driver' ? 'selected' : '' }}>Driver</option>
                </select>
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs">Filter</button>
            </form>

            <button type="button" onclick="document.getElementById('add-user-modal').classList.remove('hidden')" 
                class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs shadow-lg shadow-blue-500/20 flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Email & Kontak</th>
                        <th class="py-3 px-4">Peran (Role)</th>
                        <th class="py-3 px-4">Status Akun</th>
                        <th class="py-3 px-4">Login Terakhir</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold text-white block">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300 block font-medium">{{ $user->email }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $user->phone ?? 'Tanpa nomor telepon' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    {{ match($user->role) {
                                        'admin' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                                        'owner' => 'bg-indigo-500/15 text-indigo-400 border border-indigo-500/30',
                                        'driver' => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
                                        default => 'bg-slate-700 text-slate-300'
                                    } }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all
                                        {{ $user->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-rose-500/20 hover:text-rose-300' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30 hover:bg-emerald-500/20 hover:text-emerald-300' }}">
                                        {{ $user->is_active ? 'Aktif (Klik Nonaktif)' : 'Nonaktif (Klik Aktif)' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah login' }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <button type="button" onclick="editUser({{ json_encode($user) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 transition-colors" title="Edit Akun">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Hapus Akun">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Tidak ada akun yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Pengguna -->
<div id="add-user-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-blue-400"></i>
                <span>Daftarkan Pengguna Baru</span>
            </h3>
            <button onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Tri Sutrisno"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email *</label>
                <input type="email" name="email" required placeholder="email@armada.test"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Peran (Role) *</label>
                    <select name="role" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        @if(auth()->user()->isAdmin())
                            <option value="admin">Administrator</option>
                        @endif
                        <option value="owner">Owner / Distributor</option>
                        <option value="driver" selected>Driver (Pengemudi)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password * (Minimal 8 Karakter)</label>
                <input type="password" name="password" required value="password123"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" value="1" checked id="add-active" class="rounded bg-slate-800 border-slate-700 text-blue-600">
                <label for="add-active" class="text-xs text-slate-300">Akun Langsung Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/30">
                    Daftarkan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div id="edit-user-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-blue-400"></i>
                <span>Edit Data Pengguna</span>
            </h3>
            <button onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="edit-user-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                <input type="text" id="edit-name" name="name" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email *</label>
                <input type="email" id="edit-email" name="email" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Peran *</label>
                    <select id="edit-role" name="role" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        @if(auth()->user()->isAdmin())
                            <option value="admin">Administrator</option>
                        @endif
                        <option value="owner">Owner / Distributor</option>
                        <option value="driver">Driver (Pengemudi)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                    <input type="text" id="edit-phone" name="phone"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Ganti Password (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="Password baru..."
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" value="1" id="edit-active" class="rounded bg-slate-800 border-slate-700 text-blue-600">
                <label for="edit-active" class="text-xs text-slate-300">Status Akun Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/30">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editUser(user) {
        document.getElementById('edit-user-form').action = `/admin/users/${user.id}`;
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-role').value = user.role;
        document.getElementById('edit-phone').value = user.phone || '';
        document.getElementById('edit-active').checked = !!user.is_active;

        document.getElementById('edit-user-modal').classList.remove('hidden');
    }
</script>
@endpush
