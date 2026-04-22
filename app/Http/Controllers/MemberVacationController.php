<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemberVacationsRequest;
use App\Models\Member;
use App\Models\Vacation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MemberVacationController extends Controller
{
    public function __invoke(UpdateMemberVacationsRequest $request, Member $member): RedirectResponse
    {
        // フロントから渡された選択日（Y-m-d）を正規化し、昇順で扱う。
        $selectedVacationDates = collect($request->validated()['vacation_dates'] ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $today = now()->toDateString();

        // 今日以降の有給だけを同期対象として、削除と追加を一括で反映する。
        DB::transaction(function () use ($member, $selectedVacationDates, $today): void {
            $member->vacations()
                ->where('vacation_date', '>=', $today)
                ->whereNotIn('vacation_date', $selectedVacationDates->all())
                ->delete();

            // 選択済み日を member_id + vacation_date で冪等に保存する。
            foreach ($selectedVacationDates as $vacationDate) {
                Vacation::query()->updateOrCreate([
                    'member_id' => $member->id,
                    'vacation_date' => $vacationDate,
                ]);
            }
        });

        // 成功メッセージ向けに m/d 表記へ整形する。
        $formattedVacationDates = $selectedVacationDates
            ->map(fn (string $vacationDate): string => Carbon::createFromFormat('Y-m-d', $vacationDate)->format('n/j'))
            ->implode(', ');

        return to_route('weekly-board.index')->with('status', sprintf(
            '%s の有給日を更新しました（%s）',
            $member->name,
            $formattedVacationDates !== '' ? $formattedVacationDates : '有給予定なし'
        ));
    }
}
