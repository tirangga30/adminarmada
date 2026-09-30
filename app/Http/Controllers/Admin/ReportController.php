<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DriverProfile;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $deliveriesQuery = DeliveryOrder::whereBetween('created_at', [$startDate, $endDate]);

        $totalDeliveries = (clone $deliveriesQuery)->count();
        $completedDeliveries = (clone $deliveriesQuery)->where('status', 'DELIVERED')->count();
        $failedDeliveries = (clone $deliveriesQuery)->where('status', 'FAILED')->count();
        $cancelledDeliveries = (clone $deliveriesQuery)->where('status', 'CANCELLED')->count();

        $successRate = $totalDeliveries > 0 ? round(($completedDeliveries / $totalDeliveries) * 100, 1) : 0;

        // Status breakdown
        $statusBreakdown = DeliveryOrder::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Driver performance summary
        $drivers = DriverProfile::with('user')->get()->map(function ($dp) use ($startDate, $endDate) {
            $totalOrders = DeliveryOrder::where('driver_id', $dp->user_id)->whereBetween('created_at', [$startDate, $endDate])->count();
            $completedOrders = DeliveryOrder::where('driver_id', $dp->user_id)->where('status', 'DELIVERED')->whereBetween('created_at', [$startDate, $endDate])->count();
            return [
                'name' => $dp->user->name,
                'license' => $dp->license_number,
                'status' => $dp->status,
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100) : 0,
            ];
        });

        // Vehicles summary
        $vehicles = Vehicle::withCount(['deliveryOrders as total_orders' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        return view('admin.reports.index', compact(
            'totalDeliveries',
            'completedDeliveries',
            'failedDeliveries',
            'cancelledDeliveries',
            'successRate',
            'statusBreakdown',
            'drivers',
            'vehicles',
            'startDate',
            'endDate'
        ));
    }
}
