<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 外部キー制約の順序に従って実行
        $this->call([
            UsersTableSeeder::class,        // 1. ユーザー（他のテーブルから参照される）
            AttendancesTableSeeder::class,  // 2. 勤怠（breaksから参照される）
            BreaksTableSeeder::class,       // 3. 休憩（attendancesに依存）
        ]);
    }
}
