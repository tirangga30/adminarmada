<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\LiveTrackingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Guest Root Redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('admin.dashboard') : redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::middleware(['web', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard & Live Fleet Map
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Live Tracking & GPS Monitor
    Route::get('/tracking', [LiveTrackingController::class, 'index'])->name('tracking');
    Route::get('/tracking/api/locations', [LiveTrackingController::class, 'getDriverLocations'])->name('tracking.locations');
    Route::get('/tracking/api/trail/{userId}', [LiveTrackingController::class, 'getDriverTrail'])->name('tracking.trail');

    // Deliveries Management & Admin Override
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::get('/deliveries/{id}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{id}/override', [DeliveryController::class, 'override'])->name('deliveries.override');
    Route::delete('/deliveries/{id}', [DeliveryController::class, 'destroy'])->name('deliveries.destroy');

    // Drivers Management
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/{id}', [DriverController::class, 'show'])->name('drivers.show');
    Route::put('/drivers/{id}', [DriverController::class, 'update'])->name('drivers.update');
    Route::post('/drivers/{id}/emergency', [DriverController::class, 'toggleEmergency'])->name('drivers.emergency');

    // Vehicles Fleet Management
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // User & Role Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // System Configurations
    Route::get('/configs', [SystemConfigController::class, 'index'])->name('configs.index');
    Route::post('/configs', [SystemConfigController::class, 'update'])->name('configs.update');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Profile Settings
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});
