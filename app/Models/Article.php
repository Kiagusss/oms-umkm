<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'title', 'slug', 'thumbnail', 'category', 'content', 'author', 'date',
        'seo_title', 'seo_description', 'meta_keywords', 'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
