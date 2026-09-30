@extends('layouts.admin')

@section('title', 'Manajemen Pengiriman')
@section('page_title', 'Daftar Order Pengiriman')
@section('page_subtitle', 'Kelola penugasan distribusi barang, rute, dan intervensi status admin')

@section('content')
<div class="space-y-6">

    <!-- Action Bar & Filter Header -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-white text-base">Filter & Pencarian Pengiriman</h3>
                <p class="text-xs text-slate-400">Gunakan filter untuk mempermudah pelacakan dokumen pengiriman</p>
            </div>
            <a href="{{ route('admin.deliveries.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 self-start sm:self-auto">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Pengiriman Baru</span>
            </a>
        </div>

        <form action="{{ route('admin.deliveries.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-2">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. DO, Judul, Alamat..." 
                        class="w-full pl-9 pr-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Status ({{ count($statuses) }})</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Driver Filter -->
            <div>
                <select name="driver_id" class="w-full px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Driver</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium text-xs transition-colors">
                    Terapkan
                </button>
                <a href="{{ route('admin.deliveries.index') }}" class="py-2 px-3 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white text-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Deliveries Table Card -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">No. Pengiriman</th>
                        <th class="py-3 px-4">Driver & Kendaraan</th>
                        <th class="py-3 px-4">Tujuan / Stops</th>
                        <th class="py-3 px-4">Barang</th>
                        <th class="py-3 px-4">Prioritas</th>
                        <th class="py-3 px-4">Status Terkini</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-blue-400 block text-xs">{{ $order->order_number }}</span>
                                <span class="text-white font-medium block truncate max-w-xs">{{ $order->title }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($order->driver)
                                    <span class="text-slate-200 font-semibold block">{{ $order->driver->name }}</span>
                                    <span class="text-[11px] text-slate-400 block">{{ $order->vehicle->plate_number ?? '-' }} &bull; {{ $order->vehicle->brand_model ?? '-' }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-medium">
                                        Belum Ada Driver
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-white">{{ $order->stops->count() }} Titik Singgah</span>
                                <span class="text-[11px] text-slate-400 block truncate max-w-xs">
                                    {{ $order->stops->first()->customer_name ?? '-' }}
                                    @if($order->stops->count() > 1)
                                        (+{{ $order->stops->count() - 1 }} lainnya)
                                    @endif
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300 font-medium">{{ $order->items->count() }} Jenis Barang</span>
                                <span class="text-[11px] text-slate-400 block">Total Qty: {{ $order->items->sum('quantity') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $order->priority_badge }}">
                                    {{ strtoupper($order->priority) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide {{ $order->status_badge }}">
                                    {{ $order->status }}
                                </span>
                                @if($order->admin_override_notes)
                                    <span class="block text-[10px] text-amber-400 mt-1" title="{{ $order->admin_override_notes }}">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Overridden
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.deliveries.show', $order->id) }}" class="px-3 py-1.5 rounded-lg bg-blue-600/15 hover:bg-blue-600/25 text-blue-400 border border-blue-500/30 font-semibold text-xs inline-flex items-center gap-1.5 transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Detail & Support</span>
                                </a>
                                <form action="{{ route('admin.deliveries.destroy', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus order ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 transition-colors">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i class="fa-solid fa-box-open text-3xl mb-2 block text-slate-600"></i>
                                Tidak ada data pengiriman yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
