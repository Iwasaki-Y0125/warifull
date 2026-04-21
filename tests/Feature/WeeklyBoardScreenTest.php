<?php

namespace Tests\Feature;

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
    }

    public function test_weekly_board_top_screen_is_rendered_with_seeded_data(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('ワリフル');
        $response->assertSeeText('チームメンバー');
        $response->assertSeeText('開通確認');
        $response->assertSeeText('開始時刻: 09:30');
        $response->assertSeeText('佐藤 太郎');
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
}
