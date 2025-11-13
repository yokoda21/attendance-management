<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 打刻画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 本日の勤怠データを取得
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        return view('attendance.index', compact('todayAttendance'));
    }

    /**
     * 出勤処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clockIn(Request $request)
    {
        // 本日の勤怠データが既に存在するかチェック
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        if ($todayAttendance) {
            return redirect()->route('attendance.index')
                ->with('error', '既に出勤済みです');
        }

        // 出勤記録を作成
        Attendance::create([
            'user_id' => Auth::id(),
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_IN,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '出勤しました');
    }

    /**
     * 退勤処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clockOut(Request $request)
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

        // 既に退勤済みの場合
        if ($todayAttendance->status == Attendance::STATUS_CLOCKED_OUT) {
            return redirect()->route('attendance.index')
                ->with('error', '既に退勤済みです');
        }

        // 休憩中の場合
        if ($todayAttendance->status == Attendance::STATUS_ON_BREAK) {
            return redirect()->route('attendance.index')
                ->with('error', '休憩中は退勤できません');
        }

        // 退勤時刻を記録
        $todayAttendance->update([
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '退勤しました');
    }

    /**
     * 勤怠一覧画面を表示（一般ユーザー）
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function list(Request $request)
    {
        // リクエストから年月を取得（デフォルトは今月）
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $yearMonth);

        // 月の最初の日と最後の日を取得
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // ログイン中のユーザーの勤怠データを取得
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // 月の全日付を生成
        $days = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $dateKey = $currentDate->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            
            $days[] = [
                'date' => $currentDate->copy(),
                'attendance' => $attendance,
            ];
            
            $currentDate->addDay();
        }

        // 前月・翌月の年月を計算
        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('days', 'date', 'prevMonth', 'nextMonth'));
    }

    /**
     * 勤怠詳細画面を表示（一般ユーザー）
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function detail($id)
    {
        // 指定された勤怠データを取得
        $attendance = Attendance::with('breaks', 'user')->findOrFail($id);

        // ログイン中のユーザーの勤怠データか確認
        if ($attendance->user_id !== Auth::id()) {
            abort(403, '権限がありません');
        }

        return view('attendance.detail', compact('attendance'));
    }
}
