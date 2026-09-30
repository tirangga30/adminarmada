<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DriverProfile;
use App\Models\LocationEvent;
use Illuminate\Http\Request;

class LiveTrackingController extends Controller
{
    public function index()
    {
        $drivers = DriverProfile::with(['user', 'vehicle'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();

        $activeOrders = DeliveryOrder::with(['driver', 'vehicle', 'stops'])
            ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'EN_ROUTE', 'ARRIVED', 'UNLOADING'])
            ->get();

        return view('admin.tracking.index', compact('drivers', 'activeOrders'));
    }

    public function getDriverLocations()
    {
        $drivers = DriverProfile::with(['user', 'vehicle'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get()
            ->map(function ($profile) {
                $order = $profile->activeDeliveryOrder();
                return [
                    'id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'name' => $profile->user->name,
                    'phone' => $profile->user->phone,
                    'status' => $profile->status,
                    'emergency_status' => (bool) $profile->emergency_status,
                    'emergency_note' => $profile->emergency_note,
                    'lat' => (float) $profile->current_latitude,
                    'lng' => (float) $profile->current_longitude,
                    'battery' => $profile->battery_level,
                    'speed' => (float) $profile->speed,
                    'heading' => (float) $profile->heading,
                    'accuracy' => (float) $profile->accuracy,
                    'last_updated' => $profile->last_location_time ? $profile->last_location_time->format('H:i:s') : '-',
                    'plate_number' => $profile->vehicle?->plate_number ?? '-',
                    'vehicle_model' => $profile->vehicle?->brand_model ?? '-',
                    'vehicle_type' => $profile->vehicle?->type ?? 'blind_van',
                    'order' => $order ? [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'title' => $order->title,
                        'status' => $order->status,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'drivers' => $drivers,
        ]);
    }

    public function getDriverTrail($userId)
    {
        $trail = LocationEvent::where('driver_id', $userId)
            ->latest('recorded_at')
            ->take(50)
            ->get()
            ->reverse()
            ->map(function ($event) {
                return [
                    'lat' => (float) $event->latitude,
                    'lng' => (float) $event->longitude,
                    'speed' => $event->speed,
                    'battery' => $event->battery_level,
                    'time' => $event->recorded_at->format('H:i:s'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'trail' => $trail,
        ]);
    }
}
