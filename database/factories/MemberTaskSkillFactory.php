<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberTaskSkill;
use App\Models\WeeklyTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberTaskSkill>
 */
class MemberTaskSkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'weekly_task_id' => WeeklyTask::factory(),
            'skill_level' => fake()->numberBetween(0, 3),
        ];
    }
}
