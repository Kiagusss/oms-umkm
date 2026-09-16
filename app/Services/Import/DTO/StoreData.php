<?php

namespace App\Services\Import\DTO;

class StoreData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $logo = null,
        public ?string $banner = null,
        public ?string $phone = null,
        public ?string $whatsapp = null,
        public ?string $city = null,
        public ?string $province = null,
        public ?string $address = null,
        public ?string $externalId = null,
        public array $raw = []
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'city' => $this->city,
            'province' => $this->province,
            'address' => $this->address,
            'external_id' => $this->externalId,
        ];
    }
}
