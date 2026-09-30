@extends('layouts.admin')

@section('title', 'Live GPS Tracking')
@section('page_title', 'Live GPS Fleet Tracking')
@section('page_subtitle', 'Pantau pergerakan armada pengiriman secara realtime dengan peta interaktif')

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col space-y-4">

    <!-- Top Control Bar -->
    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl flex flex-wrap items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
            <div>
                <h3 class="font-bold text-white text-sm">Pusat Komando Pelacakan Armada</h3>
                <p class="text-[11px] text-slate-400">Pembaruan otomatis setiap 10 detik dari smartphone driver</p>
            </div>
        </div>

        <div class="flex items-center gap-3 text-xs">
            <div class="flex items-center gap-2 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800">
                <span class="text-slate-400">Total Driver Terlacak:</span>
                <span id="tracked-count" class="font-bold text-blue-400">{{ $drivers->count() }}</span>
            </div>
            <button type="button" onclick="refreshLiveTracking()" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium transition-colors flex items-center gap-2 shadow-lg shadow-blue-500/20">
                <i class="fa-solid fa-rotate"></i>
                <span>Segarkan Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Map & Sidebar Split Layout -->
    <div class="flex-1 flex flex-col lg:flex-row gap-4 min-h-0">
        <!-- Sidebar: Driver List -->
        <div class="w-full lg:w-80 bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 backdrop-blur-xl flex flex-col shrink-0 overflow-hidden">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                <span class="text-xs font-bold text-white uppercase tracking-wider">Daftar Pengemudi</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-medium">Aktif</span>
            </div>

            <div id="driver-list" class="flex-1 overflow-y-auto space-y-2.5 pr-1">
                @forelse($drivers as $d)
                    <div onclick="focusDriver({{ $d->user_id }}, {{ $d->current_latitude }}, {{ $d->current_longitude }})" 
                        class="driver-card p-3 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-blue-500/50 cursor-pointer transition-all space-y-2"
                        id="driver-card-{{ $d->user_id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center font-bold text-xs">
                                    {{ substr($d->user->name, 0, 1) }}
                                </div>
                                <span class="font-semibold text-white text-xs">{{ $d->user->name }}</span>
                            </div>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $d->status === 'delivering' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-700 text-slate-300' }}">
                                {{ $d->status === 'delivering' ? 'Mengantar' : 'Siaga' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400 pt-1 border-t border-slate-900">
                            <div><i class="fa-solid fa-truck text-[10px]"></i> {{ $d->vehicle->plate_number ?? '-' }}</div>
                            <div><i class="fa-solid fa-gauge text-[10px]"></i> {{ $d->speed ?? 0 }} km/h</div>
                            <div><i class="fa-solid fa-battery-half text-[10px]"></i> {{ $d->battery_level ?? '-' }}%</div>
                            <div><i class="fa-regular fa-clock text-[10px]"></i> {{ $d->last_location_time?->format('H:i') ?? '-' }}</div>
                        </div>

                        @if($d->emergency_status)
                            <div class="p-1.5 rounded bg-rose-500/20 border border-rose-500/40 text-rose-300 text-[10px] flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Darurat: {{ $d->emergency_note ?? 'SOS' }}</span>
                            </div>
                        @endif

                        <div class="pt-1 flex justify-between items-center text-[10px]">
                            <button type="button" onclick="event.stopPropagation(); loadDriverTrail({{ $d->user_id }})" class="text-blue-400 hover:text-blue-300 underline">
                                Tampilkan Jejak Rute
                            </button>
                            <a href="{{ route('admin.drivers.show', $d->id) }}" class="text-slate-400 hover:text-white">
                                Profil &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-slate-500">
                        Tidak ada driver dengan koordinat GPS aktif.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Live Map -->
        <div class="flex-1 bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden relative">
            <div id="live-fleet-map" class="w-full h-full"></div>

            <!-- Legend Overlay -->
            <div class="absolute bottom-4 left-4 z-[400] p-3 rounded-xl bg-slate-950/90 border border-slate-800 backdrop-blur-md text-[11px] space-y-1.5 shadow-xl hidden sm:block">
                <span class="font-bold text-white block mb-1">Legenda Peta</span>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> <span>Driver Sedang Mengantar</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> <span>Hub Gudang Logistik</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-slate-500"></span> <span>Driver Siaga (Idle)</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500"></span> <span>Sinyal Darurat (SOS)</span></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let liveMap;
    let markersLayer = L.featureGroup();
    let trailLayer = L.featureGroup();
    let activeTrailUserId = null;

    function initLiveTrackingMap() {
        liveMap = L.map('live-fleet-map').setView([-7.3325, 108.2250], 13);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO &copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(liveMap);

        markersLayer.addTo(liveMap);
        trailLayer.addTo(liveMap);

        // Hub Pergudangan Pusat Marker
        const hubIcon = L.divIcon({
            className: 'custom-hub',
            html: `
                <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid white; box-shadow: 0 4px 12px rgba(37,99,235,0.6);">
                    <i class="fa-solid fa-warehouse text-xs"></i>
                </div>
            `,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        L.marker([-7.3274, 108.2207], { icon: hubIcon })
            .addTo(liveMap)
            .bindPopup("<b>Hub Pergudangan Pusat</b><br>Jl. Siliwangi Tasikmalaya");

        refreshLiveTracking();
    }

    function refreshLiveTracking() {
        fetch('{{ route("admin.tracking.locations") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    markersLayer.clearLayers();
                    const countEl = document.getElementById('tracked-count');
                    if (countEl) countEl.innerText = data.drivers.length;

                    data.drivers.forEach(d => {
                        const isDelivering = d.status === 'delivering';
                        const isEmergency = d.emergency_status;

                        let color = isEmergency ? '#f43f5e' : (isDelivering ? '#10b981' : '#64748b');
                        let iconClass = d.vehicle_type === 'motorcycle' ? 'fa-motorcycle' : 'fa-truck';

                        const icon = L.divIcon({
                            className: 'driver-live-marker',
                            html: `
                                <div style="background: ${color}; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid white; box-shadow: 0 4px 12px ${color}80; position: relative;">
                                    <i class="fa-solid ${iconClass} text-xs"></i>
                                    ${isEmergency ? '<span style="position: absolute; top: -3px; right: -3px; width: 10px; height: 10px; background: red; border-radius: 50%; border: 1px solid white;"></span>' : ''}
                                </div>
                            `,
                            iconSize: [38, 38],
                            iconAnchor: [19, 19]
                        });

                        const marker = L.marker([d.lat, d.lng], { icon: icon });

                        let popupHtml = `
                            <div style="font-family: inherit; min-width: 220px;">
                                <div style="border-bottom: 1px solid #334155; padding-bottom: 6px; margin-bottom: 6px; display: flex; justify-content: space-between;">
                                    <strong style="color: #60a5fa;">${d.name}</strong>
                                    <span style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: #1e293b; color: #38bdf8;">${d.status}</span>
                                </div>
                                <p style="margin: 0; font-size: 11px;">Kendaraan: <b>${d.plate_number}</b> (${d.vehicle_model})</p>
                                <p style="margin: 2px 0; font-size: 11px;">Kecepatan: <b>${d.speed} km/h</b> &bull; Baterai: <b>${d.battery}%</b></p>
                                <p style="margin: 2px 0; font-size: 10px; color: #94a3b8;">Akurasi GPS: &plusmn;${d.accuracy}m &bull; Update: ${d.last_updated}</p>
                                ${d.order ? `
                                    <div style="margin-top: 6px; padding: 6px; background: #0f172a; border-radius: 6px; font-size: 10px;">
                                        <b>Tugas:</b> ${d.order.order_number}<br>
                                        <span>${d.order.title}</span>
                                    </div>
                                ` : ''}
                                <div style="margin-top: 8px; display: flex; gap: 4px;">
                                    <button onclick="loadDriverTrail(${d.user_id})" style="flex: 1; background: #2563eb; color: white; border: none; padding: 4px; border-radius: 4px; cursor: pointer; font-size: 10px; font-weight: bold;">
                                        Jejak Rute
                                    </button>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupHtml);
                        markersLayer.addLayer(marker);
                    });

                    // If user was following a trail, reload trail
                    if (activeTrailUserId) {
                        loadDriverTrail(activeTrailUserId, false);
                    }
                }
            })
            .catch(err => console.error("Error updating live tracking:", err));
    }

    function focusDriver(userId, lat, lng) {
        liveMap.setView([lat, lng], 15, { animate: true });
    }

    function loadDriverTrail(userId, panTo = true) {
        activeTrailUserId = userId;
        fetch(`/admin/tracking/api/trail/${userId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.trail.length > 0) {
                    trailLayer.clearLayers();

                    const latlngs = data.trail.map(pt => [pt.lat, pt.lng]);
                    const polyline = L.polyline(latlngs, {
                        color: '#38bdf8',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '6, 8'
                    }).addTo(trailLayer);

                    // Add point markers
                    data.trail.forEach((pt, idx) => {
                        const circle = L.circleMarker([pt.lat, pt.lng], {
                            radius: idx === data.trail.length - 1 ? 6 : 4,
                            fillColor: '#38bdf8',
                            color: '#ffffff',
                            weight: 1.5,
                            fillOpacity: 0.9
                        }).addTo(trailLayer);

                        circle.bindPopup(`Pukul: <b>${pt.time}</b><br>Kecepatan: <b>${pt.speed} km/h</b><br>Baterai: <b>${pt.battery}%</b>`);
                    });

                    if (panTo) {
                        liveMap.fitBounds(polyline.getBounds().pad(0.3));
                    }
                }
            })
            .catch(err => console.error("Error loading trail:", err));
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLiveTrackingMap();
        setInterval(refreshLiveTracking, 10000);
    });
</script>
@endpush
