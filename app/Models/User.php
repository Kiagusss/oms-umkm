<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Ai\Concerns\HasConversations;

class User extends Authenticatable
{
    use HasConversations, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'branch_id',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function hasRole(string|array $roleName): bool
    {
        if (is_array($roleName)) {
            return in_array($this->role?->name, $roleName, true);
        }

        return $this->role?->name === $roleName;
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner') || empty($this->role_id);
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function isWarehouse(): bool
    {
        return $this->hasRole('warehouse');
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return (bool) $this->role?->hasPermission($permissionName);
    }
}
