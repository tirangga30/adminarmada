<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofOfDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'delivery_stop_id',
        'driver_id',
        'receiver_name',
        'receiver_relation',
        'photo_path',
        'signature_data',
        'notes',
        'latitude',
        'longitude',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'delivered_at' => 'datetime',
        ];
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function deliveryStop(): BelongsTo
    {
        return $this->belongsTo(DeliveryStop::class, 'delivery_stop_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
