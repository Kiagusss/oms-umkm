<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeItem extends Model
{
    protected $fillable = [
        'recipe_id',
        'inventory_item_id',
        'quantity',
        'unit',
        'cost_per_unit',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_per_unit' => 'integer',
        'subtotal' => 'integer',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
