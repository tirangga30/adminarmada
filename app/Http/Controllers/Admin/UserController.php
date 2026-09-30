<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('driverProfile');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Enforce rule: Owner cannot create Admin
        $allowedRoles = $currentUser->isAdmin() ? ['admin', 'owner', 'driver'] : ['owner', 'driver'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // If driver, create driver profile automatically
        if ($user->isDriver()) {
            DriverProfile::create([
                'user_id' => $user->id,
                'license_type' => 'SIM A',
                'status' => 'available',
            ]);
        }

        AuditLog::record(
            action: 'USER_CREATED',
            targetType: 'User',
            targetId: (string) $user->id,
            description: "Pengguna baru dibuat: {$user->name} dengan peran [{$user->role}] oleh {$currentUser->name}.",
            metadata: ['role' => $user->role, 'email' => $user->email]
        );

        return back()->with('success', "Pengguna {$user->name} ({$user->role}) berhasil didaftarkan.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        // Owner cannot promote or demote Admin
        $allowedRoles = $currentUser->isAdmin() ? ['admin', 'owner', 'driver'] : ['owner', 'driver'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $oldRole = $user->role;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->is_active = $request->boolean('is_active');

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($user->isDriver() && !$user->driverProfile) {
            DriverProfile::create([
                'user_id' => $user->id,
                'license_type' => 'SIM A',
                'status' => 'available',
            ]);
        }

        AuditLog::record(
            action: 'USER_UPDATED',
            targetType: 'User',
            targetId: (string) $user->id,
            description: "Data pengguna {$user->name} diperbarui oleh {$currentUser->name}.",
            metadata: ['old_role' => $oldRole, 'new_role' => $user->role, 'is_active' => $user->is_active]
        );

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $stateText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record(
            action: 'USER_STATUS_TOGGLED',
            targetType: 'User',
            targetId: (string) $user->id,
            description: "Status akun {$user->name} {$stateText} oleh " . Auth::user()->name
        );

        return back()->with('success', "Akun {$user->name} berhasil {$stateText}.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $role = $user->role;

        AuditLog::record(
            action: 'USER_DELETED',
            targetType: 'User',
            targetId: (string) $id,
            description: "Akun {$name} ({$role}) dihapus dari sistem."
        );

        $user->delete();

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }
}
