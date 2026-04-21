<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\TaskSubstitution;
use App\Models\Vacation;
use App\Models\WeeklyTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskSubstitution>
 */
class TaskSubstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacation_id' => Vacation::factory(),
            'weekly_task_id' => WeeklyTask::factory(),
            'original_member_id' => Member::factory(),
            'substitute_member_id' => Member::factory(),
            'status' => fake()->randomElement(['pending', 'assigned']),
        ];
    }
}
