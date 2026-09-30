@extends('layouts.admin')

@section('title', 'Konfigurasi Sistem')
@section('page_title', 'Konfigurasi Sistem (F-ADM-04)')
@section('page_subtitle', 'Pengaturan parameter GPS tracking, provider rute, batas akurasi, dan identitas operasional')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <form action="{{ route('admin.configs.update') }}" method="POST" class="space-y-6">
        @csrf

        @foreach($configs as $groupName => $groupConfigs)
            <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl space-y-4">
                <div class="border-b border-slate-800 pb-2">
                    <h3 class="font-extrabold text-white text-sm uppercase tracking-wider flex items-center gap-2">
                        @if($groupName === 'tracking')
                            <i class="fa-solid fa-satellite text-blue-400"></i>
                            <span>Parameter GPS Tracking & Realtime</span>
                        @elseif($groupName === 'routing')
                            <i class="fa-solid fa-route text-emerald-400"></i>
                            <span>Provider Peta & Algoritma Routing</span>
                        @elseif($groupName === 'company')
                            <i class="fa-solid fa-building text-amber-400"></i>
                            <span>Identitas Hub Perusahaan & Kontak</span>
                        @else
                            <i class="fa-solid fa-sliders text-indigo-400"></i>
                            <span>Pengaturan Umum & Operasional</span>
                        @endif
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($groupConfigs as $cfg)
                        <div class="{{ strlen($cfg->value) > 40 ? 'sm:col-span-2' : '' }}">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                                <span>{{ $cfg->label }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $cfg->key }}</span>
                            </label>

                            @if($cfg->key === 'routing_provider')
                                <select name="{{ $cfg->key }}" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
                                    <option value="OSRM" {{ $cfg->value === 'OSRM' ? 'selected' : '' }}>Open Source Routing Machine (OSRM)</option>
                                    <option value="Google Maps" {{ $cfg->value === 'Google Maps' ? 'selected' : '' }}>Google Maps Platform Routes API</option>
                                    <option value="OpenStreetMap" {{ $cfg->value === 'OpenStreetMap' ? 'selected' : '' }}>OpenStreetMap Generic</option>
                                </select>
                            @else
                                <input type="{{ $cfg->type === 'number' ? 'number' : 'text' }}" 
                                    name="{{ $cfg->key }}" 
                                    value="{{ $cfg->value }}"
                                    class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-blue-500">
                            @endif

                            @if($cfg->description)
                                <p class="text-[11px] text-slate-400 mt-1">{{ $cfg->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-500/25 flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Konfigurasi Sistem</span>
            </button>
        </div>
    </form>

</div>
@endsection
