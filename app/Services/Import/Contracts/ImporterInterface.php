<?php

namespace App\Services\Import\Contracts;

use App\Services\Import\DTO\CategoryData;
use App\Services\Import\DTO\ProductData;
use App\Services\Import\DTO\StoreData;

interface ImporterInterface
{
    /**
     * Validate the given source URL, path, or payload.
     *
     * @param string $source
     * @return array{valid: bool, type: string, message: string, store: ?StoreData, items_count: int}
     */
    public function validateSource(string $source): array;

    /**
     * Fetch raw data and normalize into DTOs.
     *
     * @param string $source
     * @return array{store: ?StoreData, categories: CategoryData[], products: ProductData[]}
     */
    public function fetchAndNormalize(string $source): array;
}
