<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakCorrection;
use App\Http\Requests\AttendanceCorrectionStoreRequest;
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
    public function store(AttendanceCorrectionStoreRequest $request)
    {
        $validated = $request->validated();

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
