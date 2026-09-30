@extends('layouts.admin')

@section('title', 'Audit Log Sistem')
@section('page_title', 'Jejak Audit & Keamanan Sistem')
@section('page_subtitle', 'Catatan aktivitas pengguna, intervensi pengiriman, dan perubahan konfigurasi sistem')

@section('content')
<div class="space-y-6">

    <!-- Filter Header -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-white text-base">Rekam Jejak Audit Log (F-ADM-03)</h3>
            <p class="text-xs text-slate-400">Total aktivitas tercatat: <strong class="text-blue-400">{{ $logs->total() }} Log Event</strong></p>
        </div>

        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, user, IP..." 
                class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            
            <select name="action" class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                <option value="">Semua Aksi</option>
                @foreach($actions as $act)
                    <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs">Filter</button>
            @if(request()->anyFilled(['search', 'action']))
                <a href="{{ route('admin.audit-logs.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white text-xs">Reset</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Aktor Pengguna</th>
                        <th class="py-3 px-4">Aksi (Action)</th>
                        <th class="py-3 px-4">Target Entitas</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4">IP & Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 text-slate-400 whitespace-nowrap">
                                <span class="font-mono text-white block">{{ $log->created_at->format('H:i:s') }}</span>
                                <span class="text-[10px]">{{ $log->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $log->user_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $log->user?->role ?? 'System' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono
                                    {{ str_contains($log->action, 'OVERRIDE') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' :
                                       (str_contains($log->action, 'EMERGENCY') ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' :
                                       (str_contains($log->action, 'CREATED') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-blue-500/15 text-blue-400 border border-blue-500/30')) }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                <span class="font-semibold block text-slate-200">{{ $log->target_type }}</span>
                                <span class="text-[10px] text-slate-400">ID: {{ $log->target_id ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-200 max-w-sm">
                                <p class="line-clamp-2 leading-relaxed text-xs">{{ $log->description }}</p>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-[11px] text-slate-400">
                                <span class="font-mono block text-slate-300">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                @if($log->metadata)
                                    <button type="button" onclick="showMetadata({{ json_encode($log->metadata) }})" class="text-blue-400 hover:text-blue-300 underline text-[10px]">
                                        Lihat JSON
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Belum ada jejak audit log yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal JSON Metadata -->
<div id="metadata-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-code text-blue-400"></i>
                <span>Metadata Perubahan Data</span>
            </h3>
            <button onclick="document.getElementById('metadata-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <pre id="metadata-content" class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono text-emerald-400 overflow-x-auto max-h-96"></pre>

        <div class="flex justify-end pt-2">
            <button type="button" onclick="document.getElementById('metadata-modal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showMetadata(data) {
        document.getElementById('metadata-content').innerText = JSON.stringify(data, null, 2);
        document.getElementById('metadata-modal').classList.remove('hidden');
    }
</script>
@endpush
