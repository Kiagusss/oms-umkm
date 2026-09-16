<?php

namespace App\Services\Import\DTO;

class VariantData
{
    public function __construct(
        public string $name,
        public int $price,
        public ?string $sku = null,
        public int $stock = 10,
        public ?string $externalId = null,
        public ?string $optionName = null,
        public ?string $optionValue = null
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'external_id' => $this->externalId,
            'option_name' => $this->optionName,
            'option_value' => $this->optionValue,
        ];
    }
}
