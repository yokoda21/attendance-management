<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 全ての勤怠レコードを取得
        $attendances = DB::table('attendances')->get();

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->date);
            $clockIn = Carbon::parse($attendance->clock_in);
            
            // 退勤済み（status = 3）の場合のみ休憩データを作成
            if ($attendance->status == 3) {
                // 休憩1: 昼休憩（12:00〜13:00）
                $break1Start = $date->copy()->setTime(12, 0, 0);
                $break1End = $date->copy()->setTime(13, 0, 0);

                DB::table('breaks')->insert([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break1Start,
                    'break_end' => $break1End,
                    'created_at' => $break1Start,
                    'updated_at' => $break1End,
                ]);

                // ランダムで追加の休憩を作成（50%の確率）
                if (rand(1, 100) <= 50) {
                    // 休憩2: 午後の休憩（15:00〜15:15）
                    $break2Start = $date->copy()->setTime(15, 0, 0);
                    $break2End = $date->copy()->setTime(15, 15, 0);

                    DB::table('breaks')->insert([
                        'attendance_id' => $attendance->id,
                        'break_start' => $break2Start,
                        'break_end' => $break2End,
                        'created_at' => $break2Start,
                        'updated_at' => $break2End,
                    ]);
                }
            }
            
            // 休憩中（status = 2）の場合は休憩開始のみ
            if ($attendance->status == 2) {
                $breakStart = $date->copy()->setTime(12, 0, 0);

                DB::table('breaks')->insert([
                    'attendance_id' => $attendance->id,
                    'break_start' => $breakStart,
                    'break_end' => null,
                    'created_at' => $breakStart,
                    'updated_at' => $breakStart,
                ]);
            }
        }
    }
}
