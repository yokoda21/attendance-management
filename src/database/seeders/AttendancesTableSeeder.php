<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザーのID（2〜11）
        $userIds = range(2, 11);
        
        // 過去30日分のデータを生成
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->subDays(1); // 昨日まで

        foreach ($userIds as $userId) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // 土日をスキップする場合は以下のコメントを外す
                // if ($currentDate->isWeekend()) {
                //     $currentDate->addDay();
                //     continue;
                // }

                // ランダムで欠勤日を作成（10%の確率）
                if (rand(1, 100) <= 10) {
                    $currentDate->addDay();
                    continue;
                }

                // 出勤時刻（8:00〜9:30の間）
                $clockInHour = rand(8, 9);
                $clockInMinute = $clockInHour == 9 ? rand(0, 30) : rand(0, 59);
                $clockIn = $currentDate->copy()->setTime($clockInHour, $clockInMinute, 0);

                // 退勤時刻（17:00〜19:00の間）
                $clockOutHour = rand(17, 19);
                $clockOutMinute = rand(0, 59);
                $clockOut = $currentDate->copy()->setTime($clockOutHour, $clockOutMinute, 0);

                // status: 3 = 退勤済み
                $status = 3;

                DB::table('attendances')->insert([
                    'user_id' => $userId,
                    'date' => $currentDate->format('Y-m-d'),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'note' => null,
                    'created_at' => $clockIn,
                    'updated_at' => $clockOut,
                ]);

                $currentDate->addDay();
            }
        }

        // 当日のデータ（一部のユーザーのみ、出勤中や休憩中の状態を作成）
        $today = Carbon::today();
        
        // ユーザー2: 出勤中
        DB::table('attendances')->insert([
            'user_id' => 2,
            'date' => $today->format('Y-m-d'),
            'clock_in' => $today->copy()->setTime(9, 0, 0),
            'clock_out' => null,
            'status' => 1, // 出勤中
            'note' => null,
            'created_at' => $today->copy()->setTime(9, 0, 0),
            'updated_at' => $today->copy()->setTime(9, 0, 0),
        ]);

        // ユーザー3: 休憩中
        DB::table('attendances')->insert([
            'user_id' => 3,
            'date' => $today->format('Y-m-d'),
            'clock_in' => $today->copy()->setTime(8, 45, 0),
            'clock_out' => null,
            'status' => 2, // 休憩中
            'note' => null,
            'created_at' => $today->copy()->setTime(8, 45, 0),
            'updated_at' => $today->copy()->setTime(12, 0, 0),
        ]);

        // ユーザー4: 退勤済み
        DB::table('attendances')->insert([
            'user_id' => 4,
            'date' => $today->format('Y-m-d'),
            'clock_in' => $today->copy()->setTime(9, 15, 0),
            'clock_out' => $today->copy()->setTime(18, 30, 0),
            'status' => 3, // 退勤済み
            'note' => null,
            'created_at' => $today->copy()->setTime(9, 15, 0),
            'updated_at' => $today->copy()->setTime(18, 30, 0),
        ]);
    }
}
