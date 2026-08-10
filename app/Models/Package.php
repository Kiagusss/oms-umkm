<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name', 'description', 'items', 'price', 'original_price',
        'badge', 'is_featured', 'ord', 'status',
    ];

    protected $casts = [
        'items' => 'array',
        'is_featured' => 'boolean',
    ];
}
