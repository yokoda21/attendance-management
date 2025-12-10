<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 3: ログイン認証機能（管理者）
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required_for_login()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->get('email')[0]);
    }

    /**
     * ID 3: ログイン認証機能（管理者）
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required_for_login()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードを入力してください', session('errors')->get('password')[0]);
    }

    /**
     * ID 3: ログイン認証機能（管理者）
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->get('email')[0]);
    }

    /**
     * ID 3: ログイン認証機能（管理者）
     * 正常にログインできる
     */
    public function test_admin_can_login_with_valid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin/attendance/list');
    }
}
