@extends('layouts.admin')

@section('title', 'Laporan & Analitik Distribusi')
@section('page_title', 'Laporan Operasional Distribusi')
@section('page_subtitle', 'Analisis performa pengiriman, tingkat keberhasilan, dan utilisasi armada kendaraan')

@section('content')
<div class="space-y-6">

    <!-- Filter & Date Range Header -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-white text-base">Periode Laporan Distribusi</h3>
            <p class="text-xs text-slate-400">Rentang waktu: <strong class="text-blue-400">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</strong></p>
        </div>

        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            <span class="text-xs text-slate-500">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs">
                Tampilkan
            </button>
            <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Cetak</span>
            </button>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl">
            <p class="text-xs text-slate-400 uppercase font-semibold">Total Order Pengiriman</p>
            <h3 class="text-2xl font-black text-white mt-1">{{ $totalDeliveries }} <span class="text-xs font-normal text-slate-400">Order</span></h3>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl">
            <p class="text-xs text-slate-400 uppercase font-semibold">Selesai Terkirim</p>
            <h3 class="text-2xl font-black text-emerald-400 mt-1">{{ $completedDeliveries }} <span class="text-xs font-normal text-slate-400">Sukses</span></h3>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl">
            <p class="text-xs text-slate-400 uppercase font-semibold">Kendala / Gagal</p>
            <h3 class="text-2xl font-black text-rose-400 mt-1">{{ $failedDeliveries }} <span class="text-xs font-normal text-slate-400">Gagal</span></h3>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl">
            <p class="text-xs text-slate-400 uppercase font-semibold">Tingkat Keberhasilan (SLA)</p>
            <h3 class="text-2xl font-black text-cyan-400 mt-1">{{ $successRate }}%</h3>
        </div>
    </div>

    <!-- Charts & Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Distribution Chart -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-blue-400"></i>
                <span>Distribusi Status Order</span>
            </h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Driver Performance Rankings -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-400"></i>
                <span>Performa Pengemudi (Driver Performance)</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                            <th class="py-2.5 px-3">Nama Driver</th>
                            <th class="py-2.5 px-3">Lisensi SIM</th>
                            <th class="py-2.5 px-3">Total Order</th>
                            <th class="py-2.5 px-3">Terkirim</th>
                            <th class="py-2.5 px-3">Success Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($drivers as $drv)
                            <tr>
                                <td class="py-3 px-3 font-semibold text-white">{{ $drv['name'] }}</td>
                                <td class="py-3 px-3 text-slate-400 font-mono text-[11px]">{{ $drv['license'] ?? '-' }}</td>
                                <td class="py-3 px-3 text-slate-200 font-bold">{{ $drv['total_orders'] }}</td>
                                <td class="py-3 px-3 text-emerald-400 font-bold">{{ $drv['completed_orders'] }}</td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $drv['rate'] }}%;"></div>
                                        </div>
                                        <span class="font-bold text-slate-300 text-[11px]">{{ $drv['rate'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-500">Belum ada aktivitas driver pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Vehicles Utilization -->
    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
        <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-truck-ramp-box text-emerald-400"></i>
            <span>Utilisasi Armada Kendaraan</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($vehicles as $v)
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-white text-sm font-mono">{{ $v->plate_number }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $v->status_badge }}">
                            {{ ucfirst($v->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">{{ $v->brand_model }}</p>
                    <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Total Pengiriman:</span>
                        <strong class="text-blue-400">{{ $v->total_orders }} Perjalanan</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('statusChart').getContext('2d');
        const rawData = @json($statusBreakdown);

        const labels = Object.keys(rawData).length > 0 ? Object.keys(rawData) : ['DELIVERED', 'EN_ROUTE', 'ARRIVED'];
        const values = Object.keys(rawData).length > 0 ? Object.values(rawData) : [1, 1, 1];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b',
                        '#06b6d4',
                        '#8b5cf6',
                        '#f43f5e'
                    ],
                    borderColor: '#0f172a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
