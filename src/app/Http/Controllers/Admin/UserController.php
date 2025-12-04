<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * スタッフ一覧画面（FN041）
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 一般ユーザー（role = 0）のみ取得
        $users = User::where('role', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * スタッフ別月次勤怠一覧画面（FN043, FN044, FN046）
     * 
     * @param Request $request
     * @param int $user_id
     * @return \Illuminate\View\View
     */
    public function attendances(Request $request, $user_id)
    {
        // ユーザーを取得
        $user = User::findOrFail($user_id);

        // 月パラメータを取得（デフォルトは今月）
        $month = $request->input('month', \Carbon\Carbon::now()->format('Y-m'));

        // Carbon インスタンスに変換
        $targetMonth = \Carbon\Carbon::parse($month . '-01');

        // 前月・翌月の月を計算
        $previousMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

        // 指定月の勤怠データを取得
        $attendanceRecords = \App\Models\Attendance::with(['breaks'])
            ->where('user_id', $user_id)
            ->whereYear('date', $targetMonth->year)
            ->whereMonth('date', $targetMonth->month)
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // 月の全日付を生成
        $daysInMonth = $targetMonth->daysInMonth;
        $attendances = collect();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $targetMonth->copy()->day($day);
            $dateStr = $date->format('Y-m-d');

            if ($attendanceRecords->has($dateStr)) {
                // 勤怠データが存在する場合
                $attendance = $attendanceRecords->get($dateStr);

                // 休憩時間の合計を計算
                $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
                    if ($break->break_start && $break->break_end) {
                        $start = \Carbon\Carbon::parse($break->break_start);
                        $end = \Carbon\Carbon::parse($break->break_end);
                        return $end->diffInMinutes($start);
                    }
                    return 0;
                });

                // 勤務時間の合計を計算
                $totalWorkMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                    $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                    $totalMinutes = $clockOut->diffInMinutes($clockIn);
                    $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;
                }
                
                // 時間:分 形式に変換（0の場合は空文字）
                $attendance->total_break = $totalBreakMinutes > 0
                    ? sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
                    : '';
                $attendance->total_work = $totalWorkMinutes > 0
                    ? sprintf('%d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60)
                    : '';

                $attendances->push($attendance);
            } else {
                // 勤怠データが存在しない場合、空のオブジェクトを作成
                $emptyAttendance = new \stdClass();
                $emptyAttendance->date = $date;
                $emptyAttendance->clock_in = null;
                $emptyAttendance->clock_out = null;
                $emptyAttendance->total_break = '';
                $emptyAttendance->total_work = '';
                $emptyAttendance->id = null;

                $attendances->push($emptyAttendance);
            }
        }

        return view('admin.users.attendances', compact('user', 'attendances', 'targetMonth', 'previousMonth', 'nextMonth'));
    }

    /**
     * CSV出力機能（FN045）
     * 
     * @param Request $request
     * @param int $user_id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request, $user_id)
    {
        // ユーザーを取得
        $user = User::findOrFail($user_id);

        // 月パラメータを取得
        $month = $request->input('month', \Carbon\Carbon::now()->format('Y-m'));
        $targetMonth = \Carbon\Carbon::parse($month . '-01');

        // 指定月の勤怠データを取得
        $attendances = \App\Models\Attendance::with(['breaks'])
            ->where('user_id', $user_id)
            ->whereYear('date', $targetMonth->year)
            ->whereMonth('date', $targetMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        // CSVファイル名
        $filename = $user->name . '_' . $targetMonth->format('Y年m月') . '_勤怠一覧.csv';

        // CSVレスポンスを返す
        return response()->streamDownload(function () use ($attendances, $targetMonth) {
            $handle = fopen('php://output', 'w');

            // BOMを追加（Excelで文字化けを防ぐ）
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            // 月の全日付を生成
            $daysInMonth = $targetMonth->daysInMonth;
            $attendancesByDate = $attendances->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            });

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $targetMonth->copy()->day($day);
                $dateStr = $date->format('Y-m-d');
                $attendance = $attendancesByDate->get($dateStr);

                if ($attendance) {
                    // 休憩時間の合計を計算
                    $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
                        if ($break->break_start && $break->break_end) {
                            $start = \Carbon\Carbon::parse($break->break_start);
                            $end = \Carbon\Carbon::parse($break->break_end);
                            return $end->diffInMinutes($start);
                        }
                        return 0;
                    });

                    // 勤務時間の合計を計算
                    $totalWorkMinutes = 0;
                    if ($attendance->clock_in && $attendance->clock_out) {
                        $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                        $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                        $totalMinutes = $clockOut->diffInMinutes($clockIn);
                        $totalWorkMinutes = $totalMinutes - $totalBreakMinutes;
                    }

                    $row = [
                        $date->format('Y/m/d'),
                        $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '',
                        $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '',
                        sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60),
                        sprintf('%d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60),
                    ];
                } else {
                    $row = [
                        $date->format('Y/m/d'),
                        '',
                        '',
                        '',
                        '',
                    ];
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
