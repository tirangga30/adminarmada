<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'delivery_stop_id',
        'item_name',
        'quantity',
        'unit',
        'weight_kg',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'weight_kg' => 'decimal:2',
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
}
