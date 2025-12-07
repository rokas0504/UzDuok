<?php

declare(strict_types=1);

namespace Database\Factories\Tasks;

use App\Enums\TaskStatus;
use App\Models\Tasks\Task;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d'),
            'status' => $this->faker->randomElement(TaskStatus::values()),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'is_repeated' => false,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::COMPLETED->value,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::CANCELLED->value,
        ]);
    }
}