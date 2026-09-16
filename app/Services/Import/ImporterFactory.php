<?php

namespace App\Services\Import;

use App\Services\Import\Contracts\ImporterInterface;
use App\Services\Import\Importers\CsvImporter;
use App\Services\Import\Importers\JsonImporter;
use App\Services\Import\Importers\MarketplaceImporter;
use App\Services\Import\Importers\MockImporter;
use InvalidArgumentException;

class ImporterFactory
{
    /**
     * Resolve the appropriate importer based on explicit type or source signature.
     */
    public static function make(?string $type = null, ?string $source = null): ImporterInterface
    {
        if ($type) {
            return match (strtolower($type)) {
                'mock' => new MockImporter(),
                'csv' => new CsvImporter(),
                'json' => new JsonImporter(),
                'marketplace' => new MarketplaceImporter(),
                default => throw new InvalidArgumentException("Tipe import '{$type}' tidak didukung."),
            };
        }

        if ($source) {
            $trimmed = trim($source);
            $lower = strtolower($trimmed);

            if (str_contains($lower, 'demo.import') || str_contains($lower, 'mock') || str_contains($lower, 'pempek-pak-agus')) {
                return new MockImporter();
            }

            if (str_ends_with($lower, '.csv') || (!str_starts_with($lower, 'http') && str_contains($trimmed, ',') && str_contains($trimmed, "\n"))) {
                return new CsvImporter();
            }

            if (str_ends_with($lower, '.json') || str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                return new JsonImporter();
            }

            if (
                str_starts_with($lower, 'http://') ||
                str_starts_with($lower, 'https://') ||
                str_starts_with($lower, 'www.') ||
                str_contains($lower, 'shopee.') ||
                str_contains($lower, 'shp.ee') ||
                str_contains($lower, 'tokopedia.') ||
                str_contains($lower, 'lazada.') ||
                str_contains($lower, 'blibli.') ||
                str_contains($lower, 'bukalapak.') ||
                str_contains($lower, 'tiktok.com')
            ) {
                return new MarketplaceImporter();
            }
        }

        // Default fallback
        return new MockImporter();
    }
}
