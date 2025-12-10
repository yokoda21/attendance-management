<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Http\Requests\AttendanceUpdateRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * 日次勤怠一覧画面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 日付パラメータを取得（デフォルトは今日）
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Carbon インスタンスに変換
        $targetDate = Carbon::parse($date);

        // 前日・翌日の日付を計算
        $previousDate = $targetDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $targetDate->copy()->addDay()->format('Y-m-d');

        // 指定日の勤怠データを取得（実際に勤務した人のみ：status > 0）
        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('date', $targetDate)
            ->where('status', '>', 0)  // 勤務外（status=0）は除外
            ->get()
            ->map(function ($attendance) {
                // 休憩時間の合計を計算
                $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
                    if ($break->break_start && $break->break_end) {
                        $start = Carbon::parse($break->break_start);
                        $end = Carbon::parse($break->break_end);
                        return $end->diffInMinutes($start);
                    }
                    return 0;
                });

                // 勤務時間の合計を計算
                $totalWorkMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockIn = Carbon::parse($attendance->clock_in);
                    $clockOut = Carbon::parse($attendance->clock_out);
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
                return $attendance;
            });

        return view('admin.attendance.index', compact('attendances', 'targetDate', 'previousDate', 'nextDate'));
    }

    /**
     * 勤怠詳細画面（管理者）
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // IDが日付形式（Y-m-d）かどうかを判定
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $id)) {
            // 日付形式の場合
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $id);

            // URLから user_id を取得（クエリパラメータとして渡す必要がある）
            $userId = request()->query('user_id');

            if (!$userId) {
                abort(400, 'ユーザーIDが指定されていません');
            }

            // その日の勤怠データを取得または新規作成
            $attendance = Attendance::with('breaks', 'user')
                ->where('user_id', $userId)
                ->whereDate('date', $date)
                ->first();

            // 勤怠データが存在しない場合は新規作成
            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'date' => $date,
                    'status' => Attendance::STATUS_OFF_WORK, //勤務外のステータスで作成
                ]);
                // 作成したIDでリダイレクト
                return redirect()->route('admin.attendance.show',  $attendance->id);
            }
        } else {
            // ID形式の場合（従来通り）
            $attendance = Attendance::with('breaks', 'user')->findOrFail($id);
        }

        // 承認待ちの修正申請があるか確認（FN038）
        $hasPendingRequest = \App\Models\AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', \App\Models\AttendanceCorrectionRequest::STATUS_PENDING)
            ->exists();

        return view('admin.attendance.show', compact('attendance', 'hasPendingRequest'));
    }

    /**
     * 管理者による直接修正処理（FN040）
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AttendanceUpdateRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            \DB::beginTransaction();

            // 勤怠データを取得
            $attendance = Attendance::findOrFail($id);

            // 勤怠情報を更新
            $attendance->clock_in = $validated['clock_in'];
            $attendance->clock_out = $validated['clock_out'];
            $attendance->save();

            // 既存の休憩データを削除
            $attendance->breaks()->delete();

            // 新しい休憩データを作成
            if (!empty($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        \App\Models\BreakModel::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $break['break_start'],
                            'break_end' => $break['break_end'],
                        ]);
                    }
                }
            }

            \DB::commit();

            return redirect()->route('admin.attendance.show', $id)
                ->with('success', '勤怠情報を修正しました');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withInput()->with('error', '修正に失敗しました');
        }
    }
}
