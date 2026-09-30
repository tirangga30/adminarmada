@extends('layouts.admin')

@section('title', 'Profil Driver: ' . $driver->user->name)
@section('page_title', 'Detail & Pelacakan Driver')
@section('page_subtitle', 'Informasi lisensi pengemudi, riwayat koordinat GPS, dan status operasional')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.drivers.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Daftar Driver</span>
        </a>

        <!-- Emergency Trigger / Clear Button -->
        <form action="{{ route('admin.drivers.emergency', $driver->id) }}" method="POST" class="inline">
            @csrf
            @if($driver->emergency_status)
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 flex items-center gap-2">
                    <i class="fa-solid fa-bell-slash"></i>
                    <span>Reset / Matikan Status Darurat</span>
                </button>
            @else
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/30 flex items-center gap-2" onclick="return confirm('Apakah Anda ingin memicu sinyal darurat (SOS) untuk driver ini?')">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Tandai Darurat (SOS)</span>
                </button>
            @endif
        </form>
    </div>

    <!-- Top Card: Profile & Operational Status -->
    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-400 text-white flex items-center justify-center font-extrabold text-xl shadow-lg shadow-blue-500/25">
                    {{ substr($driver->user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $driver->user->name }}</h2>
                    <p class="text-xs text-slate-400">{{ $driver->user->email }} &bull; {{ $driver->user->phone ?? 'Tidak ada nomor telepon' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold 
                    {{ $driver->status === 'delivering' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($driver->status === 'available' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'bg-slate-800 text-slate-400') }}">
                    STATUS: {{ strtoupper($driver->status) }}
                </span>
                @if($driver->emergency_status)
                    <span class="px-3 py-1 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-bold animate-pulse">
                        SOS AKTIF
                    </span>
                @endif
            </div>
        </div>

        <!-- Update Driver Info Form -->
        <form action="{{ route('admin.drivers.update', $driver->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nomor Lisensi (SIM)</label>
                <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tipe Golongan SIM</label>
                <select name="license_type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                    <option value="SIM A" {{ $driver->license_type === 'SIM A' ? 'selected' : '' }}>SIM A (Mobil Penumpang/Barang Ringan)</option>
                    <option value="SIM B1" {{ $driver->license_type === 'SIM B1' ? 'selected' : '' }}>SIM B1 (Mobil Bus/Barang > 3.500kg)</option>
                    <option value="SIM B1 Umum" {{ $driver->license_type === 'SIM B1 Umum' ? 'selected' : '' }}>SIM B1 Umum</option>
                    <option value="SIM B2" {{ $driver->license_type === 'SIM B2' ? 'selected' : '' }}>SIM B2 (Kendaraan Alat Berat/Penarik)</option>
                    <option value="SIM C" {{ $driver->license_type === 'SIM C' ? 'selected' : '' }}>SIM C (Sepeda Motor)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Alokasi Armada Kendaraan</label>
                <select name="vehicle_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                    <option value="">-- Tanpa Kendaraan --</option>
                    @foreach($availableVehicles as $v)
                        <option value="{{ $v->id }}" {{ $driver->vehicle_id === $v->id ? 'selected' : '' }}>
                            {{ $v->plate_number }} ({{ $v->brand_model }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Status Tugas</label>
                <div class="flex items-center gap-2">
                    <select name="status" class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        <option value="available" {{ $driver->status === 'available' ? 'selected' : '' }}>Tersedia (Siaga)</option>
                        <option value="delivering" {{ $driver->status === 'delivering' ? 'selected' : '' }}>Sedang Mengantar</option>
                        <option value="off_duty" {{ $driver->status === 'off_duty' ? 'selected' : '' }}>Off-Duty</option>
                        <option value="suspended" {{ $driver->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                    <button type="submit" class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs shadow-md">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 2 Column Layout: Location Map + Breadcrumbs History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left: Map of Current Driver Location -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-blue-400"></i>
                    <span>Lokasi GPS Terakhir Pengemudi</span>
                </h3>
                <span class="text-xs text-slate-400">
                    Baterai: <strong class="text-white">{{ $driver->battery_level ?? '-' }}%</strong> &bull; 
                    Kecepatan: <strong class="text-white">{{ $driver->speed ?? 0 }} km/h</strong>
                </span>
            </div>

            <div id="driver-map" class="w-full h-80 rounded-xl border border-slate-800 overflow-hidden relative z-10"></div>

            <div class="grid grid-cols-3 gap-2 text-xs text-slate-400 pt-2 border-t border-slate-800">
                <div>
                    <span class="text-[10px] block">Akurasi GPS</span>
                    <strong class="text-slate-200">&plusmn;{{ $driver->accuracy ?? 0 }} meter</strong>
                </div>
                <div>
                    <span class="text-[10px] block">Arah (Heading)</span>
                    <strong class="text-slate-200">{{ $driver->heading ?? 0 }}&deg;</strong>
                </div>
                <div>
                    <span class="text-[10px] block">Pembaruan Terakhir</span>
                    <strong class="text-slate-200">{{ $driver->last_location_time ? $driver->last_location_time->format('H:i:s d/m/Y') : '-' }}</strong>
                </div>
            </div>
        </div>

        <!-- Right: Recent GPS Location Events Log -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-indigo-400"></i>
                    <span>Histori Rekaman Lokasi GPS (Event Log)</span>
                </h3>
                <span class="text-xs text-slate-400">{{ $recentLocations->count() }} Poin Terakhir</span>
            </div>

            <div class="overflow-y-auto max-h-80">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                            <th class="py-2 px-3">Waktu</th>
                            <th class="py-2 px-3">Koordinat</th>
                            <th class="py-2 px-3">Speed</th>
                            <th class="py-2 px-3">Baterai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentLocations as $loc)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 text-slate-300 font-mono">{{ $loc->recorded_at->format('H:i:s') }}</td>
                                <td class="py-2 px-3 text-slate-400 text-[11px] font-mono">{{ $loc->latitude }}, {{ $loc->longitude }}</td>
                                <td class="py-2 px-3 text-slate-300 font-semibold">{{ $loc->speed }} km/h</td>
                                <td class="py-2 px-3 text-slate-400">{{ $loc->battery_level }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">Belum ada riwayat lokasi tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = {{ $driver->current_latitude ?? -7.3325 }};
        const lng = {{ $driver->current_longitude ?? 108.2250 }};

        const map = L.map('driver-map').setView([lat, lng], 14);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO &copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        const driverIcon = L.divIcon({
            className: 'driver-detail-marker',
            html: `
                <div style="background: #2563eb; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid white; box-shadow: 0 4px 12px rgba(37,99,235,0.7);">
                    <i class="fa-solid fa-truck text-xs"></i>
                </div>
            `,
            iconSize: [38, 38],
            iconAnchor: [19, 19]
        });

        L.marker([lat, lng], { icon: driverIcon })
            .addTo(map)
            .bindPopup("<b>{{ $driver->user->name }}</b><br>Armada: {{ $driver->vehicle->plate_number ?? 'Tanpa Kendaraan' }}<br>Speed: {{ $driver->speed ?? 0 }} km/h")
            .openPopup();
    });
</script>
@endpush
