<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeliveryOrder;
use App\Models\DeliveryStop;
use App\Models\DriverProfile;
use App\Models\LocationEvent;
use App\Models\ProofOfDelivery;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    /**
     * API Login for Mobile (Owner & Driver)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak valid.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan oleh administrator.',
            ], 403);
        }

        $deviceName = $request->device_name ?? 'mobile_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'driver_profile' => $user->isDriver() ? $user->driverProfile : null,
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        if ($user->isDriver()) {
            $user->load('driverProfile.vehicle');
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Driver Tasks Endpoint (F-DRV-01)
     */
    public function driverTasks(Request $request)
    {
        $user = $request->user();
        if (!$user->isDriver()) {
            return response()->json(['success' => false, 'message' => 'Akses terbatas untuk pengemudi.'], 403);
        }

        $orders = DeliveryOrder::with(['stops.items', 'vehicle', 'items', 'proofOfDelivery'])
            ->where('driver_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Driver Delivery Update (F-DRV-03)
     * ASSIGNED -> ACCEPTED -> EN_ROUTE -> ARRIVED -> UNLOADING -> DELIVERED / FAILED
     */
    public function driverUpdateTaskStatus(Request $request, $id)
    {
        $user = $request->user();
        $order = DeliveryOrder::where('id', $id)->where('driver_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:ACCEPTED,EN_ROUTE,ARRIVED,UNLOADING,DELIVERED,FAILED',
            'failure_reason' => 'nullable|string',
        ]);

        $order->status = $validated['status'];
        if ($validated['status'] === 'EN_ROUTE' && !$order->started_at) {
            $order->started_at = now();
        } elseif ($validated['status'] === 'DELIVERED') {
            $order->completed_at = now();
        } elseif ($validated['status'] === 'FAILED') {
            $order->failure_reason = $validated['failure_reason'] ?? 'Gagal tanpa catatan spesifik';
        }

        $order->save();

        // Update driver status
        if ($validated['status'] === 'DELIVERED' || $validated['status'] === 'FAILED') {
            DriverProfile::where('user_id', $user->id)->update(['status' => 'available']);
            if ($order->vehicle_id) {
                Vehicle::where('id', $order->vehicle_id)->update(['status' => 'available']);
            }
        } else {
            DriverProfile::where('user_id', $user->id)->update(['status' => 'delivering']);
        }

        AuditLog::record(
            action: 'DRIVER_STATUS_UPDATE',
            targetType: 'DeliveryOrder',
            targetId: (string) $order->id,
            description: "Driver {$user->name} memperbarui status pengiriman {$order->order_number} menjadi {$order->status}.",
            metadata: ['status' => $order->status],
            actor: $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Status pengiriman berhasil diperbarui.',
            'order' => $order,
        ]);
    }

    /**
     * Submit Proof of Delivery (F-DRV-04)
     */
    public function driverSubmitPod(Request $request, $id)
    {
        $user = $request->user();
        $order = DeliveryOrder::where('id', $id)->where('driver_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_relation' => 'required|string|max:100',
            'signature_data' => 'nullable|string',
            'notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_stop_id' => 'nullable|exists:delivery_stops,id',
        ]);

        $pod = ProofOfDelivery::create([
            'delivery_order_id' => $order->id,
            'delivery_stop_id' => $validated['delivery_stop_id'] ?? null,
            'driver_id' => $user->id,
            'receiver_name' => $validated['receiver_name'],
            'receiver_relation' => $validated['receiver_relation'],
            'signature_data' => $validated['signature_data'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'delivered_at' => now(),
        ]);

        $order->update(['status' => 'DELIVERED', 'completed_at' => now()]);
        DriverProfile::where('user_id', $user->id)->update(['status' => 'available']);
        if ($order->vehicle_id) {
            Vehicle::where('id', $order->vehicle_id)->update(['status' => 'available']);
        }

        AuditLog::record(
            action: 'POD_SUBMITTED',
            targetType: 'ProofOfDelivery',
            targetId: (string) $pod->id,
            description: "Driver {$user->name} mengirimkan Proof of Delivery untuk order {$order->order_number}.",
            actor: $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Bukti pengiriman (POD) berhasil disimpan.',
            'pod' => $pod,
        ]);
    }

    /**
     * Driver GPS Tracking (F-DRV-02, Section 3.12)
     */
    public function trackingSubmitLocation(Request $request)
    {
        $user = $request->user();
        if (!$user->isDriver()) {
            return response()->json(['success' => false, 'message' => 'Hanya driver yang dapat mengirim lokasi.'], 403);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'battery_level' => 'nullable|integer',
            'delivery_order_id' => 'nullable|exists:delivery_orders,id',
        ]);

        // Save location event
        $event = LocationEvent::create([
            'driver_id' => $user->id,
            'delivery_order_id' => $validated['delivery_order_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy' => $validated['accuracy'] ?? null,
            'speed' => $validated['speed'] ?? 0,
            'heading' => $validated['heading'] ?? 0,
            'battery_level' => $validated['battery_level'] ?? null,
            'recorded_at' => now(),
        ]);

        // Update driver profile current location
        DriverProfile::where('user_id', $user->id)->update([
            'current_latitude' => $validated['latitude'],
            'current_longitude' => $validated['longitude'],
            'last_location_time' => now(),
            'battery_level' => $validated['battery_level'] ?? null,
            'speed' => $validated['speed'] ?? 0,
            'heading' => $validated['heading'] ?? 0,
            'accuracy' => $validated['accuracy'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi GPS berhasil diperbarui.',
        ]);
    }

    /**
     * Driver Emergency SOS Toggle
     */
    public function driverEmergency(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'emergency' => 'required|boolean',
            'note' => 'nullable|string',
        ]);

        $profile = DriverProfile::where('user_id', $user->id)->firstOrFail();
        $profile->update([
            'emergency_status' => $validated['emergency'],
            'emergency_note' => $validated['emergency'] ? ($validated['note'] ?? 'Sinyal darurat dikirim dari smartphone driver') : null,
        ]);

        AuditLog::record(
            action: 'DRIVER_EMERGENCY_TRIGGERED',
            targetType: 'DriverProfile',
            targetId: (string) $profile->id,
            description: "Driver {$user->name} memicu sinyal darurat (SOS): " . ($validated['note'] ?? 'Darurat di jalan.'),
            actor: $user
        );

        return response()->json([
            'success' => true,
            'message' => $validated['emergency'] ? 'Sinyal darurat telah dikirim ke pusat komando admin.' : 'Status darurat dibatalkan.',
        ]);
    }

    /**
     * System Config for Mobile Apps
     */
    public function systemConfig()
    {
        $configs = SystemConfig::pluck('value', 'key');
        return response()->json([
            'success' => true,
            'configs' => $configs,
        ]);
    }
}
