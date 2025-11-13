<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\BreakModel;
use App\Models\BreakCorrection;
use App\Models\AttendanceHistory;
use App\Models\BreakHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorrectionController extends Controller
{
    /**
     * 修正申請一覧画面（管理者）
     * FN047: 承認待ち情報取得機能
     * FN048: 承認済み情報取得機能
     */
    public function index(Request $request)
    {
        // タブの選択（デフォルトは承認待ち）
        $tab = $request->input('tab', 'pending');

        // 承認待ちの修正申請を取得
        $pendingRequests = AttendanceCorrectionRequest::with(['user', 'attendance'])
            ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        // 承認済みの修正申請を取得
        $approvedRequests = AttendanceCorrectionRequest::with(['user', 'attendance'])
            ->where('status', AttendanceCorrectionRequest::STATUS_APPROVED)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.corrections.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'tab' => $tab,
        ]);
    }

    /**
     * 修正申請詳細画面（管理者）
     * FN050: 申請詳細取得機能
     */
    public function show($id)
    {
        // 修正申請を取得
        $correctionRequest = AttendanceCorrectionRequest::with([
            'user',
            'attendance',
            'breakCorrections'
        ])->findOrFail($id);

        return view('admin.corrections.show', [
            'correctionRequest' => $correctionRequest,
        ]);
    }

    /**
     * 修正申請承認機能
     * FN051: 承認機能
     */
    public function approve($id)
    {
        \Log::info('Approve method called with ID: ' . $id);
        
        DB::beginTransaction();

        try {
            \Log::info('Starting approval process');
            
            // 修正申請を取得
            $correctionRequest = AttendanceCorrectionRequest::with([
                'attendance',
                'breakCorrections'
            ])->findOrFail($id);
            
            \Log::info('Correction request found', ['status' => $correctionRequest->status]);

            // 承認待ちでない場合はエラー
            if ($correctionRequest->status !== AttendanceCorrectionRequest::STATUS_PENDING) {
                \Log::warning('Request is not pending');
                return redirect()->back()->with('error', 'この申請は既に処理されています。');
            }

            $attendance = $correctionRequest->attendance;
            \Log::info('Attendance found', ['id' => $attendance->id]);

            // 変更前の勤怠データを履歴に保存
            AttendanceHistory::create([
                'attendance_id' => $attendance->id,
                'changed_by' => auth()->id(), // 現在ログイン中の管理者
                'changed_type' => 0, // 0:申請承認
                'before_clock_in' => $attendance->clock_in,
                'after_clock_in' => $correctionRequest->clock_in,
                'before_clock_out' => $attendance->clock_out,
                'after_clock_out' => $correctionRequest->clock_out,
                'note' => $correctionRequest->note,
            ]);
            \Log::info('Attendance history created');

            // 変更前の休憩データを履歴に保存
            $oldBreaks = BreakModel::where('attendance_id', $attendance->id)->get();
            foreach ($oldBreaks as $break) {
                // 対応する修正後の休憩データを探す
                $newBreak = $correctionRequest->breakCorrections->first();
                
                BreakHistory::create([
                    'break_id' => $break->id,
                    'changed_by' => auth()->id(),
                    'before_break_start' => $break->break_start,
                    'after_break_start' => $newBreak ? $newBreak->break_start : null,
                    'before_break_end' => $break->break_end,
                    'after_break_end' => $newBreak ? $newBreak->break_end : null,
                    'note' => $correctionRequest->note,
                ]);
            }
            \Log::info('Break history created', ['count' => $oldBreaks->count()]);

            // 勤怠データを更新
            $attendance->update([
                'clock_in' => $correctionRequest->clock_in,
                'clock_out' => $correctionRequest->clock_out,
                'remarks' => $correctionRequest->note,
            ]);
            \Log::info('Attendance updated');

            // 既存の休憩データを削除
            BreakModel::where('attendance_id', $attendance->id)->delete();
            \Log::info('Old breaks deleted');

            // 新しい休憩データを作成
            foreach ($correctionRequest->breakCorrections as $breakCorrection) {
                if ($breakCorrection->break_start && $breakCorrection->break_end) {
                    BreakModel::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $breakCorrection->break_start,
                        'break_end' => $breakCorrection->break_end,
                    ]);
                }
            }
            \Log::info('New breaks created', ['count' => $correctionRequest->breakCorrections->count()]);

            // 修正申請のステータスを承認済みに変更
            $correctionRequest->update([
                'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
            ]);
            \Log::info('Correction request status updated to approved');

            DB::commit();
            \Log::info('Transaction committed successfully');

            return redirect()->route('admin.corrections.index')
                ->with('success', '修正申請を承認しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Approval failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', '承認処理に失敗しました。');
        }
    }
}
