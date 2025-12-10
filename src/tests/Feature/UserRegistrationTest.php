<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 1: 認証機能（一般ユーザー）
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertEquals('お名前を入力してください', session('errors')->get('name')[0]);
    }

    /**
     * ID 1: 認証機能（一般ユーザー）
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->get('email')[0]);
    }

    /**
     * ID 1: 認証機能（一般ユーザー）
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードは8文字以上で入力してください', session('errors')->get('password')[0]);
    }

    /**
     * ID 1: 認証機能（一般ユーザー）
     * パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードと一致しません', session('errors')->get('password')[0]);
    }

    /**
     * ID 1: 認証機能（一般ユーザー）
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードを入力してください', session('errors')->get('password')[0]);
    }

    /**
     * ID 1: 認証機能（一般ユーザー）
     * フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'role' => 0, // 一般ユーザー
        ]);

        $response->assertRedirect('/attendance');
    }
}
