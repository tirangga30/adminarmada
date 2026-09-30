<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'owner_id',
        'driver_id',
        'vehicle_id',
        'title',
        'status',
        'priority',
        'route_mode',
        'origin_address',
        'origin_latitude',
        'origin_longitude',
        'total_distance_km',
        'estimated_duration_minutes',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cancellation_reason',
        'failure_reason',
        'admin_override_notes',
    ];

    protected function casts(): array
    {
        return [
            'origin_latitude' => 'decimal:7',
            'origin_longitude' => 'decimal:7',
            'total_distance_km' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(DeliveryStop::class, 'delivery_order_id')->orderBy('stop_sequence');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_order_id');
    }

    public function proofOfDelivery(): HasOne
    {
        return $this->hasOne(ProofOfDelivery::class, 'delivery_order_id');
    }

    public function proofsOfDelivery(): HasMany
    {
        return $this->hasMany(ProofOfDelivery::class, 'delivery_order_id');
    }

    public function locationEvents(): HasMany
    {
        return $this->hasMany(LocationEvent::class, 'delivery_order_id')->orderBy('recorded_at', 'asc');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'delivery_order_id')->orderBy('created_at', 'asc');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'ASSIGNED' => 'bg-indigo-500/15 text-indigo-400 border border-indigo-500/30',
            'ACCEPTED' => 'bg-blue-500/15 text-blue-400 border border-blue-500/30',
            'EN_ROUTE' => 'bg-amber-500/15 text-amber-400 border border-amber-500/30',
            'ARRIVED' => 'bg-teal-500/15 text-teal-400 border border-teal-500/30',
            'UNLOADING' => 'bg-purple-500/15 text-purple-400 border border-purple-500/30',
            'DELIVERED' => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
            'FAILED' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
            'CANCELLED' => 'bg-slate-500/15 text-slate-400 border border-slate-500/30',
            default => 'bg-slate-500/15 text-slate-400 border border-slate-500/30',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-600/20 text-rose-400 border border-rose-600/40',
            'high' => 'bg-orange-500/20 text-orange-400 border border-orange-500/40',
            'medium' => 'bg-sky-500/20 text-sky-400 border border-sky-500/40',
            'low' => 'bg-slate-500/20 text-slate-400 border border-slate-500/40',
            default => 'bg-slate-500/20 text-slate-400 border border-slate-500/40',
        };
    }
}
