<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 2: ログイン認証機能（一般ユーザー）
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required_for_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 0,
        ]);

        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->get('email')[0]);
    }

    /**
     * ID 2: ログイン認証機能（一般ユーザー）
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required_for_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードを入力してください', session('errors')->get('password')[0]);
    }

    /**
     * ID 2: ログイン認証機能（一般ユーザー）
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->get('email')[0]);
    }

    /**
     * ID 2: ログイン認証機能（一般ユーザー）
     * 正常にログインできる
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 0,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/attendance');
    }
}
