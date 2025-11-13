@extends('layouts.app')

@section('title', '勤怠詳細 - COACHTECH')

@section('content')
<div class="attendance-detail-container">
    <h2>勤怠詳細</h2>

    <div class="detail-card">
        <!-- 名前 -->
        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $attendance->user->name }}</div>
        </div>

        <!-- 日付 -->
        <div class="detail-row">
            <div class="detail-label">日付</div>
            <div class="detail-value">
                {{ $attendance->date->format('Y年') }}
                {{ $attendance->date->format('n月j日') }}
            </div>
        </div>

        <!-- 出勤・退勤 -->
        <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value time-range">
                <span class="time-input">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</span>
                <span class="separator">～</span>
                <span class="time-input">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</span>
            </div>
        </div>

        <!-- 休憩 -->
        @foreach($attendance->breaks as $index => $break)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                <div class="detail-value time-range">
                    <span class="time-input">{{ $break->break_start ? $break->break_start->format('H:i') : '' }}</span>
                    <span class="separator">～</span>
                    <span class="time-input">{{ $break->break_end ? $break->break_end->format('H:i') : '' }}</span>
                </div>
            </div>
        @endforeach

        <!-- 備考 -->
        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
                <div class="note-area">{{ $attendance->note ?? '' }}</div>
            </div>
        </div>
    </div>

    <!-- 修正ボタン -->
    <div class="button-container">
        <a href="{{ route('attendance-correction.create', ['attendance_id' => $attendance->id]) }}" class="btn-correction">修正</a>
    </div>
</div>
@endsection
