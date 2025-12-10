<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakModel;
use App\Models\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 12: 勤怠一覧情報取得機能（管理者）
     * その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_admin_can_see_all_users_attendance_for_day()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create(['role' => 0]);
        $user2 = User::factory()->create(['role' => 0]);
        $user3 = User::factory()->create(['role' => 0]);

        // 3人のユーザーの今日の勤怠データを作成
        Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(7),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        Attendance::create([
            'user_id' => $user3->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(9),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee($user3->name);
    }

    /**
     * ID 12: 勤怠一覧情報取得機能（管理者）
     * 遷移した際に現在の日付が表示される
     */
    public function test_admin_list_shows_current_date()
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y年m月d日'));
    }

    /**
     * ID 12: 勤怠一覧情報取得機能（管理者）
     * 「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function test_admin_can_view_previous_day()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $yesterday = Carbon::yesterday();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'clock_in' => $yesterday->copy()->setHour(9),
            'clock_out' => $yesterday->copy()->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年m月d日'));
    }

    /**
     * ID 12: 勤怠一覧情報取得機能（管理者）
     * 「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function test_admin_can_view_next_day()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $tomorrow = Carbon::tomorrow();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrow,
            'clock_in' => $tomorrow->copy()->setHour(9),
            'clock_out' => $tomorrow->copy()->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年m月d日'));
    }

    /**
     * ID 13: 勤怠詳細情報取得・修正機能（管理者）
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_admin_detail_shows_correct_attendance_data()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0, 'name' => 'テストユーザー']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee(Carbon::today()->format('Y-m-d'));
    }

    /**
     * ID 13: 勤怠詳細情報取得・修正機能（管理者）
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_cannot_set_clock_in_after_clock_out()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => Carbon::now()->format('H:i'),
            'clock_out' => Carbon::now()->subHours(8)->format('H:i'),
            'remarks' => '管理者による修正',
        ]);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    /**
     * ID 13: 勤怠詳細情報取得・修正機能（管理者）
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_cannot_set_break_start_after_clock_out()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => Carbon::now()->subHours(8)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
            'breaks' => [
                [
                    'break_start' => Carbon::now()->addHour()->format('H:i'),
                    'break_end' => Carbon::now()->addHours(2)->format('H:i'),
                ]
            ],
            'remarks' => '管理者による修正',
        ]);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('休憩時間が不適切な値です', session('errors')->first());
    }

    /**
     * ID 13: 勤怠詳細情報取得・修正機能（管理者）
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_cannot_set_break_end_after_clock_out()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => Carbon::now()->subHours(8)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
            'breaks' => [
                [
                    'break_start' => Carbon::now()->subHours(4)->format('H:i'),
                    'break_end' => Carbon::now()->addHour()->format('H:i'),
                ]
            ],
            'remarks' => '管理者による修正',
        ]);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    /**
     * ID 13: 勤怠詳細情報取得・修正機能（管理者）
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_admin_remarks_field_is_required()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => Carbon::now()->subHours(8)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
        ]);

        $response->assertSessionHasErrors('remarks');
        $this->assertEquals('備考を記入してください', session('errors')->get('remarks')[0]);
    }

    /**
     * ID 14: ユーザー情報取得機能（管理者）
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_view_all_users()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create(['role' => 0, 'name' => 'ユーザー1', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['role' => 0, 'name' => 'ユーザー2', 'email' => 'user2@example.com']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('ユーザー2');
        $response->assertSee('user2@example.com');
    }

    /**
     * ID 14: ユーザー情報取得機能（管理者）
     * ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_view_user_attendance_records()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        for ($i = 0; $i < 3; $i++) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today()->subDays($i),
                'clock_in' => Carbon::today()->subDays($i)->setHour(9),
                'clock_out' => Carbon::today()->subDays($i)->setHour(18),
                'status' => Attendance::STATUS_CLOCKED_OUT,
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id);

        $response->assertStatus(200);
    }

    /**
     * ID 14: ユーザー情報取得機能（管理者）
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_user_attendance_shows_previous_month()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $lastMonth = Carbon::now()->subMonth();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $lastMonth,
            'clock_in' => $lastMonth->copy()->setHour(9),
            'clock_out' => $lastMonth->copy()->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=' . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($lastMonth->format('Y年m月'));
    }

    /**
     * ID 14: ユーザー情報取得機能（管理者）
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_admin_user_attendance_shows_next_month()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $nextMonth = Carbon::now()->addMonth();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth,
            'clock_in' => $nextMonth->copy()->setHour(9),
            'clock_out' => $nextMonth->copy()->setHour(18),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y年m月'));
    }

    /**
     * ID 14: ユーザー情報取得機能（管理者）
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_detail_button_redirects_to_detail_page()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
    }

    /**
     * ID 15: 勤怠情報修正機能（管理者）
     * 承認待ちの修正申請が全て表示されている
     */
    public function test_admin_can_see_all_pending_correction_requests()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create(['role' => 0]);
        $user2 = User::factory()->create(['role' => 0]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(7),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'remarks' => '修正理由1',
            'status' => 0,
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'remarks' => '修正理由2',
            'status' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('修正理由1');
        $response->assertSee('修正理由2');
    }

    /**
     * ID 15: 勤怠情報修正機能（管理者）
     * 承認済みの修正申請が全て表示されている
     */
    public function test_admin_can_see_all_approved_correction_requests()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'remarks' => '承認済み修正',
            'status' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み修正');
    }

    /**
     * ID 15: 勤怠情報修正機能（管理者）
     * 修正申請の詳細内容が正しく表示されている
     */
    public function test_admin_can_view_correction_request_details()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'remarks' => '詳細表示テスト',
            'status' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/' . $correctionRequest->id);

        $response->assertStatus(200);
        $response->assertSee('詳細表示テスト');
    }

    /**
     * ID 15: 勤怠情報修正機能（管理者）
     * 修正申請の承認処理が正しく行われる
     */
    public function test_admin_can_approve_correction_request()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        $originalClockIn = Carbon::now()->subHours(8);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => $originalClockIn,
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $newClockIn = Carbon::now()->subHours(9);
        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => $newClockIn,
            'clock_out' => Carbon::now(),
            'remarks' => '承認テスト',
            'status' => 0,
        ]);

        $response = $this->actingAs($admin)->post('/stamp_correction_request/approve/' . $correctionRequest->id);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correctionRequest->id,
            'status' => 1,
        ]);

        $attendance->refresh();
        $this->assertEquals($newClockIn->format('Y-m-d H:i:s'), $attendance->clock_in->format('Y-m-d H:i:s'));

        $response->assertRedirect();
    }
}
