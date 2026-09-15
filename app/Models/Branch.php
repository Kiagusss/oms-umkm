<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'operating_hours',
        'manager_name',
        'status',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }
}
