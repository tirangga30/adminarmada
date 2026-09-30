<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeliveryItem;
use App\Models\DeliveryOrder;
use App\Models\DeliveryStop;
use App\Models\DriverProfile;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['driver', 'vehicle', 'stops', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('origin_address', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        $drivers = User::where('role', 'driver')->where('is_active', true)->get();
        $statuses = ['ASSIGNED', 'ACCEPTED', 'EN_ROUTE', 'ARRIVED', 'UNLOADING', 'DELIVERED', 'FAILED', 'CANCELLED'];

        return view('admin.deliveries.index', compact('orders', 'drivers', 'statuses'));
    }

    public function create()
    {
        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->with('driverProfile.vehicle')
            ->get();

        $vehicles = Vehicle::where('status', 'available')->get();

        $orderNumber = 'DO-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('admin.deliveries.create', compact('drivers', 'vehicles', 'orderNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'unique:delivery_orders,order_number'],
            'title' => ['required', 'string', 'max:255'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'route_mode' => ['required', 'in:manual,recommended,automatic'],
            'origin_address' => ['required', 'string'],
            'origin_latitude' => ['required', 'numeric'],
            'origin_longitude' => ['required', 'numeric'],
            'scheduled_at' => ['nullable', 'date'],
            'stops' => ['required', 'array', 'min:1'],
            'stops.*.customer_name' => ['required', 'string'],
            'stops.*.customer_phone' => ['nullable', 'string'],
            'stops.*.address' => ['required', 'string'],
            'stops.*.latitude' => ['required', 'numeric'],
            'stops.*.longitude' => ['required', 'numeric'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string'],
            'items.*.weight_kg' => ['nullable', 'numeric'],
        ]);

        DB::beginTransaction();
        try {
            $order = DeliveryOrder::create([
                'order_number' => $validated['order_number'],
                'owner_id' => Auth::id(),
                'driver_id' => $validated['driver_id'] ?? null,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'title' => $validated['title'],
                'status' => 'ASSIGNED',
                'priority' => $validated['priority'],
                'route_mode' => $validated['route_mode'],
                'origin_address' => $validated['origin_address'],
                'origin_latitude' => $validated['origin_latitude'],
                'origin_longitude' => $validated['origin_longitude'],
                'scheduled_at' => $validated['scheduled_at'] ?? now(),
            ]);

            $firstStopId = null;
            foreach ($validated['stops'] as $index => $stopData) {
                $stop = DeliveryStop::create([
                    'delivery_order_id' => $order->id,
                    'stop_sequence' => $index + 1,
                    'customer_name' => $stopData['customer_name'],
                    'customer_phone' => $stopData['customer_phone'] ?? null,
                    'address' => $stopData['address'],
                    'latitude' => $stopData['latitude'],
                    'longitude' => $stopData['longitude'],
                    'status' => 'pending',
                ]);

                if ($index === 0) {
                    $firstStopId = $stop->id;
                }
            }

            foreach ($validated['items'] as $itemData) {
                DeliveryItem::create([
                    'delivery_order_id' => $order->id,
                    'delivery_stop_id' => $firstStopId,
                    'item_name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'weight_kg' => $itemData['weight_kg'] ?? null,
                ]);
            }

            // Update driver and vehicle status if assigned
            if ($order->driver_id) {
                DriverProfile::where('user_id', $order->driver_id)->update(['status' => 'delivering']);
            }
            if ($order->vehicle_id) {
                Vehicle::where('id', $order->vehicle_id)->update(['status' => 'in_use']);
            }

            AuditLog::record(
                action: 'DELIVERY_CREATED',
                targetType: 'DeliveryOrder',
                targetId: (string) $order->id,
                description: "Order pengiriman baru {$order->order_number} dibuat oleh " . Auth::user()->name,
                metadata: [
                    'order_number' => $order->order_number,
                    'driver_id' => $order->driver_id,
                    'priority' => $order->priority,
                    'total_stops' => count($validated['stops']),
                ]
            );

            DB::commit();
            return redirect()->route('admin.deliveries.show', $order->id)->with('success', "Order pengiriman {$order->order_number} berhasil dibuat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat pengiriman: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = DeliveryOrder::with([
            'owner',
            'driver.driverProfile',
            'vehicle',
            'stops.items',
            'stops.proofOfDelivery',
            'items',
            'proofOfDelivery',
            'proofsOfDelivery',
            'locationEvents',
            'messages.sender',
        ])->findOrFail($id);

        $availableDrivers = User::where('role', 'driver')->where('is_active', true)->get();
        $availableVehicles = Vehicle::whereIn('status', ['available', 'in_use'])->get();

        return view('admin.deliveries.show', compact('order', 'availableDrivers', 'availableVehicles'));
    }

    /**
     * Admin Support / Override feature (Section 3.2, 3.4, 3.11, 3.15)
     */
    public function override(Request $request, $id)
    {
        $order = DeliveryOrder::findOrFail($id);

        $validated = $request->validate([
            'new_status' => ['required', 'in:ASSIGNED,ACCEPTED,EN_ROUTE,ARRIVED,UNLOADING,DELIVERED,FAILED,CANCELLED'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'admin_notes' => ['required', 'string', 'min:3'],
            'failure_reason' => ['nullable', 'string'],
        ]);

        $oldStatus = $order->status;
        $oldDriverId = $order->driver_id;
        $oldVehicleId = $order->vehicle_id;

        $order->status = $validated['new_status'];
        $order->admin_override_notes = $validated['admin_notes'];

        if (!empty($validated['driver_id'])) {
            $order->driver_id = $validated['driver_id'];
        }

        if (!empty($validated['vehicle_id'])) {
            $order->vehicle_id = $validated['vehicle_id'];
        }

        if ($validated['new_status'] === 'FAILED') {
            $order->failure_reason = $validated['failure_reason'] ?? $validated['admin_notes'];
        } elseif ($validated['new_status'] === 'CANCELLED') {
            $order->cancellation_reason = $validated['admin_notes'];
        } elseif ($validated['new_status'] === 'DELIVERED') {
            $order->completed_at = now();
        }

        $order->save();

        // If status completed or cancelled or failed, free up old vehicle and driver
        if (in_array($validated['new_status'], ['DELIVERED', 'FAILED', 'CANCELLED'])) {
            if ($oldDriverId) {
                DriverProfile::where('user_id', $oldDriverId)->update(['status' => 'available']);
            }
            if ($oldVehicleId) {
                Vehicle::where('id', $oldVehicleId)->update(['status' => 'available']);
            }
        }

        AuditLog::record(
            action: 'DELIVERY_OVERRIDE',
            targetType: 'DeliveryOrder',
            targetId: (string) $order->id,
            description: "Admin melakukan override pengiriman {$order->order_number}: Status diubah dari [{$oldStatus}] ke [{$order->status}]. Catatan: {$validated['admin_notes']}",
            metadata: [
                'old_status' => $oldStatus,
                'new_status' => $order->status,
                'old_driver_id' => $oldDriverId,
                'new_driver_id' => $order->driver_id,
                'notes' => $validated['admin_notes'],
            ]
        );

        return back()->with('success', "Intervensi/Override pengiriman {$order->order_number} berhasil diterapkan dan dicatat di Audit Log!");
    }

    public function destroy($id)
    {
        $order = DeliveryOrder::findOrFail($id);
        $orderNumber = $order->order_number;

        AuditLog::record(
            action: 'DELIVERY_DELETED',
            targetType: 'DeliveryOrder',
            targetId: (string) $id,
            description: "Admin menghapus order pengiriman {$orderNumber}."
        );

        $order->delete();

        return redirect()->route('admin.deliveries.index')->with('success', "Order {$orderNumber} telah dihapus.");
    }
}
