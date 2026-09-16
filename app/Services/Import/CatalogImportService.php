<?php

namespace App\Services\Import;

use App\Models\CatalogImport;
use App\Models\CatalogImportItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\Import\DTO\ProductData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogImportService
{
    /**
     * Validate source.
     */
    public function validateSource(string $source, ?string $type = null): array
    {
        $importer = ImporterFactory::make($type, $source);
        return $importer->validateSource($source);
    }

    /**
     * Generate preview with duplicate analysis and data quality warnings.
     */
    public function preview(string $source, ?string $type = null, ?int $storeId = null): array
    {
        $importer = ImporterFactory::make($type, $source);
        $data = $importer->fetchAndNormalize($source);

        $storeId = $storeId ?? Store::defaultStore()?->id;
        $existingProducts = Product::where('store_id', $storeId)->get();

        $existingBySku = $existingProducts->whereNotNull('sku')->keyBy('sku');
        $existingByName = $existingProducts->keyBy(fn($p) => strtolower(trim($p->name)));

        $previewItems = [];
        $willCreate = 0;
        $willUpdate = 0;
        $willSkip = 0;
        $payloadSkus = [];

        foreach ($data['products'] as $index => $product) {
            /** @var ProductData $product */
            $warnings = [];
            $matchedExisting = null;

            // Check duplicate SKU in payload
            if ($product->sku) {
                if (in_array($product->sku, $payloadSkus, true)) {
                    $warnings[] = "Duplikasi SKU '{$product->sku}' di dalam file/sumber impor.";
                } else {
                    $payloadSkus[] = $product->sku;
                }
            }

            // Check against existing database
            if ($product->sku && isset($existingBySku[$product->sku])) {
                $matchedExisting = $existingBySku[$product->sku];
            } elseif (isset($existingByName[strtolower(trim($product->name))])) {
                $matchedExisting = $existingByName[strtolower(trim($product->name))];
            }

            // Price validation warning
            if ($product->price <= 0) {
                $warnings[] = "Harga produk Rp 0 atau tidak valid.";
            }

            // Status prediction based on duplicate
            $predictedAction = $matchedExisting ? 'duplicate' : 'create';
            if ($predictedAction === 'create') {
                $willCreate++;
            } else {
                $willUpdate++; // or skip depending on strategy
            }

            $previewItems[] = [
                'index' => $index,
                'name' => $product->name,
                'price' => $product->price,
                'sku' => $product->sku,
                'category_name' => $product->categoryName,
                'stock' => $product->stock,
                'weight' => $product->weight,
                'variants_count' => count($product->variants),
                'has_variants' => $product->hasVariants,
                'images_count' => count($product->images),
                'thumbnail' => $product->thumbnail,
                'is_duplicate' => (bool) $matchedExisting,
                'matched_id' => $matchedExisting?->id,
                'warnings' => $warnings,
                'selected' => true,
            ];
        }

        return [
            'store' => $data['store']?->toArray(),
            'categories_count' => count($data['categories']),
            'categories' => array_map(fn($c) => $c->toArray(), $data['categories']),
            'total_items' => count($data['products']),
            'will_create' => $willCreate,
            'will_duplicate' => count($previewItems) - $willCreate,
            'items' => $previewItems,
        ];
    }

    /**
     * Analyze duplicates between incoming products and existing products in a store.
     */
    public function analyzeDuplicates(int $storeId, array $products): array
    {
        $existingProducts = Product::where('store_id', $storeId)->get();
        $existingBySku = $existingProducts->whereNotNull('sku')->keyBy('sku');
        $existingByName = $existingProducts->keyBy(fn($p) => strtolower(trim($p->name)));

        $duplicateItems = [];
        $newItems = [];

        foreach ($products as $product) {
            $matched = null;
            $matchType = null;

            if ($product->sku && isset($existingBySku[$product->sku])) {
                $matched = $existingBySku[$product->sku];
                $matchType = 'sku';
            } elseif (isset($existingByName[strtolower(trim($product->name))])) {
                $matched = $existingByName[strtolower(trim($product->name))];
                $matchType = 'name';
            }

            if ($matched) {
                $duplicateItems[] = [
                    'product' => $product,
                    'existing' => $matched,
                    'match_type' => $matchType,
                ];
            } else {
                $newItems[] = $product;
            }
        }

        return [
            'total_incoming' => count($products),
            'duplicates_count' => count($duplicateItems),
            'new_count' => count($newItems),
            'duplicate_items' => $duplicateItems,
            'new_items' => $newItems,
        ];
    }

    /**
     * Execute import with in-memory product data.
     */
    public function execute(
        int $storeId,
        string $sourceType,
        array $products,
        string $duplicateStrategy = 'skip',
        ?int $userId = null
    ): CatalogImport {
        $import = CatalogImport::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'source_type' => $sourceType,
            'status' => 'completed',
            'total_found' => count($products),
            'imported_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
        ]);

        $existingProducts = Product::where('store_id', $storeId)->get();
        $existingBySku = $existingProducts->whereNotNull('sku')->keyBy('sku');
        $existingByName = $existingProducts->keyBy(fn($p) => strtolower(trim($p->name)));

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($products as $prodData) {
            $existing = null;
            if ($prodData->sku && isset($existingBySku[$prodData->sku])) {
                $existing = $existingBySku[$prodData->sku];
            } elseif (isset($existingByName[strtolower(trim($prodData->name))])) {
                $existing = $existingByName[strtolower(trim($prodData->name))];
            }

            if ($existing) {
                if ($duplicateStrategy === 'skip') {
                    CatalogImportItem::create([
                        'catalog_import_id' => $import->id,
                        'external_id' => $prodData->externalId,
                        'sku' => $prodData->sku,
                        'name' => $prodData->name,
                        'price' => $prodData->price,
                        'action_taken' => 'skipped',
                        'status' => 'skipped',
                    ]);
                    $skippedCount++;
                    continue;
                }

                if ($duplicateStrategy === 'update') {
                    $existing->update([
                        'price' => $prodData->price,
                        'stock' => $prodData->stock,
                        'description' => $prodData->description ?? $existing->description,
                        'short_description' => $prodData->shortDescription ?? $existing->short_description,
                    ]);

                    CatalogImportItem::create([
                        'catalog_import_id' => $import->id,
                        'external_id' => $prodData->externalId,
                        'sku' => $prodData->sku,
                        'name' => $prodData->name,
                        'price' => $prodData->price,
                        'action_taken' => 'updated',
                        'status' => 'success',
                    ]);
                    $updatedCount++;
                    continue;
                }
            }

            $cat = null;
            if ($prodData->categoryName) {
                $cat = Category::firstOrCreate(
                    ['store_id' => $storeId, 'name' => $prodData->categoryName],
                    ['slug' => Str::slug($prodData->categoryName) . '-' . $storeId, 'status' => 'active']
                );
            }

            $uniqueSlug = Str::slug($prodData->name) . '-' . $storeId . '-' . Str::random(4);
            $newProduct = Product::create([
                'store_id' => $storeId,
                'category_id' => $cat?->id,
                'name' => $prodData->name,
                'slug' => $uniqueSlug,
                'price' => $prodData->price,
                'cost_price' => (int) ($prodData->price * 0.6),
                'short_description' => $prodData->shortDescription ?? $prodData->name,
                'description' => $prodData->description ?? $prodData->name,
                'stock' => $prodData->stock,
                'weight' => $prodData->weight ?? 250,
                'status' => 'active',
                'sku' => $prodData->sku,
                'sold' => 0,
            ]);

            if ($prodData->sku) {
                $existingBySku[$prodData->sku] = $newProduct;
            }
            $existingByName[strtolower(trim($prodData->name))] = $newProduct;

            CatalogImportItem::create([
                'catalog_import_id' => $import->id,
                'external_id' => $prodData->externalId,
                'sku' => $prodData->sku,
                'name' => $prodData->name,
                'price' => $prodData->price,
                'action_taken' => 'created',
                'status' => 'success',
            ]);
            $createdCount++;
        }

        $import->update([
            'imported_count' => $createdCount + $updatedCount,
            'skipped_count' => $skippedCount,
        ]);
        $import->items_created = $createdCount;
        $import->items_updated = $updatedCount;
        $import->items_skipped = $skippedCount;

        return $import;
    }

    /**
     * Execute the catalog import process.
     *
     * @param int $storeId
     * @param string $source
     * @param string|null $type
     * @param string $duplicateStrategy 'skip' | 'update' | 'create_new'
     * @param array $selectedIndexes Array of product indices to import (empty = import all)
     * @param int|null $userId
     * @return CatalogImport
     */
    public function executeImport(
        int $storeId,
        string $source,
        ?string $type = null,
        string $duplicateStrategy = 'skip',
        array $selectedIndexes = [],
        ?int $userId = null
    ): CatalogImport {
        $importer = ImporterFactory::make($type, $source);
        $data = $importer->fetchAndNormalize($source);

        $import = CatalogImport::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'source_type' => $type ?? 'auto',
            'source_url' => Str::limit($source, 500),
            'status' => 'processing',
            'total_found' => count($data['products']),
            'imported_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
        ]);

        $store = Store::find($storeId);

        // Optionally update store branding if current store lacks phone/whatsapp/city
        if ($store && $data['store']) {
            $updated = false;
            if (empty($store->phone) && !empty($data['store']->phone)) {
                $store->phone = $data['store']->phone;
                $updated = true;
            }
            if (empty($store->whatsapp) && !empty($data['store']->whatsapp)) {
                $store->whatsapp = $data['store']->whatsapp;
                $updated = true;
            }
            if (empty($store->city) && !empty($data['store']->city)) {
                $store->city = $data['store']->city;
                $store->province = $data['store']->province;
                $updated = true;
            }
            if ($updated) {
                $store->save();
            }
        }

        // Cache or create categories in this store
        $categoryMap = [];
        foreach ($data['categories'] as $catData) {
            $cat = Category::firstOrCreate(
                [
                    'store_id' => $storeId,
                    'name' => $catData->name,
                ],
                [
                    'slug' => Str::slug($catData->name) . '-' . $storeId,
                    'icon' => $catData->icon ?? 'cube',
                    'ord' => $catData->ord ?? 1,
                    'status' => 'active',
                ]
            );
            $categoryMap[$catData->name] = $cat->id;
            if ($catData->externalId) {
                $categoryMap[$catData->externalId] = $cat->id;
            }
        }

        $existingProducts = Product::where('store_id', $storeId)->get();
        $existingBySku = $existingProducts->whereNotNull('sku')->keyBy('sku');
        $existingByName = $existingProducts->keyBy(fn($p) => strtolower(trim($p->name)));

        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        $hasSelectionFilter = !empty($selectedIndexes);
        $selectedLookup = array_flip($selectedIndexes);

        foreach ($data['products'] as $index => $prodData) {
            /** @var ProductData $prodData */
            if ($hasSelectionFilter && !isset($selectedLookup[$index])) {
                continue;
            }

            try {
                // Find category
                $catId = $categoryMap[$prodData->categoryName]
                    ?? $categoryMap[$prodData->categoryExternalId ?? '']
                    ?? Category::where('store_id', $storeId)->first()?->id;

                // Duplicate match
                $existing = null;
                if ($prodData->sku && isset($existingBySku[$prodData->sku])) {
                    $existing = $existingBySku[$prodData->sku];
                } elseif (isset($existingByName[strtolower(trim($prodData->name))])) {
                    $existing = $existingByName[strtolower(trim($prodData->name))];
                }

                if ($existing) {
                    if ($duplicateStrategy === 'skip') {
                        CatalogImportItem::create([
                            'catalog_import_id' => $import->id,
                            'external_id' => $prodData->externalId,
                            'sku' => $prodData->sku,
                            'name' => $prodData->name,
                            'category_name' => $prodData->categoryName,
                            'price' => $prodData->price,
                            'action_taken' => 'skipped',
                            'status' => 'skipped',
                            'error_message' => 'Produk duplikat dilewati sesuai strategi.',
                            'raw_data' => $prodData->toArray(),
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    if ($duplicateStrategy === 'update') {
                        $existing->update([
                            'price' => $prodData->price,
                            'stock' => $prodData->stock,
                            'description' => $prodData->description ?? $existing->description,
                            'short_description' => $prodData->shortDescription ?? $existing->short_description,
                            'thumbnail' => $prodData->thumbnail ?? $existing->thumbnail,
                            'images' => !empty($prodData->images) ? $prodData->images : $existing->images,
                            'cost_price' => $prodData->costPrice ?? $existing->cost_price,
                        ]);

                        CatalogImportItem::create([
                            'catalog_import_id' => $import->id,
                            'external_id' => $prodData->externalId,
                            'sku' => $prodData->sku,
                            'name' => $prodData->name,
                            'category_name' => $prodData->categoryName,
                            'price' => $prodData->price,
                            'action_taken' => 'updated',
                            'status' => 'success',
                            'raw_data' => $prodData->toArray(),
                        ]);
                        $importedCount++;
                        continue;
                    }
                }

                // Strategy 'create_new' or new product
                $baseSlug = Str::slug($prodData->name);
                $uniqueSlug = $baseSlug . '-' . $storeId . '-' . Str::random(4);

                $newProduct = Product::create([
                    'store_id' => $storeId,
                    'category_id' => $catId,
                    'name' => $prodData->name,
                    'slug' => $uniqueSlug,
                    'price' => $prodData->price,
                    'cost_price' => $prodData->costPrice ?? (int) ($prodData->price * 0.6),
                    'price_strikethrough' => $prodData->priceStrikethrough,
                    'short_description' => $prodData->shortDescription,
                    'description' => $prodData->description,
                    'composition' => $prodData->composition,
                    'stock' => $prodData->stock,
                    'weight' => $prodData->weight,
                    'status' => 'active',
                    'has_variants' => $prodData->hasVariants,
                    'thumbnail' => $prodData->thumbnail,
                    'images' => $prodData->images,
                ]);

                // Update lookup
                if ($prodData->sku) {
                    $existingBySku[$prodData->sku] = $newProduct;
                }
                $existingByName[strtolower(trim($prodData->name))] = $newProduct;

                CatalogImportItem::create([
                    'catalog_import_id' => $import->id,
                    'external_id' => $prodData->externalId,
                    'sku' => $prodData->sku,
                    'name' => $prodData->name,
                    'category_name' => $prodData->categoryName,
                    'price' => $prodData->price,
                    'action_taken' => 'created',
                    'status' => 'success',
                    'raw_data' => $prodData->toArray(),
                ]);
                $importedCount++;
            } catch (\Throwable $e) {
                CatalogImportItem::create([
                    'catalog_import_id' => $import->id,
                    'external_id' => $prodData->externalId,
                    'sku' => $prodData->sku,
                    'name' => $prodData->name,
                    'category_name' => $prodData->categoryName,
                    'price' => $prodData->price,
                    'action_taken' => 'failed',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'raw_data' => $prodData->toArray(),
                ]);
                $failedCount++;
            }
        }

        $finalStatus = 'completed';
        if ($failedCount > 0 && $importedCount === 0) {
            $finalStatus = 'failed';
        } elseif ($failedCount > 0) {
            $finalStatus = 'partial';
        }

        $import->update([
            'status' => $finalStatus,
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
            'failed_count' => $failedCount,
            'summary' => [
                'strategy' => $duplicateStrategy,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
            ],
        ]);

        return $import;
    }
}
