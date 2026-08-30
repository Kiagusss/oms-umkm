<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['product_id', 'name', 'rating', 'body', 'approved'];

    protected $casts = ['approved' => 'boolean', 'rating' => 'integer'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved($q)
    {
        return $q->where('approved', true);
    }
}
