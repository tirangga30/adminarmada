@extends('layouts.admin')

@section('title', 'Dashboard Operasional')
@section('page_title', 'Dashboard Operasional Armada')
@section('page_subtitle', 'Monitoring status pengiriman, armada kendaraan, dan lokasi driver realtime')

@section('content')
<div class="space-y-6">

    <!-- Emergency Alerts (If Any) -->
    @if($emergencyAlerts->count() > 0)
        <div class="p-4 rounded-2xl bg-rose-950/60 border border-rose-500/50 text-white shadow-xl shadow-rose-900/20 space-y-3">
            <div class="flex items-center gap-3 text-rose-400 font-bold text-sm">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <span>PERINGATAN DARURAT (SOS DRIVER AKTIF)</span>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($emergencyAlerts as $alert)
                    <div class="p-3 rounded-xl bg-rose-900/40 border border-rose-500/30 flex items-center justify-between gap-3 text-xs">
                        <div>
                            <p class="font-semibold text-rose-200">{{ $alert->user->name }}</p>
                            <p class="text-[11px] text-rose-300/80">{{ $alert->emergency_note ?? 'Sinyal darurat terdeteksi dari driver.' }}</p>
                            <p class="text-[10px] text-rose-400 mt-1">Telp: {{ $alert->user->phone ?? '-' }}</p>
                        </div>
                        <a href="{{ route('admin.drivers.show', $alert->id) }}" class="px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-medium shrink-0">
                            Respon
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Top KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Card 1: Active Drivers -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl relative overflow-hidden group hover:border-blue-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pengemudi</p>
                    <h3 class="text-2xl font-black text-white mt-1">{{ $totalDrivers }} <span class="text-xs font-normal text-slate-400">Driver</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> {{ $deliveringDrivers }} Mengirim</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-400"></span> {{ $availableDrivers }} Siap</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-500"></span> {{ $offDutyDrivers }} Off</span>
            </div>
        </div>

        <!-- Card 2: Vehicles Fleet -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl relative overflow-hidden group hover:border-emerald-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Armada Kendaraan</p>
                    <h3 class="text-2xl font-black text-white mt-1">{{ $totalVehicles }} <span class="text-xs font-normal text-slate-400">Unit</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> {{ $inUseVehicles }} Beroperasi</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-400"></span> {{ $availableVehicles }} Siaga</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> {{ $maintenanceVehicles }} Servis</span>
            </div>
        </div>

        <!-- Card 3: Active Deliveries -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl relative overflow-hidden group hover:border-indigo-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pengiriman Berjalan</p>
                    <h3 class="text-2xl font-black text-white mt-1">{{ $activeDeliveries }} <span class="text-xs font-normal text-slate-400">Order</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-route"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>Total Hari Ini: <strong class="text-white">{{ $todayDeliveries }}</strong></span>
                <a href="{{ route('admin.deliveries.index') }}" class="text-blue-400 hover:text-blue-300 font-medium">Lihat Semua &rarr;</a>
            </div>
        </div>

        <!-- Card 4: Selesai vs Gagal -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl relative overflow-hidden group hover:border-cyan-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Selesai Hari Ini</p>
                    <h3 class="text-2xl font-black text-white mt-1">{{ $deliveredDeliveries }} <span class="text-xs font-normal text-slate-400">Terkirim</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span class="text-emerald-400"><i class="fa-solid fa-check"></i> Selesai: {{ $deliveredDeliveries }}</span>
                <span class="text-rose-400"><i class="fa-solid fa-xmark"></i> Kendala/Gagal: {{ $failedDeliveries }}</span>
            </div>
        </div>
    </div>

    <!-- Live Fleet Map Section -->
    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-blue-400"></i>
                    <span>Live Monitoring Peta Armada</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-medium">Realtime GPS</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Posisi GPS driver smartphone aktif di wilayah Tasikmalaya dan sekitarnya</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <a href="{{ route('admin.tracking') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-expand"></i>
                    <span>Buka Layar Penuh</span>
                </a>
                <button type="button" onclick="refreshMap()" class="px-3 py-1.5 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 border border-blue-500/30 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Segarkan</span>
                </button>
            </div>
        </div>

        <!-- The Map Container -->
        <div id="dashboard-map" class="w-full h-96 sm:h-[450px] rounded-xl border border-slate-800 overflow-hidden relative z-10"></div>

        <!-- Driver Quick List below Map -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
            @forelse($activeDrivers as $drv)
                <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between gap-3 text-xs hover:border-slate-700 transition-colors">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/15 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ substr($drv['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold text-white">{{ $drv['name'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $drv['plate_number'] }} &bull; {{ $drv['speed'] }} km/h</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium {{ $drv['status'] === 'delivering' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-700 text-slate-300' }}">
                            {{ $drv['status'] === 'delivering' ? 'Mengantar' : 'Siaga' }}
                        </span>
                        <p class="text-[10px] text-slate-400 mt-1"><i class="fa-solid fa-battery-half text-slate-400"></i> {{ $drv['battery'] ?? '-' }}%</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-4 text-xs text-slate-500">
                    Belum ada sinyal GPS aktif saat ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2 Column Section: Recent Deliveries & Audit Trail -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Deliveries (2 Cols) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-box-open text-indigo-400"></i>
                        <span>Order Pengiriman Terbaru</span>
                    </h2>
                    <p class="text-xs text-slate-400">Aktivitas penugasan dan status perjalanan barang</p>
                </div>
                <a href="{{ route('admin.deliveries.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-medium">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                            <th class="py-2.5 px-3">No. DO & Judul</th>
                            <th class="py-2.5 px-3">Driver & Armada</th>
                            <th class="py-2.5 px-3">Prioritas</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentDeliveries as $order)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-white block">{{ $order->order_number }}</span>
                                    <span class="text-slate-400 text-[11px] truncate max-w-xs block">{{ $order->title }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="text-slate-200 block font-medium">{{ $order->driver->name ?? 'Belum Ditugaskan' }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $order->vehicle->plate_number ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $order->priority_badge }}">
                                        {{ strtoupper($order->priority) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $order->status_badge }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right space-x-1">
                                    <a href="{{ route('admin.deliveries.show', $order->id) }}" class="px-2.5 py-1.5 rounded-lg bg-blue-600/15 hover:bg-blue-600/25 text-blue-400 border border-blue-500/30 font-medium inline-block">
                                        Detail / Override
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">
                                    Belum ada data pengiriman terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Log (1 Col) -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-fingerprint text-emerald-400"></i>
                        <span>Aktivitas Sistem</span>
                    </h2>
                    <p class="text-xs text-slate-400">Jejak audit keamanan & operasi</p>
                </div>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium">
                    Semua &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentAudits as $log)
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-blue-400 text-[11px]">{{ $log->action }}</span>
                            <span class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-300 text-xs leading-relaxed">{{ $log->description }}</p>
                        <div class="flex items-center gap-2 text-[10px] text-slate-400 pt-1 border-t border-slate-800/50">
                            <span><i class="fa-regular fa-user"></i> {{ $log->user_name }}</span>
                            <span>&bull;</span>
                            <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-xs text-center py-4">Belum ada riwayat audit log.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let map, markersGroup;
    const driversData = @json($activeDrivers);

    function initMap() {
        // Default center: Tasikmalaya (-7.3274, 108.2207)
        map = L.map('dashboard-map').setView([-7.3325, 108.2250], 13);

        // OpenStreetMap Dark tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://carto.com/">CARTO</a>, &copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        markersGroup = L.featureGroup().addTo(map);

        // Hub Pergudangan marker
        const hubIcon = L.divIcon({
            className: 'custom-hub-icon',
            html: `
                <div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid white; box-shadow: 0 4px 12px rgba(59,130,246,0.5);">
                    <i class="fa-solid fa-warehouse text-xs"></i>
                </div>
            `,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        L.marker([-7.3274, 108.2207], { icon: hubIcon })
            .addTo(map)
            .bindPopup("<b>Hub Pergudangan Pusat</b><br>Jl. Siliwangi Tasikmalaya<br>Titik Awal Pengiriman");

        renderDrivers();
    }

    function renderDrivers() {
        markersGroup.clearLayers();

        driversData.forEach(driver => {
            const isDelivering = driver.status === 'delivering';
            const isEmergency = driver.emergency_status;

            let bgColor = isEmergency ? '#e11d48' : (isDelivering ? '#10b981' : '#64748b');
            let iconClass = driver.vehicle_type === 'motorcycle' ? 'fa-motorcycle' : 'fa-truck';

            const driverIcon = L.divIcon({
                className: 'driver-marker',
                html: `
                    <div style="background: ${bgColor}; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid white; box-shadow: 0 4px 14px ${bgColor}80; position: relative;">
                        <i class="fa-solid ${iconClass} text-xs"></i>
                        ${isEmergency ? '<span style="position: absolute; top: -3px; right: -3px; width: 10px; height: 10px; background: red; border-radius: 50%; border: 1px solid white;"></span>' : ''}
                    </div>
                `,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            const marker = L.marker([driver.latitude, driver.longitude], { icon: driverIcon });

            let popupContent = `
                <div style="font-family: inherit; min-width: 200px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 6px; margin-bottom: 6px;">
                        <strong style="color: #60a5fa; font-size: 13px;">${driver.name}</strong>
                        <span style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: ${isDelivering ? '#065f46' : '#334155'}; color: ${isDelivering ? '#6ee7b7' : '#cbd5e1'};">
                            ${isDelivering ? 'Sedang Mengantar' : 'Siaga'}
                        </span>
                    </div>
                    <p style="margin: 0; font-size: 11px; color: #cbd5e1;">Armada: <b>${driver.plate_number}</b> (${driver.vehicle_name})</p>
                    <p style="margin: 3px 0; font-size: 11px; color: #cbd5e1;">Kecepatan: <b>${driver.speed} km/h</b> &bull; Baterai: <b>${driver.battery}%</b></p>
                    ${driver.active_order ? `
                        <div style="margin-top: 6px; padding: 6px; background: #1e293b; border-radius: 6px; font-size: 10px; color: #94a3b8;">
                            <strong>Order:</strong> ${driver.active_order.order_number}<br>
                            ${driver.active_order.title}
                        </div>
                    ` : ''}
                    <div style="margin-top: 8px;">
                        <a href="/admin/drivers/${driver.id}" style="display: block; text-align: center; background: #2563eb; color: white; padding: 4px 8px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: bold;">
                            Lihat Profil & Jejak
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            markersGroup.addLayer(marker);
        });

        if (markersGroup.getLayers().length > 0) {
            map.fitBounds(markersGroup.getBounds().pad(0.2));
        }
    }

    function refreshMap() {
        fetch('{{ route("admin.tracking.locations") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    driversData.length = 0;
                    data.drivers.forEach(d => {
                        driversData.push({
                            id: d.id,
                            name: d.name,
                            phone: d.phone,
                            status: d.status,
                            emergency_status: d.emergency_status,
                            emergency_note: d.emergency_note,
                            latitude: d.lat,
                            longitude: d.lng,
                            battery: d.battery,
                            speed: d.speed,
                            heading: d.heading,
                            plate_number: d.plate_number,
                            vehicle_name: d.vehicle_model,
                            vehicle_type: d.vehicle_type,
                            active_order: d.order
                        });
                    });
                    renderDrivers();
                }
            })
            .catch(err => console.error("Error refreshing tracking data:", err));
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        setInterval(refreshMap, 10000); // Polling every 10 seconds according to system config
    });
</script>
@endpush
