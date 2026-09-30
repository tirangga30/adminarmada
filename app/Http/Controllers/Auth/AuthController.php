<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda dinonaktifkan oleh administrator.');
            }

            // Only Admin is allowed to login to the Web Panel
            if (!$user->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', "Akses Ditolak: Akun dengan peran [{$user->role}] hanya dapat digunakan pada Aplikasi Mobile. Web Panel ini khusus untuk Administrator.");
            }

            $user->update(['last_login_at' => now()]);

            AuditLog::record(
                action: 'AUTH_LOGIN',
                targetType: 'User',
                targetId: (string) $user->id,
                description: "Pengguna {$user->name} ({$user->role}) berhasil masuk ke Web Admin Panel.",
                metadata: ['email' => $user->email, 'ip' => $request->ip()]
            );

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))->with('success', "Selamat datang kembali, {$user->name}!");
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::record(
                action: 'AUTH_LOGOUT',
                targetType: 'User',
                targetId: (string) $user->id,
                description: "Pengguna {$user->name} telah keluar dari sesi web panel.",
                actor: $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->save();

        AuditLog::record(
            action: 'PROFILE_UPDATED',
            targetType: 'User',
            targetId: (string) $user->id,
            description: "Pengguna {$user->name} memperbarui data profil.",
            actor: $user
        );

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
