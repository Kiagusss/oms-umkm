<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    protected $table = 'seos';

    protected $fillable = [
        'site_name', 'site_url', 'default_title', 'default_description',
        'favicon', 'meta_title', 'meta_description', 'keywords',
        'og_image', 'canonical_url', 'robots', 'google_verification', 'schema_json_ld',
    ];
}