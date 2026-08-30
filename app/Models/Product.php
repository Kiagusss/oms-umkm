<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'price_strikethrough',
        'category_id',
        'short_description',
        'description',
        'composition',
        'stock',
        'weight',
        'status',
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
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}