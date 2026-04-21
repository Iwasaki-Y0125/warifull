<?php

namespace Database\Factories;

use App\Enums\WeeklyTaskOwnerRole;
use App\Models\Member;
use App\Models\WeeklyTask;
use App\Models\WeeklyTaskOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyTaskOwner>
 */
class WeeklyTaskOwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weekly_task_id' => WeeklyTask::factory(),
            'member_id' => Member::factory(),
            'role' => fake()->randomElement([
                WeeklyTaskOwnerRole::Main->value,
                WeeklyTaskOwnerRole::Sub->value,
            ]),
        ];
    }
}
