@extends('layouts.app')

@section('title', '打刻 - COACHTECH')

@section('content')
<div class="attendance-container">
    <!-- 退勤後のみメッセージ表示 -->
    @if($todayAttendance && $todayAttendance->status == \App\Models\Attendance::STATUS_CLOCKED_OUT)
        <h2>お疲れ様でした。</h2>
    @endif

    <!-- 現在日時表示 -->
    <div class="current-datetime">
        <p id="current-date"></p>
        <p id="current-time"></p>
    </div>

    <!-- 打刻ボタン -->
    <div class="button-container">
        @if(!$todayAttendance)
            <!-- 出勤前: 出勤ボタンのみ -->
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button type="submit" class="btn-clock-in">出勤する</button>
            </form>
        @elseif($todayAttendance->status == \App\Models\Attendance::STATUS_CLOCKED_IN)
            <!-- 出勤後（休憩前）: 退勤・休憩ボタン -->
            <form method="POST" action="{{ route('attendance.clockOut') }}">
                @csrf
                <button type="submit" class="btn-clock-out">退勤する</button>
            </form>
            <form method="POST" action="{{ route('break.start') }}">
                @csrf
                <button type="submit" class="btn-break-start">休憩する</button>
            </form>
        @elseif($todayAttendance->status == \App\Models\Attendance::STATUS_ON_BREAK)
            <!-- 休憩中: 休憩戻るボタンのみ -->
            <form method="POST" action="{{ route('break.end') }}">
                @csrf
                <button type="submit" class="btn-break-end">休憩戻る</button>
            </form>
        @elseif($todayAttendance->status == \App\Models\Attendance::STATUS_CLOCKED_OUT)
            <!-- 退勤後: ボタンなし（メッセージは上部に「お疲れ様でした。」のみ） -->
        @endif
    </div>

    <!-- エラーメッセージ表示 -->
    @if(session('error'))
        <div class="error-message">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- 成功メッセージ表示（退勤後は表示しない） -->
    @if(session('success') && (!$todayAttendance || $todayAttendance->status != \App\Models\Attendance::STATUS_CLOCKED_OUT))
        <div class="success-message">
            <p>{{ session('success') }}</p>
        </div>
    @endif
</div>

<script>
    // 現在日時を表示
    function updateDateTime() {
        const now = new Date();
        
        // 日付のフォーマット（例: 2025年11月8日）
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const day = now.getDate();
        const dateStr = `${year}年${month}月${day}日`;
        document.getElementById('current-date').textContent = dateStr;
        
        // 時刻のフォーマット（例: 14:30:45）
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeStr = `${hours}:${minutes}:${seconds}`;
        document.getElementById('current-time').textContent = timeStr;
    }

    // 初回実行
    updateDateTime();
    
    // 1秒ごとに更新
    setInterval(updateDateTime, 1000);
</script>
@endsection
