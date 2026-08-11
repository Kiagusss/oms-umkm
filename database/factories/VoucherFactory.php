<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('????')),
            'type' => 'percentage',
            'value' => 10,
            'min_order' => null,
            'max_uses' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true,
        ];
    }
}