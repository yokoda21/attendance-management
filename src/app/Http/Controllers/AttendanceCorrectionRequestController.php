<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionRequestController extends Controller
{
    /**
     * 修正申請一覧画面
     */
    public function index()
    {
        $userId = Auth::id();

        // 承認待ちの修正申請を取得
        $pendingRequests = AttendanceCorrectionRequest::with(['attendance.user', 'breakCorrections'])
            ->where('user_id', $userId)
            ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        // 承認済みの修正申請を取得
        $approvedRequests = AttendanceCorrectionRequest::with(['attendance.user', 'breakCorrections'])
            ->where('user_id', $userId)
            ->where('status', AttendanceCorrectionRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('attendance-correction.index', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 修正申請作成画面
     */
    public function create(Request $request)
    {
        $attendanceId = $request->query('attendance_id');
        
        // 勤怠データを取得
        $attendance = Attendance::with('breaks', 'user')->findOrFail($attendanceId);

        // ログイン中のユーザーの勤怠データか確認
        if ($attendance->user_id !== Auth::id()) {
            abort(403, '権限がありません');
        }

        // 承認待ちの修正申請がある場合はエラー
        $hasPendingRequest = AttendanceCorrectionRequest::where('attendance_id', $attendanceId)
            ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
            ->exists();

        return view('attendance-correction.create', compact('attendance', 'hasPendingRequest'));
    }

    /**
     * 修正申請を保存
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'breaks' => 'nullable|array',
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end' => 'nullable|date_format:H:i|after:breaks.*.break_start',
            'note' => 'required|string|max:500',
        ], [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間の形式が正しくありません',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間の形式が正しくありません',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_start.date_format' => '休憩時間の形式が正しくありません',
            'breaks.*.break_end.date_format' => '休憩時間の形式が正しくありません',
            'breaks.*.break_end.after' => '休憩時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'note.max' => '備考は500文字以内で入力してください',
        ]);

        // 勤怠データを取得
        $attendance = Attendance::findOrFail($validated['attendance_id']);

        // 権限チェック
        if ($attendance->user_id !== Auth::id()) {
            abort(403, '権限がありません');
        }

        try {
            DB::beginTransaction();

            // 修正申請を作成
            $correctionRequest = AttendanceCorrectionRequest::create([
                'attendance_id' => $validated['attendance_id'],
                'user_id' => Auth::id(),
                'clock_in' => $validated['clock_in'],
                'clock_out' => $validated['clock_out'],
                'note' => $validated['note'],
                'status' => AttendanceCorrectionRequest::STATUS_PENDING,
            ]);

            // 休憩の修正を保存
            if (!empty($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        BreakCorrection::create([
                            'correction_request_id' => $correctionRequest->id,
                            'break_start' => $break['break_start'],
                            'break_end' => $break['break_end'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('attendance-correction.index')
                ->with('success', '修正申請を送信しました');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '修正申請の送信に失敗しました');
        }
    }
}
