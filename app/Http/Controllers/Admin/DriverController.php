<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverProfile;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverProfile::with(['user', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('emergency')) {
            $query->where('emergency_status', (bool) $request->emergency);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $drivers = $query->latest()->paginate(10)->withQueryString();
        $vehicles = Vehicle::where('status', 'available')->get();

        return view('admin.drivers.index', compact('drivers', 'vehicles'));
    }

    public function show($id)
    {
        $driver = DriverProfile::with(['user', 'vehicle', 'user.deliveryOrdersAsDriver'])->findOrFail($id);

        $recentLocations = $driver->locationEvents()
            ->latest('recorded_at')
            ->take(20)
            ->get();

        $activeOrder = $driver->activeDeliveryOrder();

        $availableVehicles = Vehicle::where('status', 'available')
            ->orWhere('id', $driver->vehicle_id)
            ->get();

        return view('admin.drivers.show', compact('driver', 'recentLocations', 'activeOrder', 'availableVehicles'));
    }

    public function update(Request $request, $id)
    {
        $driver = DriverProfile::findOrFail($id);

        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'license_number' => ['required', 'string', 'max:50'],
            'license_type' => ['required', 'string', 'max:30'],
            'license_expiry' => ['nullable', 'date'],
            'status' => ['required', 'in:available,delivering,off_duty,suspended'],
        ]);

        $oldVehicleId = $driver->vehicle_id;
        $driver->update($validated);

        if ($validated['vehicle_id'] !== $oldVehicleId) {
            if ($oldVehicleId) {
                Vehicle::where('id', $oldVehicleId)->update(['status' => 'available']);
            }
            if ($validated['vehicle_id']) {
                Vehicle::where('id', $validated['vehicle_id'])->update(['status' => 'in_use']);
            }
        }

        AuditLog::record(
            action: 'DRIVER_UPDATED',
            targetType: 'DriverProfile',
            targetId: (string) $driver->id,
            description: "Admin memperbarui profil driver {$driver->user->name}."
        );

        return back()->with('success', "Data pengemudi {$driver->user->name} berhasil diperbarui.");
    }

    public function toggleEmergency(Request $request, $id)
    {
        $driver = DriverProfile::findOrFail($id);
        $newStatus = !$driver->emergency_status;

        $driver->update([
            'emergency_status' => $newStatus,
            'emergency_note' => $newStatus ? ($request->input('note') ?? 'Darurat diaktifkan oleh admin/sistem') : null,
        ]);

        $statusText = $newStatus ? 'DIAKTIFKAN' : 'DINONAKTIFKAN/DIRESET';

        AuditLog::record(
            action: 'DRIVER_EMERGENCY_STATUS',
            targetType: 'DriverProfile',
            targetId: (string) $driver->id,
            description: "Status darurat (SOS) driver {$driver->user->name} {$statusText} oleh Admin."
        );

        return back()->with('success', "Status darurat driver {$driver->user->name} berhasil diubah.");
    }
}
