<?php

namespace Tests\Feature;

use App\Models\WeeklyTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WeeklyBoardScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'middleware_auth.enabled' => false,
        ]);

        $this->travelTo(Carbon::parse('2026-04-22'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    /**
     * トップ画面で、当日選択に対応するタスク情報が表示されることを確認する。
     */
    public function test_weekly_board_top_screen_is_rendered_with_seeded_data(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('ワリフル');
        $response->assertSeeText('チームメンバー');
        $response->assertSeeText('納品報告');
        $response->assertSeeText('開始時刻: 11:00');
        $response->assertSeeText('伊藤 美咲');
    }

    public function test_weekly_board_displays_monday_to_friday_tabs_for_current_week(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeTextInOrder(['月', '火', '水', '木', '金']);

        $weekStart = Carbon::today()->startOfWeek(Carbon::MONDAY);

        foreach (range(0, 4) as $offset) {
            $response->assertSeeText($weekStart->copy()->addDays($offset)->format('n/j'));
        }
    }

    /**
     * dateクエリ指定時に、選択日の曜日タスクのみへ絞り込まれることを確認する。
     */
    public function test_weekly_board_filters_tasks_by_selected_date_query(): void
    {
        $this->seed();

        $response = $this->get('/?date=2026-04-21');

        $response->assertOk();
        $response->assertSeeText('請求確認');
        $response->assertSeeText('入金エラー確認');
        $response->assertDontSeeText('開通確認');
        $response->assertDontSeeText('週次予定同期');
    }

    /**
     * 不正なdateクエリが渡された場合は安全なデフォルト日にフォールバックすることを確認する。
     */
    public function test_weekly_board_falls_back_to_default_date_when_date_query_is_invalid(): void
    {
        $this->seed();

        $response = $this->get('/?date=invalid-date');

        $response->assertOk();
        $response->assertSeeText('納品報告');
        $response->assertDontSeeText('請求確認');
    }

    /**
     * 選択日の曜日にタスクが存在しない場合、空状態メッセージを表示することを確認する。
     */
    public function test_weekly_board_shows_empty_state_when_selected_day_has_no_tasks(): void
    {
        $this->seed();

        WeeklyTask::query()->where('weekday', 3)->delete();

        $response = $this->get('/?date=2026-04-22');

        $response->assertOk();
        $response->assertSeeText('この日のタスクはありません。');
    }
}
