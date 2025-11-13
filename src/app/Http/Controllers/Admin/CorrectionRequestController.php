<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;

class CorrectionRequestController extends Controller
{
    /**
     * 修正申請一覧画面（管理者）
     * US014 - FN047, FN048, FN049
     */
    public function index(Request $request)
    {
        // タブの状態を取得（デフォルトは承認待ち）
        $tab = $request->query('tab', 'pending');
        
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
        
        return view('admin.correction-requests.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'tab' => $tab,
        ]);
    }
}
