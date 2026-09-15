<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'inventory_item_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
