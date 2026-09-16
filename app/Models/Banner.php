<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'title', 'subtitle', 'button_text', 'button_link', 'background_image', 'status',
    ];
}
