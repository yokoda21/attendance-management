@extends('layouts.app')

@section('title', '勤怠詳細 - COACHTECH')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h2>勤怠詳細</h2>    

    @php
    $hasPendingCorrection = $attendance->correctionRequests()->where('status', 'pending')->exists();
    @endphp

    <form action="{{ route('attendance-correction.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

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
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="clock_in" class="time-input {{ $errors->has('clock_in') || $errors->has('clock_out') ? 'error' : '' }}"
                            value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}"
                            {{ $hasPendingCorrection ? 'disabled' : 'required' }}>
                        <span class="separator">～</span>
                        <input type="time" name="clock_out" class="time-input {{ $errors->has('clock_in') || $errors->has('clock_out') ? 'error' : '' }}"
                            value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}"
                            {{ $hasPendingCorrection ? 'disabled' : 'required' }}>
                    </div>
                    @if($errors->has('clock_in') || $errors->has('clock_out'))
                    <div class="field-error-message">
                        {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- 休憩 -->
            @foreach($attendance->breaks as $index => $break)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="breaks[{{ $index }}][break_start]" class="time-input {{ $errors->has('breaks.'.$index.'.break_start') || $errors->has('breaks.'.$index.'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.$index.'.break_start', $break->break_start ? $break->break_start->format('H:i') : '') }}"
                            {{ $hasPendingCorrection ? 'disabled' : '' }}>
                        <span class="separator">～</span>
                        <input type="time" name="breaks[{{ $index }}][break_end]" class="time-input {{ $errors->has('breaks.'.$index.'.break_start') || $errors->has('breaks.'.$index.'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.$index.'.break_end', $break->break_end ? $break->break_end->format('H:i') : '') }}"
                            {{ $hasPendingCorrection ? 'disabled' : '' }}>
                        <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                    </div>
                    @if($errors->has('breaks.'.$index.'.break_start') || $errors->has('breaks.'.$index.'.break_end'))
                    <div class="field-error-message">
                        {{ $errors->first('breaks.'.$index.'.break_start') ?: $errors->first('breaks.'.$index.'.break_end') }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- 休憩追加用 -->
            <div class="detail-row">
                <div class="detail-label">休憩{{ count($attendance->breaks) + 1 }}</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="breaks[{{ count($attendance->breaks) }}][break_start]" class="time-input {{ $errors->has('breaks.'.count($attendance->breaks).'.break_start') || $errors->has('breaks.'.count($attendance->breaks).'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.count($attendance->breaks).'.break_start') }}"
                            {{ $hasPendingCorrection ? 'disabled' : '' }}>
                        <span class="separator">～</span>
                        <input type="time" name="breaks[{{ count($attendance->breaks) }}][break_end]" class="time-input {{ $errors->has('breaks.'.count($attendance->breaks).'.break_start') || $errors->has('breaks.'.count($attendance->breaks).'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.count($attendance->breaks).'.break_end') }}"
                            {{ $hasPendingCorrection ? 'disabled' : '' }}>
                    </div>
                    @if($errors->has('breaks.'.count($attendance->breaks).'.break_start') || $errors->has('breaks.'.count($attendance->breaks).'.break_end'))
                    <div class="field-error-message">
                        {{ $errors->first('breaks.'.count($attendance->breaks).'.break_start') ?: $errors->first('breaks.'.count($attendance->breaks).'.break_end') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- 備考 -->
            <div class="detail-row">
                <div class="detail-label">備考</div>
                <div class="detail-value">
                    <textarea name="note" class="note-area {{ $errors->has('note') ? 'error' : '' }}" {{ $hasPendingCorrection ? 'disabled' : 'required' }}>{{ old('note', $attendance->note ?? '') }}</textarea>
                    @if($errors->has('note'))
                    <div class="field-error-message">
                        {{ $errors->first('note') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 承認待ちメッセージ -->
        @if($hasPendingCorrection)
        <div class="pending-message">
            * 承認待ちのため修正はできません。
        </div>
        @else
        <!-- 修正ボタン -->
        <div class="button-container">
            <button type="submit" class="btn-correction">修正</button>
        </div>
        @endif
    </form>
</div>
@endsection