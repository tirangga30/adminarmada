@extends('layouts.admin')

@section('title', 'Buat Pengiriman Baru')
@section('page_title', 'Form Pengiriman Baru')
@section('page_subtitle', 'Alokasikan penugasan driver, rute singgah, dan manifest barang')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.deliveries.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Daftar Pengiriman</span>
        </a>
    </div>

    <form action="{{ route('admin.deliveries.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- General Delivery Information Card -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-5">
            <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-blue-400"></i>
                        <span>Informasi Pokok Pengiriman</span>
                    </h3>
                    <p class="text-xs text-slate-400">Nomor order, prioritas, dan pemilihan rute</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/30 text-xs font-mono font-bold">
                    {{ $orderNumber }}
                </span>
                <input type="hidden" name="order_number" value="{{ $orderNumber }}">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Judul / Deskripsi Pengiriman *</label>
                    <input type="text" name="title" value="{{ old('title', 'Distribusi Sembako Wilayah Tasikmalaya') }}" required
                        class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Prioritas Pengiriman</label>
                    <select name="priority" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="medium">Medium (Standar)</option>
                        <option value="low">Rendah</option>
                        <option value="high">Tinggi</option>
                        <option value="urgent">Mendesak (Urgent)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Mode Optimasi Rute (Section 3.13)</label>
                    <select name="route_mode" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="recommended">Recommended (Jarak & Waktu Efisien)</option>
                        <option value="automatic">Automatic TSP (Sistem Algoritma)</option>
                        <option value="manual">Manual (Urutan Ditentukan Owner/Driver)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Pilih Pengemudi (Driver)</label>
                    <select name="driver_id" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Driver (Opsional, dapat ditentukan nanti) --</option>
                        @foreach($drivers as $drv)
                            <option value="{{ $drv->id }}">
                                {{ $drv->name }} (SIM: {{ $drv->driverProfile->license_type ?? '-' }}, Status: {{ $drv->driverProfile->status ?? 'available' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Pilih Armada Kendaraan</label>
                    <select name="vehicle_id" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Kendaraan (Opsional) --</option>
                        @foreach($vehicles as $veh)
                            <option value="{{ $veh->id }}">
                                {{ $veh->plate_number }} - {{ $veh->brand_model }} (Kapasitas: {{ $veh->capacity_weight_kg }} kg)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Origin Warehouse Location -->
            <div class="pt-3 border-t border-slate-800 space-y-3">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Titik Asal / Gudang Pusat</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] text-slate-400 mb-1">Nama / Alamat Gudang Asal</label>
                        <input type="text" name="origin_address" value="Hub Gudang Pusat Siliwangi, Tasikmalaya" required
                            class="w-full px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Latitude</label>
                            <input type="text" name="origin_latitude" value="-7.3274000" required
                                class="w-full px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Longitude</label>
                            <input type="text" name="origin_longitude" value="108.2207000" required
                                class="w-full px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Destination Stops / Waypoints -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-emerald-400"></i>
                        <span>Titik Tujuan / Pemberhentian (Stops)</span>
                    </h3>
                    <p class="text-xs text-slate-400">Daftar pelanggan atau toko penerima barang</p>
                </div>
                <button type="button" onclick="addStopRow()" class="px-3 py-1.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 text-xs font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Titik Stop</span>
                </button>
            </div>

            <div id="stops-container" class="space-y-4">
                <!-- Stop Row 1 -->
                <div class="stop-item p-4 rounded-xl bg-slate-950/60 border border-slate-800 relative space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Pemberhentian #1</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Nama Toko / Penerima *</label>
                            <input type="text" name="stops[0][customer_name]" value="Toko Sumber Rejeki" required
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Nomor Telepon / WA</label>
                            <input type="text" name="stops[0][customer_phone]" value="081234567891"
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] text-slate-400 mb-1">Alamat Tujuan Lengkap *</label>
                            <input type="text" name="stops[0][address]" value="Jl. Cihideung Balong No. 12, Tasikmalaya" required
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Latitude *</label>
                            <input type="text" name="stops[0][latitude]" value="-7.3325000" required
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Longitude *</label>
                            <input type="text" name="stops[0][longitude]" value="108.2250000" required
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Items Manifest -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <i class="fa-solid fa-dolly text-amber-400"></i>
                        <span>Manifest Muatan Barang</span>
                    </h3>
                    <p class="text-xs text-slate-400">Daftar item produk yang diangkut oleh armada</p>
                </div>
                <button type="button" onclick="addItemRow()" class="px-3 py-1.5 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 text-amber-400 border border-amber-500/30 text-xs font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Item Barang</span>
                </button>
            </div>

            <div id="items-container" class="space-y-3">
                <!-- Item Row 1 -->
                <div class="item-row p-3 rounded-xl bg-slate-950/60 border border-slate-800 grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] text-slate-400 mb-1">Nama Barang *</label>
                        <input type="text" name="items[0][item_name]" value="Beras Premium Raja Lele 25kg" required
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Jumlah *</label>
                            <input type="number" name="items[0][quantity]" value="10" min="1" required
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Satuan *</label>
                            <input type="text" name="items[0][unit]" value="Sak" required
                                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Total Berat (Kg)</label>
                        <input type="number" step="0.01" name="items[0][weight_kg]" value="250.00"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.deliveries.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/25 transition-all flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Simpan & Terbitkan Pengiriman</span>
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    let stopCount = 1;
    let itemCount = 1;

    function addStopRow() {
        const container = document.getElementById('stops-container');
        const index = stopCount++;

        const div = document.createElement('div');
        div.className = 'stop-item p-4 rounded-xl bg-slate-950/60 border border-slate-800 relative space-y-3';
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Pemberhentian #${index + 1}</span>
                <button type="button" onclick="this.closest('.stop-item').remove()" class="text-rose-400 hover:text-rose-300 text-xs">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Nama Toko / Penerima *</label>
                    <input type="text" name="stops[${index}][customer_name]" required
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="stops[${index}][customer_phone]"
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[11px] text-slate-400 mb-1">Alamat Tujuan Lengkap *</label>
                    <input type="text" name="stops[${index}][address]" required
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Latitude *</label>
                    <input type="text" name="stops[${index}][latitude]" value="-7.3360000" required
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Longitude *</label>
                    <input type="text" name="stops[${index}][longitude]" value="108.2280000" required
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function addItemRow() {
        const container = document.getElementById('items-container');
        const index = itemCount++;

        const div = document.createElement('div');
        div.className = 'item-row p-3 rounded-xl bg-slate-950/60 border border-slate-800 grid grid-cols-1 sm:grid-cols-4 gap-3 relative';
        div.innerHTML = `
            <div class="sm:col-span-2">
                <label class="block text-[11px] text-slate-400 mb-1">Nama Barang *</label>
                <input type="text" name="items[${index}][item_name]" required
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Jumlah *</label>
                    <input type="number" name="items[${index}][quantity]" value="1" min="1" required
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Satuan *</label>
                    <input type="text" name="items[${index}][unit]" value="Dus" required
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] text-slate-400 mb-1">Total Berat (Kg)</label>
                    <input type="number" step="0.01" name="items[${index}][weight_kg]"
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <button type="button" onclick="this.closest('.item-row').remove()" class="p-2 mt-4 text-rose-400 hover:text-rose-300">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    }
</script>
@endpush
