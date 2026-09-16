<?php

namespace App\Jobs;

use App\Services\Import\CatalogImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCatalogImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $storeId,
        public string $source,
        public ?string $type = null,
        public string $duplicateStrategy = 'skip',
        public array $selectedIndexes = [],
        public ?int $userId = null
    ) {}

    public function handle(CatalogImportService $service): void
    {
        $service->executeImport(
            storeId: $this->storeId,
            source: $this->source,
            type: $this->type,
            duplicateStrategy: $this->duplicateStrategy,
            selectedIndexes: $this->selectedIndexes,
            userId: $this->userId
        );
    }
}
