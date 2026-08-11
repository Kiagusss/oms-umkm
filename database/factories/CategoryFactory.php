<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name'   => $name,
            'slug'   => str($name)->slug()->toString(),
            'icon'   => null,
            'ord'    => fake()->numberBetween(0, 100),
            'status' => 'active',
        ];
    }
}
