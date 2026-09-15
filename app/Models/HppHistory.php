<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HppHistory extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'cost_price',
        'material_cost',
        'packaging_cost',
        'additional_cost',
        'source',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'cost_price' => 'integer',
        'material_cost' => 'integer',
        'packaging_cost' => 'integer',
        'additional_cost' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
