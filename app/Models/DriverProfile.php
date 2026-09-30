<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'license_number',
        'license_type',
        'license_expiry',
        'status',
        'emergency_status',
        'emergency_note',
        'current_latitude',
        'current_longitude',
        'last_location_time',
        'battery_level',
        'speed',
        'heading',
        'accuracy',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'emergency_status' => 'boolean',
            'current_latitude' => 'decimal:7',
            'current_longitude' => 'decimal:7',
            'last_location_time' => 'datetime',
            'battery_level' => 'integer',
            'speed' => 'decimal:2',
            'heading' => 'decimal:2',
            'accuracy' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function locationEvents(): HasMany
    {
        return $this->hasMany(LocationEvent::class, 'driver_id', 'user_id');
    }

    public function activeDeliveryOrder()
    {
        return DeliveryOrder::where('driver_id', $this->user_id)
            ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'EN_ROUTE', 'ARRIVED', 'UNLOADING'])
            ->latest()
            ->first();
    }
}
