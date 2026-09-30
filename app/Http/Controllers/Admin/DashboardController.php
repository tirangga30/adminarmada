<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeliveryOrder;
use App\Models\DriverProfile;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Drivers stats
        $totalDrivers = DriverProfile::count();
        $deliveringDrivers = DriverProfile::where('status', 'delivering')->count();
        $availableDrivers = DriverProfile::where('status', 'available')->count();
        $offDutyDrivers = DriverProfile::where('status', 'off_duty')->count();

        // Vehicles stats
        $totalVehicles = Vehicle::count();
        $inUseVehicles = Vehicle::where('status', 'in_use')->count();
        $availableVehicles = Vehicle::where('status', 'available')->count();
        $maintenanceVehicles = Vehicle::where('status', 'maintenance')->count();

        // Deliveries stats
        $todayDeliveries = DeliveryOrder::whereDate('created_at', $today)->orWhereDate('scheduled_at', $today)->count();
        $activeDeliveries = DeliveryOrder::whereIn('status', ['ASSIGNED', 'ACCEPTED', 'EN_ROUTE', 'ARRIVED', 'UNLOADING'])->count();
        $deliveredDeliveries = DeliveryOrder::where('status', 'DELIVERED')->whereDate('completed_at', $today)->count();
        $failedDeliveries = DeliveryOrder::where('status', 'FAILED')->whereDate('updated_at', $today)->count();

        // Recent orders
        $recentDeliveries = DeliveryOrder::with(['driver', 'vehicle', 'stops', 'stops.items'])
            ->latest()
            ->take(6)
            ->get();

        // Active drivers with coordinates for the map
        $activeDrivers = DriverProfile::with(['user', 'vehicle'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get()
            ->map(function ($profile) {
                $activeOrder = $profile->activeDeliveryOrder();
                return [
                    'id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'name' => $profile->user->name,
                    'phone' => $profile->user->phone,
                    'status' => $profile->status,
                    'emergency_status' => $profile->emergency_status,
                    'emergency_note' => $profile->emergency_note,
                    'latitude' => (float) $profile->current_latitude,
                    'longitude' => (float) $profile->current_longitude,
                    'battery' => $profile->battery_level,
                    'speed' => $profile->speed,
                    'heading' => $profile->heading,
                    'last_updated' => $profile->last_location_time?->diffForHumans() ?? 'Belum ada',
                    'plate_number' => $profile->vehicle?->plate_number ?? 'Tanpa Kendaraan',
                    'vehicle_name' => $profile->vehicle?->brand_model ?? '-',
                    'vehicle_type' => $profile->vehicle?->type ?? 'blind_van',
                    'active_order' => $activeOrder ? [
                        'order_number' => $activeOrder->order_number,
                        'title' => $activeOrder->title,
                        'status' => $activeOrder->status,
                    ] : null,
                ];
            });

        // Emergencies
        $emergencyAlerts = DriverProfile::with('user')
            ->where('emergency_status', true)
            ->get();

        // Recent Audit logs
        $recentAudits = AuditLog::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalDrivers',
            'deliveringDrivers',
            'availableDrivers',
            'offDutyDrivers',
            'totalVehicles',
            'inUseVehicles',
            'availableVehicles',
            'maintenanceVehicles',
            'todayDeliveries',
            'activeDeliveries',
            'deliveredDeliveries',
            'failedDeliveries',
            'recentDeliveries',
            'activeDrivers',
            'emergencyAlerts',
            'recentAudits'
        ));
    }
}
