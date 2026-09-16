<?php

namespace App\Services\Tenant;

use App\Models\Store;

class TenantContext
{
    protected static ?int $storeId = null;
    protected static ?Store $store = null;

    public static function setStore(?Store $store): void
    {
        self::$store = $store;
        self::$storeId = $store?->id;
        if (session()->isStarted()) {
            session(['selected_store_id' => self::$storeId]);
        }
    }

    public static function setStoreId(?int $storeId): void
    {
        self::$storeId = $storeId;
        self::$store = $storeId ? Store::find($storeId) : null;
        if (session()->isStarted()) {
            session(['selected_store_id' => $storeId]);
        }
    }

    public static function getStoreId(): ?int
    {
        if (self::$storeId !== null) {
            return self::$storeId;
        }

        if (session()->isStarted() && session()->has('selected_store_id')) {
            self::$storeId = (int) session('selected_store_id');
            return self::$storeId;
        }

        // Fallback to default active store
        $default = Store::defaultStore();
        if ($default) {
            self::$storeId = $default->id;
            self::$store = $default;
            return self::$storeId;
        }

        return null;
    }

    public static function getStore(): ?Store
    {
        $id = self::getStoreId();
        if (!$id) {
            return null;
        }

        if (self::$store && self::$store->id === $id) {
            return self::$store;
        }

        self::$store = Store::find($id);
        return self::$store;
    }

    public static function clear(): void
    {
        self::$storeId = null;
        self::$store = null;
        if (session()->isStarted()) {
            session()->forget('selected_store_id');
        }
    }

    public static function reset(): void
    {
        self::clear();
    }
}
