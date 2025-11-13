<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BreakController extends Controller
{
    /**
     * 休憩開始処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Request $request)
    {
        // 本日の勤怠データを取得
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        // 勤怠データが存在しない場合
        if (!$todayAttendance) {
            return redirect()->route('attendance.index')
                ->with('error', '出勤記録がありません');
        }

        // 既に休憩中の場合
        if ($todayAttendance->status == Attendance::STATUS_ON_BREAK) {
            return redirect()->route('attendance.index')
                ->with('error', '既に休憩中です');
        }

        // 退勤済みの場合
        if ($todayAttendance->status == Attendance::STATUS_CLOCKED_OUT) {
            return redirect()->route('attendance.index')
                ->with('error', '退勤後は休憩できません');
        }

        // 休憩記録を作成
        BreakModel::create([
            'attendance_id' => $todayAttendance->id,
            'break_start' => Carbon::now(),
            // break_end は null（休憩中）
        ]);

        // 勤怠ステータスを休憩中に変更
        $todayAttendance->update([
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '休憩を開始しました');
    }

    /**
     * 休憩終了処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function end(Request $request)
    {
        // 本日の勤怠データを取得
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        // 勤怠データが存在しない場合
        if (!$todayAttendance) {
            return redirect()->route('attendance.index')
                ->with('error', '出勤記録がありません');
        }

        // 休憩中でない場合
        if ($todayAttendance->status != Attendance::STATUS_ON_BREAK) {
            return redirect()->route('attendance.index')
                ->with('error', '休憩中ではありません');
        }

        // 現在の休憩記録を取得（break_end が null のもの）
        $currentBreak = BreakModel::where('attendance_id', $todayAttendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$currentBreak) {
            return redirect()->route('attendance.index')
                ->with('error', '休憩記録が見つかりません');
        }

        // 休憩終了時刻を記録
        $currentBreak->update([
            'break_end' => Carbon::now(),
        ]);

        // 勤怠ステータスを出勤中に戻す
        $todayAttendance->update([
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '休憩を終了しました');
    }
}
