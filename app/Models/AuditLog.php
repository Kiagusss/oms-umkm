<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, Model $model, ?array $old = null, ?array $new = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public static function log($user, string $action, string $description, ?Model $model = null): self
    {
        $userId = is_numeric($user) ? $user : ($user?->id ?? auth()->id());
        $auditableType = $model ? get_class($model) : ($user && is_object($user) ? get_class($user) : 'system');
        $auditableId = $model ? $model->getKey() : ($userId ?? null);

        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'new_values' => ['description' => $description],
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
