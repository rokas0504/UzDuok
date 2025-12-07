<?php

declare(strict_types=1);

namespace Database\Factories\Roles;

use App\Models\Roles\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
        ];
    }

    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Parent',
            'slug' => 'parent',
            'description' => 'Parent role',
        ]);
    }

    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Child',
            'slug' => 'child',
            'description' => 'Child role',
        ]);
    }
}