<?php

namespace Tests\Feature;

use App\Enums\TaskSubstitutionStatus;
use App\Enums\WeeklyTaskOwnerRole;
use App\Models\Member;
use App\Models\MemberTaskSkill;
use App\Models\TaskSubstitution;
use App\Models\Vacation;
use App\Models\WeeklyTask;
use App\Models\WeeklyTaskOwner;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaModelsTest extends TestCase
{
    use LazilyRefreshDatabase;

    // Issue #2 で追加した6テーブルがマイグレーション後に存在することを確認する。
    public function test_it_has_issue_2_tables(): void
    {
        self::assertTrue(Schema::hasTable('members'));
        self::assertTrue(Schema::hasTable('weekly_tasks'));
        self::assertTrue(Schema::hasTable('member_task_skills'));
        self::assertTrue(Schema::hasTable('vacations'));
        self::assertTrue(Schema::hasTable('weekly_task_owners'));
        self::assertTrue(Schema::hasTable('task_substitutions'));
    }

    // メンバー×週次業務のスキルは1件のみ登録でき、重複登録は拒否されることを確認する。
    public function test_member_task_skills_requires_unique_member_and_weekly_task(): void
    {
        $member = Member::factory()->create();
        $weeklyTask = WeeklyTask::factory()->create();

        MemberTaskSkill::factory()->create([
            'member_id' => $member->id,
            'weekly_task_id' => $weeklyTask->id,
        ]);

        $this->expectException(QueryException::class);

        MemberTaskSkill::factory()->create([
            'member_id' => $member->id,
            'weekly_task_id' => $weeklyTask->id,
        ]);
    }

    // 休暇×週次業務の振替は1件のみ登録でき、重複登録は拒否されることを確認する。
    public function test_task_substitutions_requires_unique_vacation_and_weekly_task(): void
    {
        $member = Member::factory()->create();
        $substituteMember = Member::factory()->create();
        $weeklyTask = WeeklyTask::factory()->create();
        $vacation = Vacation::factory()->create(['member_id' => $member->id]);

        TaskSubstitution::factory()->create([
            'vacation_id' => $vacation->id,
            'weekly_task_id' => $weeklyTask->id,
            'original_member_id' => $member->id,
            'substitute_member_id' => $substituteMember->id,
            'status' => TaskSubstitutionStatus::Pending->value,
        ]);

        $this->expectException(QueryException::class);

        TaskSubstitution::factory()->create([
            'vacation_id' => $vacation->id,
            'weekly_task_id' => $weeklyTask->id,
            'original_member_id' => $member->id,
            'substitute_member_id' => $substituteMember->id,
            'status' => TaskSubstitutionStatus::Assigned->value,
        ]);
    }

    // 休暇テーブルの member_id が members を参照する外部キー制約を持つことを確認する。
    public function test_vacations_member_id_enforces_foreign_key_constraint(): void
    {
        $this->expectException(QueryException::class);

        DB::table('vacations')->insert([
            'member_id' => 999999,
            'vacation_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // task_substitutions.status が文字列ではなく Enum として取得されることを確認する。
    public function test_task_substitution_status_is_casted_to_enum(): void
    {
        $member = Member::factory()->create();
        $substituteMember = Member::factory()->create();
        $weeklyTask = WeeklyTask::factory()->create();
        $vacation = Vacation::factory()->create(['member_id' => $member->id]);

        $taskSubstitution = TaskSubstitution::factory()->create([
            'vacation_id' => $vacation->id,
            'weekly_task_id' => $weeklyTask->id,
            'original_member_id' => $member->id,
            'substitute_member_id' => $substituteMember->id,
            'status' => TaskSubstitutionStatus::Pending->value,
        ])->fresh();

        self::assertInstanceOf(TaskSubstitutionStatus::class, $taskSubstitution->status);
        self::assertSame(TaskSubstitutionStatus::Pending, $taskSubstitution->status);
    }

    // weekly_task_owners.role が文字列ではなく Enum として取得されることを確認する。
    public function test_weekly_task_owner_role_is_casted_to_enum(): void
    {
        $member = Member::factory()->create();
        $weeklyTask = WeeklyTask::factory()->create();

        $weeklyTaskOwner = WeeklyTaskOwner::factory()->create([
            'member_id' => $member->id,
            'weekly_task_id' => $weeklyTask->id,
            'role' => WeeklyTaskOwnerRole::Main->value,
        ])->fresh();

        self::assertInstanceOf(WeeklyTaskOwnerRole::class, $weeklyTaskOwner->role);
        self::assertSame(WeeklyTaskOwnerRole::Main, $weeklyTaskOwner->role);
    }
}
