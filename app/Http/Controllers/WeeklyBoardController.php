<?php

namespace App\Http\Controllers;

use App\Enums\WeeklyTaskOwnerRole;
use App\Models\Member;
use App\Models\WeeklyTask;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class WeeklyBoardController extends Controller
{
    /**
     * トップの週次ボード表示用データを組み立てて返す。
     */
    public function __invoke(): View
    {
        $today = now()->toDateString();
        $weekStart = Carbon::today()->startOfWeek(Carbon::MONDAY);

        // 上部タブ用に、今週の月〜金の日付を m/d 形式で作る。
        $weekdayTabs = collect(range(1, 5))
            ->mapWithKeys(function (int $weekday) use ($weekStart): array {
                $date = $weekStart->copy()->addDays($weekday - 1);

                return [
                    $weekday => [
                        'label' => match ($weekday) {
                            1 => '月',
                            2 => '火',
                            3 => '水',
                            4 => '木',
                            5 => '金',
                        },
                        'date' => $date->format('n/j'),
                    ],
                ];
            })
            ->all();

        $activeWeekday = Carbon::today()->dayOfWeekIso;
        if ($activeWeekday < 1 || $activeWeekday > 5) {
            $activeWeekday = 1;
        }

        // メンバー一覧を取得し、今日以降の有給予定を直近3件（m/d形式）へ整形する。
        $members = Member::query()
            ->with([
                'vacations' => function ($query): void {
                    $query->select(['id', 'member_id', 'vacation_date'])
                        ->orderBy('vacation_date');
                },
            ])
            ->orderBy('id')
            ->get()
            ->map(function (Member $member) use ($today): array {
                $upcomingVacationDates = $member->vacations
                    ->where('vacation_date', '>=', $today)
                    ->take(3)
                    ->map(
                        fn ($vacationDate): string => Carbon::parse($vacationDate->vacation_date)->format('n/j')
                    )
                    ->values()
                    ->all();

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'upcoming_vacation_dates' => $upcomingVacationDates,
                    'editable_vacation_dates' => $member->vacations
                        ->where('vacation_date', '>=', $today)
                        ->map(
                            fn ($vacationDate): string => Carbon::parse($vacationDate->vacation_date)->format('Y-m-d')
                        )
                        ->values()
                        ->all(),
                ];
            });

        // 週次タスクを曜日・開始時刻で並べて取得する。
        // 同時に担当者情報も先に取得し、タスクごとの追加クエリ（N+1）を防ぐ。
        $weeklyTasks = WeeklyTask::query()
            ->with([
                'owners.member',
            ])
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get()
            ->map(function (WeeklyTask $weeklyTask): array {
                // 表示用の担当者は main ロールを優先して採用する。
                $mainOwner = $weeklyTask->owners
                    ->firstWhere('role', WeeklyTaskOwnerRole::Main);

                return [
                    'id' => $weeklyTask->id,
                    'name' => $weeklyTask->name,
                    'description' => $weeklyTask->description,
                    'weekday' => (int) $weeklyTask->weekday,
                    'start_time' => $weeklyTask->start_time,
                    'main_owner_name' => $mainOwner?->member?->name,
                ];
            })
            ->groupBy('weekday');

        // Bladeで曜日ヘッダーと曜日別タスク一覧を描画するためのデータを渡す。
        return view('weekly_board.index', [
            'weekdayTabs' => $weekdayTabs,
            'activeWeekday' => $activeWeekday,
            'members' => $members,
            'weeklyTasksByWeekday' => $weeklyTasks,
        ]);
    }
}
