<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchInventory extends Model
{
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'minimum_stock',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'minimum_stock');
    }

    public function adjust(float $qtyChange, string $type = 'adjustment', mixed $referenceType = null, ?int $referenceId = null, ?string $notes = null, ?int $userId = null): StockMovement
    {
        if (is_int($referenceType) && $userId === null) {
            $userId = $referenceType;
            $referenceType = null;
        }

        $after = (float) $this->quantity + $qtyChange;
        $this->update(['quantity' => $after]);

        return StockMovement::create([
            'branch_id' => $this->branch_id,
            'inventory_item_id' => $this->inventory_item_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'type' => strtolower($type),
            'quantity' => $qtyChange,
            'balance_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }
}
