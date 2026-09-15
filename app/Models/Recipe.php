<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'name',
        'additional_cost',
        'packaging_cost',
        'total_material_cost',
        'total_hpp',
        'notes',
    ];

    protected $casts = [
        'additional_cost' => 'integer',
        'packaging_cost' => 'integer',
        'total_material_cost' => 'integer',
        'total_hpp' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function recalculateHpp(?int $userId = null, string $source = 'recipe'): int
    {
        $this->load('items.inventoryItem');
        $materialCost = 0;
        foreach ($this->items as $item) {
            $currentUnitCost = $item->inventoryItem ? (int) $item->inventoryItem->cost_per_unit : (int) $item->cost_per_unit;
            $subtotal = (int) round((float) $item->quantity * $currentUnitCost);
            $item->update([
                'cost_per_unit' => $currentUnitCost,
                'subtotal' => $subtotal,
            ]);
            $materialCost += $subtotal;
        }

        $totalHpp = $materialCost + (int) $this->packaging_cost + (int) $this->additional_cost;

        $this->update([
            'total_material_cost' => $materialCost,
            'total_hpp' => $totalHpp,
        ]);

        if ($this->product_variant_id) {
            ProductVariant::where('id', $this->product_variant_id)->update(['cost_price' => $totalHpp]);
        } else {
            Product::where('id', $this->product_id)->update(['cost_price' => $totalHpp]);
        }

        HppHistory::create([
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'cost_price' => $totalHpp,
            'material_cost' => $materialCost,
            'packaging_cost' => (int) $this->packaging_cost,
            'additional_cost' => (int) $this->additional_cost,
            'source' => $source,
            'notes' => 'Recalculated from recipe: ' . $this->name,
            'created_by' => $userId,
        ]);

        return $totalHpp;
    }
}
