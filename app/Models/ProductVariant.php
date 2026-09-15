<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'cost_price',
        'stock',
        'barcode',
        'attributes',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
        'cost_price' => 'integer',
        'stock' => 'integer',
        'attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function stockForBranch(?int $branchId): int
    {
        if (!$branchId) {
            return (int) $this->stock;
        }

        $inv = $this->inventories()->where('branch_id', $branchId)->first();
        return $inv ? (int) $inv->quantity : (int) $this->stock;
    }
}
