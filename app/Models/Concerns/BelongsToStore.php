<?php

namespace App\Models\Concerns;

use App\Models\Store;
use App\Services\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::creating(function ($model) {
            if (empty($model->store_id)) {
                $storeId = TenantContext::getStoreId() ?? Store::defaultStore()?->id;
                if ($storeId) {
                    $model->store_id = $storeId;
                }
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeForStore($query, ?int $storeId = null)
    {
        $id = $storeId ?? TenantContext::getStoreId();
        if ($id) {
            return $query->where('store_id', $id);
        }
        return $query;
    }
}
