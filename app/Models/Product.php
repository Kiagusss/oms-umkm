<?php
namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, BelongsToStore;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'price',
        'cost_price',
        'price_strikethrough',
        'category_id',
        'short_description',
        'description',
        'composition',
        'stock',
        'weight',
        'status',
        'has_variants',
        'is_best_seller',
        'is_featured',
        'ord',
        'thumbnail',
        'images',
        'seo_title',
        'seo_description',
        'meta_keywords',
    ];

    protected $casts = [
        'images' => 'array',
        'is_best_seller' => 'boolean',
        'is_featured' => 'boolean',
        'has_variants' => 'boolean',
        'cost_price' => 'integer',
        'price' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class)->whereNull('product_variant_id');
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function branchInventories()
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function stockForBranch(?int $branchId): int
    {
        if (!$branchId) {
            return (int) $this->stock;
        }

        $inv = $this->branchInventories()->where('branch_id', $branchId)->whereNull('product_variant_id')->first();
        return $inv ? (int) $inv->quantity : (int) $this->stock;
    }
}