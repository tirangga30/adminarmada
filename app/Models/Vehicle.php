<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'brand_model',
        'type',
        'capacity_weight_kg',
        'capacity_volume_m3',
        'fuel_type',
        'year',
        'status',
        'last_maintenance_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity_weight_kg' => 'decimal:2',
            'capacity_volume_m3' => 'decimal:2',
            'year' => 'integer',
            'last_maintenance_at' => 'date',
        ];
    }

    public function assignedDriver(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'vehicle_id');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'vehicle_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'motorcycle' => 'Sepeda Motor',
            'blind_van' => 'Blind Van',
            'pickup' => 'Pickup Box',
            'truck_small' => 'Truk Engkel (CDE)',
            'truck_heavy' => 'Truk Double (CDD)',
            default => ucfirst($this->type),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available' => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
            'in_use' => 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/30',
            'maintenance' => 'bg-amber-500/15 text-amber-400 border border-amber-500/30',
            'inactive' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
            default => 'bg-slate-500/15 text-slate-400 border border-slate-500/30',
        };
    }
}
