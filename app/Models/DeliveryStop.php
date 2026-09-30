<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'stop_sequence',
        'customer_name',
        'customer_phone',
        'address',
        'latitude',
        'longitude',
        'status',
        'arrived_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'stop_sequence' => 'integer',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_stop_id');
    }

    public function proofOfDelivery(): HasOne
    {
        return $this->hasOne(ProofOfDelivery::class, 'delivery_stop_id');
    }
}
