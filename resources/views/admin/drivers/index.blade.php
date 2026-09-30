@extends('layouts.admin')

@section('title', 'Data Pengemudi (Driver)')
@section('page_title', 'Manajemen Pengemudi (Driver)')
@section('page_subtitle', 'Kelola lisensi SIM, alokasi kendaraan, status tugas, dan histori lokasi driver')

@section('content')
<div class="space-y-6">

    <!-- Filters & Stats Header -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-white text-base">Daftar Pengemudi Armada</h3>
            <p class="text-xs text-slate-400">Total terdaftar: <strong class="text-blue-400">{{ $drivers->total() }} Driver</strong></p>
        </div>

        <form action="{{ route('admin.drivers.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, telp..." 
                class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            
            <select name="status" class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia (Siaga)</option>
                <option value="delivering" {{ request('status') === 'delivering' ? 'selected' : '' }}>Sedang Mengantar</option>
                <option value="off_duty" {{ request('status') === 'off_duty' ? 'selected' : '' }}>Off-Duty</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>

            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.drivers.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white text-xs">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Drivers Grid Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Nama & Akun</th>
                        <th class="py-3 px-4">Kendaraan Terpasang</th>
                        <th class="py-3 px-4">Lisensi (SIM)</th>
                        <th class="py-3 px-4">Status & Darurat</th>
                        <th class="py-3 px-4">GPS & Baterai</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($drivers as $driver)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($driver->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block">{{ $driver->user->name }}</span>
                                        <span class="text-[11px] text-slate-400 block">{{ $driver->user->phone ?? $driver->user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($driver->vehicle)
                                    <span class="font-bold text-slate-200 block">{{ $driver->vehicle->plate_number }}</span>
                                    <span class="text-[11px] text-slate-400 block">{{ $driver->vehicle->brand_model }}</span>
                                @else
                                    <span class="text-slate-500 italic">Belum Ada Kendaraan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/25 font-semibold text-[11px]">
                                    {{ $driver->license_type }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-1 font-mono">{{ $driver->license_number ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold
                                    {{ $driver->status === 'delivering' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($driver->status === 'available' ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30' : 'bg-slate-800 text-slate-400') }}">
                                    {{ ucfirst($driver->status) }}
                                </span>
                                @if($driver->emergency_status)
                                    <span class="block mt-1 text-[10px] text-rose-400 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> SOS AKTIF
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="space-y-0.5">
                                    <span class="text-slate-300 flex items-center gap-1">
                                        <i class="fa-solid fa-battery-half text-slate-400 text-[10px]"></i> {{ $driver->battery_level ?? '-' }}% &bull; {{ $driver->speed ?? 0 }} km/h
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">
                                        {{ $driver->last_location_time ? $driver->last_location_time->diffForHumans() : 'Belum pernah update' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('admin.drivers.show', $driver->id) }}" class="px-3 py-1.5 rounded-lg bg-blue-600/15 hover:bg-blue-600/25 text-blue-400 border border-blue-500/30 font-semibold text-xs inline-flex items-center gap-1.5 transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Detail & Rute</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Tidak ada data pengemudi yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($drivers->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
