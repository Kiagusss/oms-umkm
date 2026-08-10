<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $table = 'gallery_items';

    protected $fillable = ['image', 'caption', 'ord'];

    protected $casts = [
        'images' => 'array',
    ];
}
