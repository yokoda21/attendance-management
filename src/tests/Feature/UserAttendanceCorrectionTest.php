<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakModel;
use App\Models\AttendanceCorrectionRequest;
use Carbon\Carbon;

class UserAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     * 
     * 機能要件 FN029:
     * 「出勤時間もしくは退勤時間が不適切な値です」
     */
    public function test_clock_in_cannot_be_after_clock_out()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->format('H:i'),
            'clock_out' => Carbon::now()->subHours(8)->format('H:i'),
            'note' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        // 出勤時間が退勤時間より後の場合のエラーメッセージ（機能要件 FN029）
        $this->assertStringContainsString('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     * 
     * 機能要件 FN029:
     * 「休憩時間が不適切な値です」
     */
    public function test_break_start_cannot_be_after_clock_out()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        BreakModel::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subHours(4),
            'break_end' => Carbon::now()->subHours(3),
        ]);

        $response = $this->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->subHours(8)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
            'breaks' => [
                [
                    'break_start' => Carbon::now()->addHour()->format('H:i'),
                    'break_end' => Carbon::now()->addHours(2)->format('H:i'),
                ]
            ],
            'note' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        // 休憩開始時間が退勤時間より後の場合のエラーメッセージ（機能要件 FN029）
        $this->assertStringContainsString('休憩時間が不適切な値です', session('errors')->first());
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     * 
     * 機能要件 FN029:
     * 「休憩時間もしくは退勤時間が不適切な値です」
     */
    public function test_break_end_cannot_be_after_clock_out()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        BreakModel::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subHours(4),
            'break_end' => Carbon::now()->subHours(3),
        ]);

        $response = $this->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->subHours(8)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
            'breaks' => [
                [
                    'break_start' => Carbon::now()->subHours(4)->format('H:i'),
                    'break_end' => Carbon::now()->addHour()->format('H:i'),
                ]
            ],
            'note' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        // 休憩終了時間が退勤時間より後の場合のエラーメッセージ（機能要件 FN029）
        $this->assertStringContainsString('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 備考欄が未入力の場合のエラーメッセージが表示される
     * 
     * フィールド名: note（remarksではない）
     */
    public function test_remarks_field_is_required()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->subHours(8)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
        ]);

        // フィールド名は'note'
        $response->assertSessionHasErrors('note');
        $this->assertEquals('備考を記入してください', session('errors')->get('note')[0]);
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 修正申請処理が実行される
     */
    public function test_correction_request_is_created_successfully()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);
        $admin = User::factory()->create(['role' => 1]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => Carbon::now()->subHours(9)->format('H:i'),
            'clock_out' => Carbon::now()->format('H:i'),
            'note' => '打刻忘れのため修正',
        ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 0, // 承認待ち
        ]);

        // 管理者の承認画面で確認
        $adminResponse = $this->actingAs($admin)->get('/stamp_correction_request/list');
        $adminResponse->assertStatus(200);
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_pending_requests_are_displayed_in_user_list()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        // noteフィールドを追加
        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'note' => '修正理由',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('修正理由');
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function test_approved_requests_are_displayed_in_user_list()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        // noteフィールドを追加
        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'note' => '修正理由',
            'status' => 1, // 承認済み
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('修正理由');
    }

    /**
     * ID 11: 勤怠詳細情報修正機能（一般ユーザー）
     * 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_detail_button_redirects_to_attendance_detail()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        // noteフィールドを追加
        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => Carbon::now()->subHours(9),
            'clock_out' => Carbon::now(),
            'note' => '修正理由',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
    }
}
