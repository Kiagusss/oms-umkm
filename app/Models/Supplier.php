<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'status',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
