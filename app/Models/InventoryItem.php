<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'item_type',
        'unit',
        'cost_per_unit',
        'minimum_stock',
        'status',
        'notes',
    ];

    protected $casts = [
        'cost_per_unit' => 'integer',
        'minimum_stock' => 'decimal:2',
    ];

    protected $appends = [
        'unit_cost',
    ];

    public function getUnitCostAttribute(): int
    {
        return (int) ($this->cost_per_unit ?? 0);
    }

    public function branchInventories(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockForBranch(?int $branchId): float
    {
        if (!$branchId) {
            return (float) $this->branchInventories()->sum('quantity');
        }

        $inv = $this->branchInventories()->where('branch_id', $branchId)->first();
        return $inv ? (float) $inv->quantity : 0.0;
    }

    public function isLowStock(?int $branchId = null): bool
    {
        $current = $this->stockForBranch($branchId);
        return $current <= (float) $this->minimum_stock;
    }
}
