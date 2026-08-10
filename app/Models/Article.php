<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title', 'slug', 'thumbnail', 'category', 'content', 'author', 'date',
        'seo_title', 'seo_description', 'meta_keywords', 'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
