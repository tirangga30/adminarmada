<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Armada') - Sistem Manajemen Distribusi & Pelacakan Driver</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet Maps CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Tailwind CSS (CDN for zero-fail styling) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        dark: {
                            800: '#1e293b',
                            850: '#131b2e',
                            900: '#0f172a',
                            950: '#0b0f19',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet JS & Chart.js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>
<body class="bg-[#0B0F19] text-slate-100 min-h-screen flex flex-col antialiased selection:bg-blue-500 selection:text-white">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="w-64 bg-[#0F172A]/90 border-r border-slate-800/80 flex flex-col z-30 transition-all duration-300 backdrop-blur-xl shrink-0">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center px-5 border-b border-slate-800/80 gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-400 flex items-center justify-center text-white shadow-lg shadow-blue-500/25">
                    <i class="fa-solid fa-truck-fast text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-base tracking-wide text-white flex items-center gap-1.5">
                        ARMADA <span class="text-xs px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 font-medium">ADMIN</span>
                    </span>
                    <span class="text-[11px] text-slate-400">UNSIL Priangan Logistics</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <div class="px-3 pb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Operasional Utama
                </div>

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-base {{ request()->routeIs('admin.dashboard') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.tracking') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.tracking*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-satellite-dish w-5 text-center text-base {{ request()->routeIs('admin.tracking*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span class="flex-1">Live Tracking</span>
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </a>

                <a href="{{ route('admin.deliveries.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.deliveries*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-boxes-packing w-5 text-center text-base {{ request()->routeIs('admin.deliveries*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Pengiriman & Override</span>
                </a>

                <div class="pt-4 px-3 pb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Manajemen Armada
                </div>

                <a href="{{ route('admin.drivers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.drivers*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-id-card w-5 text-center text-base {{ request()->routeIs('admin.drivers*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Data Driver (Pengemudi)</span>
                </a>

                <a href="{{ route('admin.vehicles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.vehicles*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-van-shuttle w-5 text-center text-base {{ request()->routeIs('admin.vehicles*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Armada Kendaraan</span>
                </a>

                <div class="pt-4 px-3 pb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Sistem & Keamanan
                </div>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.reports*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center text-base {{ request()->routeIs('admin.reports*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Laporan & Analitik</span>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.audit-logs*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-shield-halved w-5 text-center text-base {{ request()->routeIs('admin.audit-logs*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Audit Log</span>
                </a>

                <a href="{{ route('admin.configs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.configs*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-base {{ request()->routeIs('admin.configs*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Konfigurasi Sistem</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center text-base {{ request()->routeIs('admin.users*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span>Kelola Pengguna</span>
                </a>
            </div>

            <!-- User Footer Profile Card -->
            <div class="p-3 border-t border-slate-800/80">
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900/80 border border-slate-800">
                    <a href="{{ route('admin.profile') }}" class="flex items-center gap-3 min-w-0 flex-1 hover:opacity-80 transition-opacity">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-indigo-500 to-blue-500 flex items-center justify-center font-bold text-white text-sm shrink-0 shadow-md">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300 font-medium uppercase">
                                {{ auth()->user()->role ?? 'Admin' }}
                            </span>
                        </div>
                    </a>
                    <a href="{{ route('logout') }}" title="Keluar" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="p-2 text-slate-400 hover:text-rose-400 transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header Topbar -->
            <header class="h-16 bg-[#0F172A]/70 border-b border-slate-800/80 px-6 flex items-center justify-between backdrop-blur-xl shrink-0 z-20">
                <div class="flex items-center gap-4">
                    <button id="toggle-sidebar" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors md:hidden">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-white tracking-tight">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-xs text-slate-400 hidden sm:block">@yield('page_subtitle', 'Pusat Manajemen Armada & Monitoring Distribusi')</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- GPS Live Beacon -->
                    <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-slate-300 font-medium">GPS Tracking Active</span>
                        <span class="text-slate-400 text-[11px]">(10s)</span>
                    </div>

                    <!-- Quick Time Indicator -->
                    <div class="hidden md:flex items-center gap-2 text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-800/60">
                        <i class="fa-regular fa-clock text-slate-400"></i>
                        <span id="live-clock">--:--:-- WIB</span>
                    </div>

                    <!-- Quick Action: Buat Delivery -->
                    <a href="{{ route('admin.deliveries.create') }}" class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/35 active:scale-95">
                        <i class="fa-solid fa-plus"></i>
                        <span class="hidden sm:inline">Buat Pengiriman</span>
                    </a>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
                <!-- Flash Notification Alerts -->
                @if(session('success'))
                    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center justify-between gap-3 shadow-lg shadow-emerald-500/5 animate-fade-in">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-400">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center justify-between gap-3 shadow-lg shadow-rose-500/5 animate-fade-in">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-exclamation text-rose-400 text-base"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm flex items-center justify-between gap-3 shadow-lg shadow-amber-500/5 animate-fade-in">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base"></i>
                            <span>{{ session('warning') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-amber-400/60 hover:text-amber-400">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm shadow-lg shadow-rose-500/5 space-y-1">
                        <div class="font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-circle-xmark"></i> Terjadi kesalahan input:
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 pl-2 text-rose-300">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Live Clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            const el = document.getElementById('live-clock');
            if (el) el.innerText = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
