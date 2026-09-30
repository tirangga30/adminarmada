@extends('layouts.admin')

@section('title', 'Detail Pengiriman ' . $order->order_number)
@section('page_title', 'Detail & Intervensi Pengiriman')
@section('page_subtitle', 'Monitoring perjalanan order, bukti pengiriman (POD), dan kontrol override admin')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.deliveries.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Daftar Pengiriman</span>
        </a>

        <!-- Admin Override Button Trigger -->
        <button type="button" onclick="document.getElementById('override-modal').classList.remove('hidden')" 
            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/20 flex items-center gap-2 transition-all self-start sm:self-auto">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Admin Support / Override Status</span>
        </button>
    </div>

    <!-- Status Lifecycle Stepper (Section 3.11) -->
    @php
        $steps = ['ASSIGNED', 'ACCEPTED', 'EN_ROUTE', 'ARRIVED', 'UNLOADING', 'DELIVERED'];
        $currentIdx = array_search($order->status, $steps);
        $isTerminal = in_array($order->status, ['FAILED', 'CANCELLED']);
    @endphp

    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
            <div>
                <span class="text-xs text-slate-400 font-mono">ORDER: {{ $order->order_number }}</span>
                <h2 class="text-lg font-black text-white">{{ $order->title }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $order->status_badge }}">
                    {{ $order->status }}
                </span>
                <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $order->priority_badge }}">
                    PRIORITAS {{ strtoupper($order->priority) }}
                </span>
            </div>
        </div>

        @if($isTerminal)
            <div class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-ban text-lg text-rose-400"></i>
                <div>
                    <strong class="block text-sm">Status Khusus: {{ $order->status }}</strong>
                    <span>Alasan: {{ $order->failure_reason ?? ($order->cancellation_reason ?? 'Tidak ada catatan alasan.') }}</span>
                </div>
            </div>
        @else
            <!-- Progress Stepper Pipeline -->
            <div class="relative py-3">
                <div class="hidden md:flex items-center justify-between relative">
                    <!-- Background line -->
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-800 z-0"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-500 transition-all duration-500 z-0" 
                        style="width: {{ $currentIdx !== false ? ($currentIdx / (count($steps) - 1)) * 100 : 0 }}%;"></div>

                    @foreach($steps as $idx => $step)
                        @php
                            $isPassed = $currentIdx !== false && $idx <= $currentIdx;
                            $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                        @endphp
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs border-2 transition-all
                                {{ $isCurrent ? 'bg-blue-600 border-white text-white ring-4 ring-blue-500/30 scale-110' : ($isPassed ? 'bg-blue-500 border-blue-400 text-white' : 'bg-slate-900 border-slate-700 text-slate-500') }}">
                                @if($isPassed && !$isCurrent)
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </div>
                            <span class="text-[11px] font-semibold mt-2 {{ $isCurrent ? 'text-blue-400' : ($isPassed ? 'text-slate-200' : 'text-slate-500') }}">
                                {{ $step }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($order->admin_override_notes)
            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/25 text-amber-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                <span><strong>Catatan Intervensi Admin:</strong> {{ $order->admin_override_notes }}</span>
            </div>
        @endif
    </div>

    <!-- 2 Column Layout: Route Map + Stops Timeline & Driver Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Interactive Map & Waypoints -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Route Visualization Map -->
            <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <i class="fa-solid fa-route text-blue-400"></i>
                        <span>Visualisasi Rute Pengiriman (Peta Interaktif)</span>
                    </h3>
                    <span class="text-xs text-slate-400">Mode: <strong class="text-blue-400 uppercase">{{ $order->route_mode }}</strong></span>
                </div>

                <div id="route-map" class="w-full h-80 rounded-xl border border-slate-800 overflow-hidden relative z-10"></div>
            </div>

            <!-- Destination Stops List -->
            <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <i class="fa-solid fa-location-crosshairs text-emerald-400"></i>
                        <span>Daftar Titik Pemberhentian (Waypoints)</span>
                    </h3>
                    <span class="text-xs text-slate-400">{{ $order->stops->count() }} Destinasi</span>
                </div>

                <div class="space-y-3">
                    <!-- Origin -->
                    <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            <i class="fa-solid fa-warehouse"></i>
                        </div>
                        <div class="flex-1 min-w-0 text-xs">
                            <span class="text-[10px] text-blue-400 font-bold uppercase tracking-wider block">Titik Awal (Origin)</span>
                            <p class="font-bold text-white">{{ $order->origin_address }}</p>
                            <p class="text-[11px] text-slate-400">Koordinat: {{ $order->origin_latitude }}, {{ $order->origin_longitude }}</p>
                        </div>
                    </div>

                    <!-- Stops -->
                    @foreach($order->stops as $stop)
                        <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 flex items-start gap-3 hover:border-slate-700 transition-colors">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                {{ $stop->stop_sequence }}
                            </div>
                            <div class="flex-1 min-w-0 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-white">{{ $stop->customer_name }}</p>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium 
                                        {{ $stop->status === 'completed' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($stop->status === 'arrived' ? 'bg-cyan-500/15 text-cyan-400' : 'bg-slate-800 text-slate-400') }}">
                                        {{ strtoupper($stop->status) }}
                                    </span>
                                </div>
                                <p class="text-slate-300 text-[11px]">{{ $stop->address }}</p>
                                <div class="flex items-center gap-3 text-[10px] text-slate-400 pt-1">
                                    <span><i class="fa-solid fa-phone"></i> {{ $stop->customer_phone ?? 'Tidak ada telp' }}</span>
                                    <span>&bull;</span>
                                    <span>Koordinat: {{ $stop->latitude }}, {{ $stop->longitude }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Manifest of Items -->
            <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-amber-400"></i>
                        <span>Manifest Barang Bawaan</span>
                    </h3>
                    <span class="text-xs text-slate-400">Total: {{ $order->items->count() }} Item</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                <th class="py-2.5 px-3">Nama Barang</th>
                                <th class="py-2.5 px-3">Jumlah</th>
                                <th class="py-2.5 px-3">Satuan</th>
                                <th class="py-2.5 px-3">Berat (Kg)</th>
                                <th class="py-2.5 px-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($order->items as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-medium text-white">{{ $item->item_name }}</td>
                                    <td class="py-2.5 px-3 text-slate-300 font-bold">{{ $item->quantity }}</td>
                                    <td class="py-2.5 px-3 text-slate-400">{{ $item->unit }}</td>
                                    <td class="py-2.5 px-3 text-slate-300">{{ $item->weight_kg ?? '-' }} kg</td>
                                    <td class="py-2.5 px-3 text-slate-400 text-[11px]">{{ $item->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-500">Tidak ada item tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Driver Card, Vehicle Card, & POD Proof of Delivery -->
        <div class="space-y-6">

            <!-- Driver Card -->
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pengemudi Ditugaskan</span>
                @if($order->driver)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-sm">
                            {{ substr($order->driver->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-white text-sm truncate">{{ $order->driver->name }}</h4>
                            <p class="text-xs text-slate-400">{{ $order->driver->phone ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-800 grid grid-cols-2 gap-2 text-xs text-slate-300">
                        <div>
                            <span class="text-[10px] text-slate-400 block">SIM</span>
                            <span class="font-medium">{{ $order->driver->driverProfile->license_type ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Status Driver</span>
                            <span class="font-medium text-emerald-400">{{ $order->driver->driverProfile->status ?? 'available' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-amber-400">Belum ada pengemudi dialokasikan untuk order ini.</p>
                @endif
            </div>

            <!-- Vehicle Card -->
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kendaraan Armada</span>
                @if($order->vehicle)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-base">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-white text-sm truncate">{{ $order->vehicle->plate_number }}</h4>
                            <p class="text-xs text-slate-400">{{ $order->vehicle->brand_model }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-800 grid grid-cols-2 gap-2 text-xs text-slate-300">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Tipe</span>
                            <span class="font-medium">{{ $order->vehicle->type_label }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Kapasitas Muat</span>
                            <span class="font-medium">{{ $order->vehicle->capacity_weight_kg }} Kg</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-500">Kendaraan belum dipilih.</p>
                @endif
            </div>

            <!-- Proof of Delivery (POD) Section (Section 3.10 & 3.14 & F-DRV-04) -->
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h3 class="font-bold text-white text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-signature text-emerald-400"></i>
                        <span>Proof of Delivery (POD)</span>
                    </h3>
                    @if($order->proofOfDelivery)
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">Terverifikasi</span>
                    @else
                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px]">Belum Ada</span>
                    @endif
                </div>

                @if($order->proofOfDelivery)
                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Nama Penerima</span>
                                <strong class="text-white">{{ $order->proofOfDelivery->receiver_name }}</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Hubungan</span>
                                <span class="text-slate-300">{{ $order->proofOfDelivery->receiver_relation }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] text-slate-400 block">Waktu Penerimaan</span>
                            <span class="text-slate-200">{{ $order->proofOfDelivery->delivered_at->format('d M Y, H:i') }} WIB</span>
                        </div>

                        @if($order->proofOfDelivery->notes)
                            <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 text-[11px]">
                                <strong>Catatan:</strong> {{ $order->proofOfDelivery->notes }}
                            </div>
                        @endif

                        @if($order->proofOfDelivery->signature_data)
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-1">Tanda Tangan Digital Penerima</span>
                                <div class="p-2 rounded-xl bg-white border border-slate-300 flex items-center justify-center">
                                    <img src="{{ $order->proofOfDelivery->signature_data }}" alt="Tanda Tangan Penerima" class="max-h-20 object-contain">
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6 text-xs text-slate-500 space-y-1">
                        <i class="fa-solid fa-file-circle-question text-2xl text-slate-600 block"></i>
                        <span>Bukti pengiriman (POD) akan diunggah oleh driver saat sampai di tujuan.</span>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>

<!-- Admin Override / Support Modal -->
<div id="override-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-5 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-extrabold text-white text-base flex items-center gap-2 text-amber-400">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Admin Override & Intervensi Sistem</span>
            </h3>
            <button onclick="document.getElementById('override-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.deliveries.override', $order->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Ubah Status Pengiriman (Override)</label>
                <select name="new_status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                    <option value="ASSIGNED" {{ $order->status === 'ASSIGNED' ? 'selected' : '' }}>ASSIGNED (Ditugaskan)</option>
                    <option value="ACCEPTED" {{ $order->status === 'ACCEPTED' ? 'selected' : '' }}>ACCEPTED (Diterima Driver)</option>
                    <option value="EN_ROUTE" {{ $order->status === 'EN_ROUTE' ? 'selected' : '' }}>EN_ROUTE (Dalam Perjalanan)</option>
                    <option value="ARRIVED" {{ $order->status === 'ARRIVED' ? 'selected' : '' }}>ARRIVED (Tiba di Lokasi)</option>
                    <option value="UNLOADING" {{ $order->status === 'UNLOADING' ? 'selected' : '' }}>UNLOADING (Bongkar Muatan)</option>
                    <option value="DELIVERED" {{ $order->status === 'DELIVERED' ? 'selected' : '' }}>DELIVERED (Selesai Terkirim)</option>
                    <option value="FAILED" {{ $order->status === 'FAILED' ? 'selected' : '' }}>FAILED (Gagal / Kendala Teknis)</option>
                    <option value="CANCELLED" {{ $order->status === 'CANCELLED' ? 'selected' : '' }}>CANCELLED (Dibatalkan)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Tugaskan Ulang Driver</label>
                    <select name="driver_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        <option value="">-- Pertahankan Driver Saat Ini --</option>
                        @foreach($availableDrivers as $ad)
                            <option value="{{ $ad->id }}" {{ $order->driver_id === $ad->id ? 'selected' : '' }}>{{ $ad->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Ganti Kendaraan</label>
                    <select name="vehicle_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        <option value="">-- Pertahankan Kendaraan --</option>
                        @foreach($availableVehicles as $av)
                            <option value="{{ $av->id }}" {{ $order->vehicle_id === $av->id ? 'selected' : '' }}>{{ $av->plate_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Catatan Override Admin * (Audit Log)</label>
                <textarea name="admin_notes" rows="3" required placeholder="Jelaskan alasan intervensi, kendala driver, atau perubahan rute..." 
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('override-modal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-medium">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/30">
                    Terapkan & Simpan ke Audit Log
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let routeMap;

    function initRouteMap() {
        const originLat = {{ $order->origin_latitude }};
        const originLng = {{ $order->origin_longitude }};

        routeMap = L.map('route-map').setView([originLat, originLng], 13);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO &copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(routeMap);

        const bounds = [];

        // Origin Marker
        const originIcon = L.divIcon({
            className: 'route-hub',
            html: `
                <div style="background: #2563eb; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                    <i class="fa-solid fa-warehouse text-xs"></i>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        L.marker([originLat, originLng], { icon: originIcon })
            .addTo(routeMap)
            .bindPopup("<b>Titik Asal</b><br>{{ $order->origin_address }}");

        bounds.push([originLat, originLng]);

        const routePoints = [[originLat, originLng]];

        // Stops Markers
        @foreach($order->stops as $stop)
            const stopLat{{ $stop->id }} = {{ $stop->latitude }};
            const stopLng{{ $stop->id }} = {{ $stop->longitude }};
            routePoints.push([stopLat{{ $stop->id }}, stopLng{{ $stop->id }}]);
            bounds.push([stopLat{{ $stop->id }}, stopLng{{ $stop->id }}]);

            const stopIcon{{ $stop->id }} = L.divIcon({
                className: 'route-stop',
                html: `
                    <div style="background: #10b981; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 11px;">
                        {{ $stop->stop_sequence }}
                    </div>
                `,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            L.marker([stopLat{{ $stop->id }}, stopLng{{ $stop->id }}], { icon: stopIcon{{ $stop->id }} })
                .addTo(routeMap)
                .bindPopup("<b>Stop #{{ $stop->stop_sequence }}: {{ $stop->customer_name }}</b><br>{{ $stop->address }}");
        @endforeach

        // Draw Polyline connecting Origin and Stops
        if (routePoints.length > 1) {
            L.polyline(routePoints, {
                color: '#3b82f6',
                weight: 4,
                opacity: 0.85,
                dashArray: '8, 8'
            }).addTo(routeMap);
        }

        if (bounds.length > 0) {
            routeMap.fitBounds(bounds, { padding: [30, 30] });
        }
    }

    document.addEventListener('DOMContentLoaded', initRouteMap);
</script>
@endpush
