<?php

namespace Database\Seeders;

use App\Enums\TaskSubstitutionStatus;
use App\Enums\WeeklyTaskOwnerRole;
use App\Models\Member;
use App\Models\MemberTaskSkill;
use App\Models\TaskSubstitution;
use App\Models\Vacation;
use App\Models\WeeklyTask;
use App\Models\WeeklyTaskOwner;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = collect([
            '佐藤 太郎',
            '鈴木 花子',
            '高橋 健',
            '伊藤 美咲',
            '中村 翔',
        ])->mapWithKeys(
            fn (string $name): array => [
                $name => Member::query()->updateOrCreate(['name' => $name]),
            ]
        );

        $weeklyTasks = collect([
            [
                'key' => 'line_open_check',
                'name' => '開通確認',
                'description' => '開通処理のチェックと報告',
                'weekday' => 1,
                'start_time' => '09:30:00',
            ],
            [
                'key' => 'weekly_schedule_sync',
                'name' => '週次予定同期',
                'description' => '週次予定の更新差分を確認して共有',
                'weekday' => 1,
                'start_time' => '15:00:00',
            ],
            [
                'key' => 'invoice_check',
                'name' => '請求確認',
                'description' => '請求データの整合性確認',
                'weekday' => 2,
                'start_time' => '14:00:00',
            ],
            [
                'key' => 'payment_error_review',
                'name' => '入金エラー確認',
                'description' => '入金エラー一覧を確認して一次対応を整理',
                'weekday' => 2,
                'start_time' => '17:00:00',
            ],
            [
                'key' => 'delivery_report',
                'name' => '納品報告',
                'description' => '納品実績を集計して共有',
                'weekday' => 3,
                'start_time' => '11:00:00',
            ],
            [
                'key' => 'doc_template_update',
                'name' => '帳票テンプレ更新',
                'description' => '帳票テンプレートの定型文を最新化',
                'weekday' => 3,
                'start_time' => '15:30:00',
            ],
            [
                'key' => 'ops_review',
                'name' => '運用レビュー',
                'description' => '運用課題の棚卸し',
                'weekday' => 4,
                'start_time' => '16:00:00',
            ],
            [
                'key' => 'knowledge_share',
                'name' => 'ナレッジ共有会',
                'description' => '問い合わせ事例と対応ノウハウを共有',
                'weekday' => 4,
                'start_time' => '10:30:00',
            ],
            [
                'key' => 'kpi_update',
                'name' => 'KPI更新',
                'description' => '週次KPIダッシュボード更新',
                'weekday' => 5,
                'start_time' => '10:00:00',
            ],
            [
                'key' => 'weekly_retrospective',
                'name' => '週次ふりかえり',
                'description' => '今週の改善点と次週アクションを整理',
                'weekday' => 5,
                'start_time' => '17:30:00',
            ],
        ])->mapWithKeys(
            fn (array $task): array => [
                $task['key'] => WeeklyTask::query()->updateOrCreate(
                    ['name' => $task['name']],
                    [
                        'description' => $task['description'],
                        'weekday' => $task['weekday'],
                        'start_time' => $task['start_time'],
                    ]
                ),
            ]
        );

        $ownerDefinitions = [
            ['task' => 'line_open_check', 'member' => '佐藤 太郎', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'line_open_check', 'member' => '鈴木 花子', 'role' => WeeklyTaskOwnerRole::Sub->value],
            ['task' => 'weekly_schedule_sync', 'member' => '中村 翔', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'invoice_check', 'member' => '高橋 健', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'invoice_check', 'member' => '中村 翔', 'role' => WeeklyTaskOwnerRole::Sub->value],
            ['task' => 'payment_error_review', 'member' => '鈴木 花子', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'delivery_report', 'member' => '伊藤 美咲', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'doc_template_update', 'member' => '佐藤 太郎', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'ops_review', 'member' => '鈴木 花子', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'knowledge_share', 'member' => '高橋 健', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'kpi_update', 'member' => '中村 翔', 'role' => WeeklyTaskOwnerRole::Main->value],
            ['task' => 'weekly_retrospective', 'member' => '伊藤 美咲', 'role' => WeeklyTaskOwnerRole::Main->value],
        ];

        foreach ($ownerDefinitions as $ownerDefinition) {
            WeeklyTaskOwner::query()->updateOrCreate(
                [
                    'weekly_task_id' => $weeklyTasks[$ownerDefinition['task']]->id,
                    'member_id' => $members[$ownerDefinition['member']]->id,
                ],
                ['role' => $ownerDefinition['role']]
            );
        }

        $skillDefinitions = [
            ['member' => '佐藤 太郎', 'task' => 'line_open_check', 'skill_level' => 3],
            ['member' => '鈴木 花子', 'task' => 'line_open_check', 'skill_level' => 2],
            ['member' => '高橋 健', 'task' => 'line_open_check', 'skill_level' => 2],
            ['member' => '中村 翔', 'task' => 'weekly_schedule_sync', 'skill_level' => 3],
            ['member' => '伊藤 美咲', 'task' => 'weekly_schedule_sync', 'skill_level' => 2],
            ['member' => '高橋 健', 'task' => 'invoice_check', 'skill_level' => 3],
            ['member' => '中村 翔', 'task' => 'invoice_check', 'skill_level' => 2],
            ['member' => '鈴木 花子', 'task' => 'payment_error_review', 'skill_level' => 3],
            ['member' => '中村 翔', 'task' => 'payment_error_review', 'skill_level' => 2],
            ['member' => '伊藤 美咲', 'task' => 'delivery_report', 'skill_level' => 3],
            ['member' => '鈴木 花子', 'task' => 'delivery_report', 'skill_level' => 2],
            ['member' => '佐藤 太郎', 'task' => 'doc_template_update', 'skill_level' => 3],
            ['member' => '伊藤 美咲', 'task' => 'doc_template_update', 'skill_level' => 2],
            ['member' => '鈴木 花子', 'task' => 'ops_review', 'skill_level' => 3],
            ['member' => '佐藤 太郎', 'task' => 'ops_review', 'skill_level' => 2],
            ['member' => '高橋 健', 'task' => 'knowledge_share', 'skill_level' => 3],
            ['member' => '鈴木 花子', 'task' => 'knowledge_share', 'skill_level' => 2],
            ['member' => '中村 翔', 'task' => 'kpi_update', 'skill_level' => 3],
            ['member' => '伊藤 美咲', 'task' => 'kpi_update', 'skill_level' => 2],
            ['member' => '伊藤 美咲', 'task' => 'weekly_retrospective', 'skill_level' => 3],
            ['member' => '高橋 健', 'task' => 'weekly_retrospective', 'skill_level' => 2],
        ];

        foreach ($skillDefinitions as $skillDefinition) {
            MemberTaskSkill::query()->updateOrCreate(
                [
                    'member_id' => $members[$skillDefinition['member']]->id,
                    'weekly_task_id' => $weeklyTasks[$skillDefinition['task']]->id,
                ],
                ['skill_level' => $skillDefinition['skill_level']]
            );
        }

        $vacations = collect([
            ['key' => 'sato_2026_04_27', 'member' => '佐藤 太郎', 'vacation_date' => '2026-04-27'],
            ['key' => 'takahashi_2026_04_28', 'member' => '高橋 健', 'vacation_date' => '2026-04-28'],
            ['key' => 'suzuki_2026_05_01', 'member' => '鈴木 花子', 'vacation_date' => '2026-05-01'],
        ])->mapWithKeys(
            fn (array $vacation): array => [
                $vacation['key'] => Vacation::query()->updateOrCreate(
                    [
                        'member_id' => $members[$vacation['member']]->id,
                        'vacation_date' => $vacation['vacation_date'],
                    ]
                ),
            ]
        );

        TaskSubstitution::query()->updateOrCreate(
            [
                'vacation_id' => $vacations['sato_2026_04_27']->id,
                'weekly_task_id' => $weeklyTasks['line_open_check']->id,
            ],
            [
                'original_member_id' => $members['佐藤 太郎']->id,
                'substitute_member_id' => $members['鈴木 花子']->id,
                'status' => TaskSubstitutionStatus::Assigned->value,
            ]
        );

        TaskSubstitution::query()->updateOrCreate(
            [
                'vacation_id' => $vacations['takahashi_2026_04_28']->id,
                'weekly_task_id' => $weeklyTasks['invoice_check']->id,
            ],
            [
                'original_member_id' => $members['高橋 健']->id,
                'substitute_member_id' => $members['中村 翔']->id,
                'status' => TaskSubstitutionStatus::Pending->value,
            ]
        );
    }
}
