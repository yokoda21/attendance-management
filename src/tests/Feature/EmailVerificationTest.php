<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 16: メール認証機能
     * 会員登録後、認証メールが送信される
     */
    public function test_email_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 会員登録が成功する
        $response->assertStatus(302);

        // ユーザーが作成される
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // メール認証通知が送信される
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * ID 16: メール認証機能
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_unverified_user_is_redirected_to_verification_notice()
    {
        // メール未認証のユーザーを作成
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => null,
        ]);

        // 保護されたルート（勤怠画面）にアクセス
        $response = $this->actingAs($user)->get('/attendance');

        // メール認証誘導画面にリダイレクトされる
        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    /**
     * ID 16: メール認証機能
     * メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_user_can_verify_email_and_access_attendance()
    {
        // メール未認証のユーザーを作成
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => null,
        ]);

        // メール認証URLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // メール認証URLにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // メール認証が完了する
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // リダイレクトされる（Fortifyのデフォルトは/dashboard、カスタマイズされている場合は別のページ）
        $response->assertStatus(302);

        // メール認証後、勤怠画面にアクセスできる
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤怠');
    }
}
