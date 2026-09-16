<?php

namespace App\Services\Import\DTO;

class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $externalId = null,
        public ?string $slug = null,
        public ?string $icon = null,
        public int $ord = 0
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'external_id' => $this->externalId,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'ord' => $this->ord,
        ];
    }
}
