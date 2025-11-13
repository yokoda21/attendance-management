<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 管理者ユーザー
        DB::table('users')->insert([
            'name' => '管理者太郎',
            'email' => 'admin@example.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password123'),
            'role' => 1,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 一般ユーザー（10名）
        $users = [
            ['name' => '山田花子', 'email' => 'yamada@example.com'],
            ['name' => '佐藤次郎', 'email' => 'sato@example.com'],
            ['name' => '鈴木三郎', 'email' => 'suzuki@example.com'],
            ['name' => '田中美咲', 'email' => 'tanaka@example.com'],
            ['name' => '伊藤健太', 'email' => 'ito@example.com'],
            ['name' => '渡辺愛', 'email' => 'watanabe@example.com'],
            ['name' => '中村大輔', 'email' => 'nakamura@example.com'],
            ['name' => '小林優子', 'email' => 'kobayashi@example.com'],
            ['name' => '加藤翔太', 'email' => 'kato@example.com'],
            ['name' => '吉田莉子', 'email' => 'yoshida@example.com'],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified_at' => $now,
                'password' => Hash::make('password123'), // 全ユーザー共通のパスワード
                'role' => 0,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
