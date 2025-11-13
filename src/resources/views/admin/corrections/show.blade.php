@extends('layouts.admin')

@section('content')
<div class="attendance-detail-container">
    <h2 class="attendance-detail-title">勤怠詳細</h2>

    <div class="attendance-detail-card">
        <!-- 名前 -->
        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $correctionRequest->user->name }}</div>
        </div>

        <!-- 日付 -->
        <div class="detail-row">
            <div class="detail-label">日付</div>
            <div class="detail-value">
                {{ \Carbon\Carbon::parse($correctionRequest->attendance->date)->format('Y年') }}
                {{ \Carbon\Carbon::parse($correctionRequest->attendance->date)->format('n月j日') }}
            </div>
        </div>

        <!-- 出勤・退勤 -->
        <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value">
                {{ \Carbon\Carbon::parse($correctionRequest->clock_in)->format('H:i') }}
                ～
                {{ $correctionRequest->clock_out ? \Carbon\Carbon::parse($correctionRequest->clock_out)->format('H:i') : '' }}
            </div>
        </div>

        <!-- 休憩 -->
        @php
            $breakCorrections = $correctionRequest->breakCorrections->sortBy('break_start');
        @endphp
        
        @foreach($breakCorrections as $index => $breakCorrection)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index > 0 ? ($index + 1) : '' }}</div>
                <div class="detail-value">
                    @if($breakCorrection->break_start && $breakCorrection->break_end)
                        {{ \Carbon\Carbon::parse($breakCorrection->break_start)->format('H:i') }}
                        ～
                        {{ \Carbon\Carbon::parse($breakCorrection->break_end)->format('H:i') }}
                    @endif
                </div>
            </div>
        @endforeach

        <!-- 備考（申請理由） -->
        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">{{ $correctionRequest->note }}</div>
        </div>
    </div>

    <!-- 承認ボタン -->
    @if($correctionRequest->status === \App\Models\AttendanceCorrectionRequest::STATUS_PENDING)
        <div class="approve-button-container">
            <form method="POST" action="{{ route('admin.corrections.approve', $correctionRequest->id) }}">
                @csrf
                @method('PUT')
                <button type="submit" class="approve-button">承認</button>
            </form>
        </div>
    @else
        <div class="approved-message">
            この申請は既に承認済みです。
        </div>
    @endif
</div>
@endsection
