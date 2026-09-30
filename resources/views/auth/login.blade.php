<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin - Sistem Manajemen Armada & Distribusi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#070B14] text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden font-sans">

    <!-- Ambient Glowing Orbs Background -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-[128px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-[128px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-cyan-600/10 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-400 text-white shadow-xl shadow-blue-500/25 border border-white/20">
                <i class="fa-solid fa-truck-fast text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">ARMADA PRIANGAN</h1>
                <p class="text-xs text-slate-400 mt-1">Sistem Manajemen Distribusi Barang & Pelacakan Driver Realtime</p>
                <div class="inline-block mt-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-[11px] text-slate-300 font-medium">
                    Informatika - Universitas Siliwangi Tasikmalaya
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/80 border border-slate-800/90 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl space-y-6">
            @if(session('error'))
                <div class="p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-sm shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-3.5 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-400 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-circle-info text-sm shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Akun</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email', 'admin@armada.test') }}" required
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                    </div>
                    @error('email')
                        <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" value="admin123" required
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                    </div>
                    @error('password')
                        <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-blue-600 focus:ring-blue-500">
                        <span>Ingat sesi masuk</span>
                    </label>
                    <span class="text-slate-500">Web Portal Admin</span>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-blue-500/25 transition-all duration-200 hover:shadow-blue-500/40 active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk ke Web Panel</span>
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="pt-4 border-t border-slate-800/80 space-y-2">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block text-center">Akun Masuk Cepat (Administrator)</span>
                <div>
                    <button type="button" onclick="setAccount('admin@armada.test', 'admin123')" class="w-full p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 text-xs text-slate-200 border border-slate-700/60 hover:border-blue-500/50 transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-shield text-blue-400"></i>
                        <span>Login sebagai Super Admin (admin@armada.test)</span>
                    </button>
                </div>
                <p class="text-[10px] text-center text-slate-500 italic mt-1">
                    * Akun Owner & Driver digunakan untuk pengujian Aplikasi Mobile melalui REST API.
                </p>
            </div>
        </div>

        <div class="text-center text-xs text-slate-400">
            &copy; 2026 Capstone Project &bull; Friza, Tirangga, Gita
        </div>
    </div>

    <script>
        function setAccount(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
