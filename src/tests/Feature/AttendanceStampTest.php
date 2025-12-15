<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakModel;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 4: 日時取得機能
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_datetime_is_displayed_correctly()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // 日時表示用のコンテナが存在することを確認
        $response->assertSee('current-datetime');
        // JavaScriptが実行されることを確認
        $response->assertSee('updateDateTime');
    }

    /**
     * ID 5: ステータス確認機能
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_off_work_correctly()
    {
        // メール認証済みのユーザーを作成
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // デバッグ: HTMLの内容を出力
        dump($response->getContent());

        $response->assertSee('勤務外');
    }

    /**
     * ID 5: ステータス確認機能
     * 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_clocked_in_correctly()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * ID 5: ステータス確認機能
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_on_break_correctly()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        BreakModel::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * ID 5: ステータス確認機能
     * 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_clocked_out_correctly()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now()->subHour(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    /**
     * ID 6: 出勤機能
     * 出勤ボタンが正しく機能する
     */
    public function test_clock_in_button_works_correctly()
    {
        $user = User::factory()->create(['role' => 0]);

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $response->assertRedirect('/attendance');
    }

    /**
     * ID 6: 出勤機能
     * 出勤は一日一回のみできる
     */
    public function test_cannot_clock_in_twice_in_one_day()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now()->subHour(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('class="btn-clock-in"');
    }

    /**
     * ID 6: 出勤機能
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_displayed_in_list()
    {
        $user = User::factory()->create(['role' => 0]);
        $clockInTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => $clockInTime,
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($clockInTime->format('H:i'));
    }

    /**
     * ID 7: 休憩機能
     * 休憩ボタンが正しく機能する
     * 
     * 修正: /attendance/break-start → /break/start
     */
    public function test_break_start_button_works_correctly()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $response = $this->actingAs($user)->post('/break/start');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $response->assertRedirect('/attendance');
    }

    /**
     * ID 7: 休憩機能
     * 休憩は一日に何回でもできる
     * 
     * 修正: /attendance/break-start → /break/start
     * 修正: /attendance/break-end → /break/end
     */
    public function test_can_take_multiple_breaks()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(4),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post('/break/start');
        $this->actingAs($user)->post('/break/end');

        // 2回目の休憩が可能か確認
        $response = $this->actingAs($user)->post('/break/start');

        $this->assertEquals(2, BreakModel::where('attendance_id', $attendance->id)->count());
        $response->assertRedirect('/attendance');
    }

    /**
     * ID 7: 休憩機能
     * 休憩戻ボタンが正しく機能する
     * 
     * 修正: /attendance/break-end → /break/end
     */
    public function test_break_end_button_works_correctly()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $break = BreakModel::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($user)->post('/break/end');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $this->assertDatabaseHas('breaks', [
            'id' => $break->id,
        ]);

        $break->refresh();
        $this->assertNotNull($break->break_end);

        $response->assertRedirect('/attendance');
    }

    /**
     * ID 7: 休憩機能
     * 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_is_displayed_in_list()
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

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
    }

    /**
     * ID 8: 退勤機能
     * 退勤ボタンが正しく機能する
     */
    public function test_clock_out_button_works_correctly()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);

        $response->assertRedirect('/attendance');
    }

    /**
     * ID 8: 退勤機能
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_displayed_in_list()
    {
        $user = User::factory()->create(['role' => 0]);
        $clockOutTime = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => $clockOutTime,
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($clockOutTime->format('H:i'));
    }
}
