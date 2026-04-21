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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoSeedDataTest extends TestCase
{
    use RefreshDatabase;

    // デモ用Seeder実行後に、Issue #2 で定義した業務ドメインの主要テーブルへデータが投入されることを確認する。
    public function test_demo_seeder_populates_issue_2_domain_tables(): void
    {
        $this->seed();

        self::assertGreaterThan(0, Member::query()->count());
        self::assertGreaterThan(0, WeeklyTask::query()->count());
        self::assertGreaterThan(0, MemberTaskSkill::query()->count());
        self::assertGreaterThan(0, Vacation::query()->count());
        self::assertGreaterThan(0, WeeklyTaskOwner::query()->count());
        self::assertGreaterThan(0, TaskSubstitution::query()->count());
    }

    // デモ表示で必要な通常担当（main）と、振替ステータス（pending/assigned）が含まれることを確認する。
    public function test_demo_seeder_includes_main_owner_and_assignment_statuses(): void
    {
        $this->seed();

        self::assertDatabaseHas('weekly_task_owners', [
            'role' => WeeklyTaskOwnerRole::Main->value,
        ]);
        self::assertDatabaseHas('task_substitutions', [
            'status' => TaskSubstitutionStatus::Pending->value,
        ]);
        self::assertDatabaseHas('task_substitutions', [
            'status' => TaskSubstitutionStatus::Assigned->value,
        ]);
    }

    // 週次表の見た目要件として、月〜金の各曜日にタスクが2〜3件あることを確認する。
    public function test_demo_seeder_has_two_or_three_tasks_for_each_weekday(): void
    {
        $this->seed();

        $taskCountsByWeekday = WeeklyTask::query()
            ->select('weekday', DB::raw('COUNT(*) as task_count'))
            ->groupBy('weekday')
            ->pluck('task_count', 'weekday');

        foreach (range(1, 5) as $weekday) {
            self::assertArrayHasKey($weekday, $taskCountsByWeekday->toArray());
            self::assertGreaterThanOrEqual(2, $taskCountsByWeekday[$weekday]);
            self::assertLessThanOrEqual(3, $taskCountsByWeekday[$weekday]);
        }
    }

    // Seederを繰り返し実行しても件数が増殖しない（冪等である）ことを確認する。
    public function test_demo_seeder_is_idempotent_for_repeated_runs(): void
    {
        $this->seed();
        $this->seed();

        self::assertSame(5, Member::query()->count());
        self::assertSame(10, WeeklyTask::query()->count());
        self::assertSame(3, Vacation::query()->count());
        self::assertSame(2, TaskSubstitution::query()->count());
    }
}
