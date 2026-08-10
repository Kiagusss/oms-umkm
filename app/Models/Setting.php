<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'logo', 'site_name', 'address', 'whatsapp', 'instagram', 'facebook', 'tiktok',
        'google_maps_embed', 'email', 'operating_hours', 'footer_text', 'about_us',
        'key', 'value',
    ];
}
