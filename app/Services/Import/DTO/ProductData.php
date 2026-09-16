<?php

namespace App\Services\Import\DTO;

class ProductData
{
    public string $name;

    /**
     * @param VariantData[] $variants
     * @param string[] $images
     */
    public function __construct(
        string $name = '',
        public int $price = 0,
        public ?string $externalId = null,
        public ?string $sku = null,
        public ?string $categoryName = null,
        public ?string $categoryExternalId = null,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?string $composition = null,
        public int $stock = 10,
        public int $weight = 250,
        public ?string $thumbnail = null,
        public array $images = [],
        public array $variants = [],
        public bool $hasVariants = false,
        public ?int $costPrice = null,
        public ?int $priceStrikethrough = null,
        public array $raw = [],
        ?string $title = null
    ) {
        $this->name = !empty($name) ? $name : ($title ?? '');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'external_id' => $this->externalId,
            'sku' => $this->sku,
            'category_name' => $this->categoryName,
            'category_external_id' => $this->categoryExternalId,
            'short_description' => $this->shortDescription,
            'description' => $this->description,
            'composition' => $this->composition,
            'stock' => $this->stock,
            'weight' => $this->weight,
            'thumbnail' => $this->thumbnail,
            'images' => $this->images,
            'variants' => array_map(fn($v) => $v instanceof VariantData ? $v->toArray() : $v, $this->variants),
            'has_variants' => $this->hasVariants,
            'cost_price' => $this->costPrice,
            'price_strikethrough' => $this->priceStrikethrough,
        ];
    }
}
