@extends('layouts.admin')

@section('title', 'Armada Kendaraan')
@section('page_title', 'Manajemen Armada Kendaraan')
@section('page_subtitle', 'Data kendaraan distribusi, status servis, kapasitas muatan, dan alokasi driver')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Header -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-white text-base">Daftar Armada Kendaraan</h3>
            <p class="text-xs text-slate-400">Total terdata: <strong class="text-blue-400">{{ $vehicles->total() }} Unit Kendaraan</strong></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.vehicles.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari plat nomor atau merk..." 
                    class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                <select name="type" class="px-3 py-2 bg-slate-950/70 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="blind_van" {{ request('type') === 'blind_van' ? 'selected' : '' }}>Blind Van</option>
                    <option value="pickup" {{ request('type') === 'pickup' ? 'selected' : '' }}>Pickup Box</option>
                    <option value="truck_small" {{ request('type') === 'truck_small' ? 'selected' : '' }}>Truk Engkel</option>
                    <option value="truck_heavy" {{ request('type') === 'truck_heavy' ? 'selected' : '' }}>Truk Double</option>
                    <option value="motorcycle" {{ request('type') === 'motorcycle' ? 'selected' : '' }}>Sepeda Motor</option>
                </select>
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs">Filter</button>
            </form>

            <button type="button" onclick="document.getElementById('add-vehicle-modal').classList.remove('hidden')" 
                class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs shadow-lg shadow-blue-500/20 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Kendaraan</span>
            </button>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Plat Nomor & Merk</th>
                        <th class="py-3 px-4">Tipe Armada</th>
                        <th class="py-3 px-4">Kapasitas Muat</th>
                        <th class="py-3 px-4">Bahan Bakar & Tahun</th>
                        <th class="py-3 px-4">Driver Terpasang</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($vehicles as $veh)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-white text-sm block font-mono">{{ $veh->plate_number }}</span>
                                <span class="text-slate-400 text-[11px] block">{{ $veh->brand_model }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[11px] font-semibold">
                                    {{ $veh->type_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-200 font-bold block">{{ number_format($veh->capacity_weight_kg) }} kg</span>
                                <span class="text-[10px] text-slate-400">{{ $veh->capacity_volume_m3 ? $veh->capacity_volume_m3 . ' m³' : '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300 block">{{ $veh->fuel_type }}</span>
                                <span class="text-[10px] text-slate-400">Tahun {{ $veh->year }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($veh->assignedDriver && $veh->assignedDriver->user)
                                    <span class="font-semibold text-slate-200 block">{{ $veh->assignedDriver->user->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $veh->assignedDriver->user->phone ?? '-' }}</span>
                                @else
                                    <span class="text-slate-500 italic">Belum dialokasikan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $veh->status_badge }}">
                                    {{ match($veh->status) {
                                        'available' => 'Tersedia',
                                        'in_use' => 'Dalam Tugas',
                                        'maintenance' => 'Servis Bengkel',
                                        'inactive' => 'Nonaktif',
                                        default => $veh->status
                                    } }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <button type="button" onclick="editVehicle({{ json_encode($veh) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 transition-colors" title="Edit Data">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.vehicles.destroy', $veh->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kendaraan {{ $veh->plate_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Hapus Kendaraan">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Belum ada armada kendaraan terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Kendaraan -->
<div id="add-vehicle-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-truck text-blue-400"></i>
                <span>Tambah Armada Kendaraan Baru</span>
            </h3>
            <button onclick="document.getElementById('add-vehicle-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.vehicles.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Plat Nomor *</label>
                    <input type="text" name="plate_number" required placeholder="Contoh: Z 8123 TA"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white uppercase focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tipe Kendaraan *</label>
                    <select name="type" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        <option value="blind_van">Blind Van</option>
                        <option value="pickup">Pickup Box</option>
                        <option value="truck_small">Truk Engkel (CDE)</option>
                        <option value="truck_heavy">Truk Double (CDD)</option>
                        <option value="motorcycle">Sepeda Motor</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Merk & Model *</label>
                <input type="text" name="brand_model" required placeholder="Contoh: Daihatsu Gran Max Blind Van 1.5"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kapasitas (Kg) *</label>
                    <input type="number" step="0.01" name="capacity_weight_kg" required value="750"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Volume (m³)</label>
                    <input type="number" step="0.01" name="capacity_volume_m3" value="3.5"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tahun</label>
                    <input type="number" name="year" value="{{ date('Y') }}" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Bahan Bakar</label>
                    <input type="text" name="fuel_type" value="Bensin Pertalite" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Status Awal</label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        <option value="available">Tersedia (Siaga)</option>
                        <option value="in_use">Dalam Tugas</option>
                        <option value="maintenance">Servis / Perbaikan</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Catatan Khusus</label>
                <textarea name="notes" rows="2" placeholder="Catatan kondisi kendaraan..."
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('add-vehicle-modal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/30">
                    Simpan Kendaraan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kendaraan -->
<div id="edit-vehicle-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-white text-base flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-blue-400"></i>
                <span>Edit Data Kendaraan</span>
            </h3>
            <button onclick="document.getElementById('edit-vehicle-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="edit-vehicle-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Plat Nomor *</label>
                    <input type="text" id="edit-plate" name="plate_number" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white uppercase focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tipe Kendaraan *</label>
                    <select id="edit-type" name="type" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        <option value="blind_van">Blind Van</option>
                        <option value="pickup">Pickup Box</option>
                        <option value="truck_small">Truk Engkel (CDE)</option>
                        <option value="truck_heavy">Truk Double (CDD)</option>
                        <option value="motorcycle">Sepeda Motor</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Merk & Model *</label>
                <input type="text" id="edit-brand" name="brand_model" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kapasitas (Kg) *</label>
                    <input type="number" step="0.01" id="edit-weight" name="capacity_weight_kg" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Volume (m³)</label>
                    <input type="number" step="0.01" id="edit-vol" name="capacity_volume_m3"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tahun</label>
                    <input type="number" id="edit-year" name="year" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Bahan Bakar</label>
                    <input type="text" id="edit-fuel" name="fuel_type" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Status</label>
                    <select id="edit-status" name="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white">
                        <option value="available">Tersedia (Siaga)</option>
                        <option value="in_use">Dalam Tugas</option>
                        <option value="maintenance">Servis / Perbaikan</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Catatan Khusus</label>
                <textarea id="edit-notes" name="notes" rows="2"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('edit-vehicle-modal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/30">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editVehicle(veh) {
        document.getElementById('edit-vehicle-form').action = `/admin/vehicles/${veh.id}`;
        document.getElementById('edit-plate').value = veh.plate_number;
        document.getElementById('edit-type').value = veh.type;
        document.getElementById('edit-brand').value = veh.brand_model;
        document.getElementById('edit-weight').value = veh.capacity_weight_kg;
        document.getElementById('edit-vol').value = veh.capacity_volume_m3 || '';
        document.getElementById('edit-year').value = veh.year;
        document.getElementById('edit-fuel').value = veh.fuel_type;
        document.getElementById('edit-status').value = veh.status;
        document.getElementById('edit-notes').value = veh.notes || '';

        document.getElementById('edit-vehicle-modal').classList.remove('hidden');
    }
</script>
@endpush
