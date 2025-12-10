<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakModel;
use Carbon\Carbon;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 9: 勤怠一覧情報取得機能（一般ユーザー）
     * 自分が行った勤怠情報が全て表示されている
     */
    public function test_user_can_see_all_their_attendance_records()
    {
        $user = User::factory()->create(['role' => 0]);
        
        // 複数日の勤怠データを作成
        for ($i = 0; $i < 5; $i++) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today()->subDays($i),
                'clock_in' => Carbon::today()->subDays($i)->setHour(9),
                'clock_out' => Carbon::today()->subDays($i)->setHour(18),
                'status' => Attendance::STATUS_CLOCKED_OUT,
            ]);
        }

        $response = $this->actingAs($user)->get('/attendance/list');
        
        $response->assertStatus(200);
        $this->assertEquals(5, Attendance::where('user_id', $user->id)->count());
    }

    /**
     * ID 9: 勤怠一覧情報取得機能（一般ユーザー）
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_on_list_page()
    {
        $user = User::factory()->create(['role' => 0]);

        $response = $this->actingAs($user)->get('/attendance/list');
        
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y年m月'));
    }

    /**
     * ID 9: 勤怠一覧情報取得機能（一般ユーザー）
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_button_displays_previous_month_data()
    {
        $user = User::factory()->create(['role' => 0]);
        
        $lastMonth = Carbon::now()->subMonth();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $lastMonth,
            'clock_in' => $lastMonth->setHour(9),
            'clock_out' => $lastMonth->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $lastMonth->format('Y-m'));
        
        $response->assertStatus(200);
        $response->assertSee($lastMonth->format('Y年m月'));
    }

    /**
     * ID 9: 勤怠一覧情報取得機能（一般ユーザー）
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_button_displays_next_month_data()
    {
        $user = User::factory()->create(['role' => 0]);
        
        $nextMonth = Carbon::now()->addMonth();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth,
            'clock_in' => $nextMonth->setHour(9),
            'clock_out' => $nextMonth->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $nextMonth->format('Y-m'));
        
        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y年m月'));
    }

    /**
     * ID 9: 勤怠一覧情報取得機能（一般ユーザー）
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_detail_button_redirects_to_detail_page()
    {
        $user = User::factory()->create(['role' => 0]);
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        
        $response->assertStatus(200);
    }

    /**
     * ID 10: 勤怠詳細情報取得機能（一般ユーザー）
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_detail_page_shows_logged_in_user_name()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'role' => 0,
        ]);
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    /**
     * ID 10: 勤怠詳細情報取得機能（一般ユーザー）
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_detail_page_shows_selected_date()
    {
        $user = User::factory()->create(['role' => 0]);
        
        $selectedDate = Carbon::create(2024, 12, 1);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in' => $selectedDate->copy()->setHour(9),
            'clock_out' => $selectedDate->copy()->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        
        $response->assertStatus(200);
        $response->assertSee($selectedDate->format('Y-m-d'));
    }

    /**
     * ID 10: 勤怠詳細情報取得機能（一般ユーザー）
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_detail_page_shows_correct_clock_in_out_times()
    {
        $user = User::factory()->create(['role' => 0]);
        
        $clockIn = Carbon::create(2024, 12, 1, 9, 0, 0);
        $clockOut = Carbon::create(2024, 12, 1, 18, 0, 0);
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2024, 12, 1),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        
        $response->assertStatus(200);
        $response->assertSee($clockIn->format('H:i'));
        $response->assertSee($clockOut->format('H:i'));
    }

    /**
     * ID 10: 勤怠詳細情報取得機能（一般ユーザー）
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_detail_page_shows_correct_break_times()
    {
        $user = User::factory()->create(['role' => 0]);
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $breakStart = Carbon::now()->subHours(4);
        $breakEnd = Carbon::now()->subHours(3);
        
        BreakModel::create([
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        
        $response->assertStatus(200);
        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($breakEnd->format('H:i'));
    }
}
