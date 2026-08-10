<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['name', 'kombinasi', 'avatar', 'rating', 'comment', 'date', 'status', 'ord'];
}
