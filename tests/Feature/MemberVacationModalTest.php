<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Vacation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MemberVacationModalTest extends TestCase
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
     * 今日以降の有給日を同期更新し、過去日の有給は保持されることを確認する。
     */
    public function test_it_syncs_future_vacations_and_keeps_past_dates(): void
    {
        $member = Member::factory()->create(['name' => '佐藤 太郎']);

        Vacation::factory()->create([
            'member_id' => $member->id,
            'vacation_date' => '2026-04-20',
        ]);
        Vacation::factory()->create([
            'member_id' => $member->id,
            'vacation_date' => '2026-04-25',
        ]);
        Vacation::factory()->create([
            'member_id' => $member->id,
            'vacation_date' => '2026-04-28',
        ]);

        $response = $this->put(route('members.vacations.update', ['member' => $member->id]), [
            'vacation_dates' => ['2026-05-01', '2026-04-25'],
        ]);

        $response->assertRedirect(route('weekly-board.index'));
        $response->assertSessionHas('status', '佐藤 太郎 の有給日を更新しました（4/25, 5/1）');

        $this->assertDatabaseHas('vacations', [
            'member_id' => $member->id,
            'vacation_date' => '2026-04-20',
        ]);
        $this->assertDatabaseHas('vacations', [
            'member_id' => $member->id,
            'vacation_date' => '2026-04-25',
        ]);
        $this->assertDatabaseHas('vacations', [
            'member_id' => $member->id,
            'vacation_date' => '2026-05-01',
        ]);
        $this->assertDatabaseMissing('vacations', [
            'member_id' => $member->id,
            'vacation_date' => '2026-04-28',
        ]);
    }

    /**
     * 空配列送信で、今日以降の有給日を全解除できることを確認する。
     */
    public function test_it_can_clear_all_future_vacations(): void
    {
        $member = Member::factory()->create(['name' => '鈴木 花子']);

        Vacation::factory()->create([
            'member_id' => $member->id,
            'vacation_date' => '2026-04-20',
        ]);
        Vacation::factory()->create([
            'member_id' => $member->id,
            'vacation_date' => '2026-04-25',
        ]);

        $response = $this->put(route('members.vacations.update', ['member' => $member->id]), [
            'vacation_dates' => [],
        ]);

        $response->assertRedirect(route('weekly-board.index'));
        $response->assertSessionHas('status', '鈴木 花子 の有給日を更新しました（有給予定なし）');

        $this->assertDatabaseHas('vacations', [
            'member_id' => $member->id,
            'vacation_date' => '2026-04-20',
        ]);
        $this->assertDatabaseMissing('vacations', [
            'member_id' => $member->id,
            'vacation_date' => '2026-04-25',
        ]);
    }

    /**
     * 過去日を送信した場合にバリデーションエラーになることを確認する。
     */
    public function test_it_rejects_past_vacation_date(): void
    {
        $member = Member::factory()->create();

        $response = $this->from(route('weekly-board.index'))
            ->put(route('members.vacations.update', ['member' => $member->id]), [
                'vacation_dates' => ['2026-04-21'],
            ]);

        $response->assertRedirect(route('weekly-board.index'));
        $response->assertSessionHasErrors(['vacation_dates.0']);
    }

    /**
     * Y-m-d 以外の形式の日付がバリデーションで拒否されることを確認する。
     */
    public function test_it_rejects_invalid_date_format(): void
    {
        $member = Member::factory()->create();

        $response = $this->from(route('weekly-board.index'))
            ->put(route('members.vacations.update', ['member' => $member->id]), [
                'vacation_dates' => ['04/29/2026'],
            ]);

        $response->assertRedirect(route('weekly-board.index'));
        $response->assertSessionHasErrors(['vacation_dates.0']);
    }

    /**
     * 同一日付の重複送信が distinct ルールで拒否されることを確認する。
     */
    public function test_it_rejects_duplicate_vacation_dates(): void
    {
        $member = Member::factory()->create();

        $response = $this->from(route('weekly-board.index'))
            ->put(route('members.vacations.update', ['member' => $member->id]), [
                'vacation_dates' => ['2026-04-29', '2026-04-29'],
            ]);

        $response->assertRedirect(route('weekly-board.index'));
        $response->assertSessionHasErrors(['vacation_dates.1']);
    }
}
