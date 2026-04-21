<?php

namespace Database\Factories;

use App\Models\WeeklyTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyTask>
 */
class WeeklyTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'weekday' => fake()->numberBetween(1, 5),
            'start_time' => fake()->time('H:i:s'),
        ];
    }
}
