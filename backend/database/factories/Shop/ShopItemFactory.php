<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\ShopItem;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopItem>
 */
class ShopItemFactory extends Factory
{
    protected $model = ShopItem::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 200),
            'quantity' => $this->faker->numberBetween(1, 50),
            'icon' => null,
        ];
    }
}