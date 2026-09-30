<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('assignedDriver.user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('brand_model', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->latest()->paginate(10)->withQueryString();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'brand_model' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:motorcycle,blind_van,pickup,truck_small,truck_heavy'],
            'capacity_weight_kg' => ['required', 'numeric', 'min:1'],
            'capacity_volume_m3' => ['nullable', 'numeric', 'min:0'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'status' => ['required', 'in:available,in_use,maintenance,inactive'],
            'last_maintenance_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle = Vehicle::create($validated);

        AuditLog::record(
            action: 'VEHICLE_CREATED',
            targetType: 'Vehicle',
            targetId: (string) $vehicle->id,
            description: "Armada kendaraan baru ditambahkan: {$vehicle->plate_number} ({$vehicle->brand_model}).",
            metadata: $validated
        );

        return back()->with('success', "Kendaraan {$vehicle->plate_number} berhasil didaftarkan.");
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number,' . $id],
            'brand_model' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:motorcycle,blind_van,pickup,truck_small,truck_heavy'],
            'capacity_weight_kg' => ['required', 'numeric', 'min:1'],
            'capacity_volume_m3' => ['nullable', 'numeric', 'min:0'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'status' => ['required', 'in:available,in_use,maintenance,inactive'],
            'last_maintenance_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle->update($validated);

        AuditLog::record(
            action: 'VEHICLE_UPDATED',
            targetType: 'Vehicle',
            targetId: (string) $vehicle->id,
            description: "Data armada kendaraan {$vehicle->plate_number} diperbarui.",
            metadata: $validated
        );

        return back()->with('success', "Data kendaraan {$vehicle->plate_number} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $plate = $vehicle->plate_number;

        if ($vehicle->status === 'in_use') {
            return back()->with('error', "Kendaraan {$plate} sedang dalam perjalanan tugas dan tidak dapat dihapus.");
        }

        AuditLog::record(
            action: 'VEHICLE_DELETED',
            targetType: 'Vehicle',
            targetId: (string) $id,
            description: "Armada kendaraan {$plate} dihapus dari sistem."
        );

        $vehicle->delete();

        return back()->with('success', "Kendaraan {$plate} berhasil dihapus.");
    }
}
