<?php
namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name'               => $name,
            'slug'               => str($name)->slug()->toString(),
            'category_id'        => Category::factory(),
            'price'              => fake()->numberBetween(5000, 50000),
            'price_strikethrough'=> null,
            'short_description'  => fake()->sentence(),
            'description'        => fake()->paragraph(),
            'composition'        => null,
            'stock'              => fake()->numberBetween(10, 100),
            'weight'             => fake()->numberBetween(100, 1000),
            'thumbnail'          => null,
            'images'             => null,
            'is_best_seller'     => false,
            'is_featured'        => false,
            'ord'                => fake()->numberBetween(0, 100),
            'status'             => 'active',
        ];
    }
}