@extends('layouts.admin')

@section('title', '勤怠詳細 - 管理者')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h2>勤怠詳細</h2>

    @if(session('success'))
    <div class="success-message">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="error-message">
        {{ session('error') }}
    </div>
    @endif

    @if($hasPendingRequest)
    <!-- 承認待ちの場合は編集不可（FN038） -->

    <div class="attendance-detail-card">
        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $attendance->user->name }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">日付</div>
            <div class="detail-value">
                {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                <span class="date-spacer"></span>
                {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value">
                <div class="time-range">
                    <span class="time">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span>
                    <span class="separator">～</span>
                    <span class="time">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
                </div>
            </div>
        </div>

        @foreach($attendance->breaks as $index => $break)
        <div class="detail-row">
            <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
            <div class="detail-value">
                <div class="time-range">
                    <span class="time">{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</span>
                    <span class="separator">～</span>
                    <span class="time">{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
                </div>
            </div>
        </div>
        @endforeach

        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
                <div class="note-area">{{ $attendance->note ?? '' }}</div>
            </div>
        </div>

    </div>

    <!-- 承認待ちメッセージ -->
    @if($hasPendingRequest)
    <div class="pending-message">
        * 承認待ちのため修正はできません。
    </div>
    @endif

    @else
    <!-- 承認待ちでない場合は編集可能（FN040） -->
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" novalidate>
        @csrf
        @method('PUT')


        <div class="attendance-detail-card">
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">{{ $attendance->user->name }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                    <span class="date-spacer"></span>
                    {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="clock_in" class="time-input {{ $errors->has('clock_in') || $errors->has('clock_out') ? 'error' : '' }}"
                            value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                            required>
                        <span class="separator">～</span>
                        <input type="time" name="clock_out" class="time-input {{ $errors->has('clock_in') || $errors->has('clock_out') ? 'error' : '' }}"
                            value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                            required>
                    </div>
                    @if($errors->has('clock_in') || $errors->has('clock_out'))
                    <div class="error-text">
                        {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
                    </div>
                    @endif
                </div>
            </div>

            @foreach($attendance->breaks as $index => $break)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="breaks[{{ $index }}][break_start]" class="time-input {{ $errors->has('breaks.'.$index.'.break_start') || $errors->has('breaks.'.$index.'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.$index.'.break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                        <span class="separator">～</span>
                        <input type="time" name="breaks[{{ $index }}][break_end]" class="time-input {{ $errors->has('breaks.'.$index.'.break_start') || $errors->has('breaks.'.$index.'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.$index.'.break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                    </div>
                    @if($errors->has('breaks.'.$index.'.break_start') || $errors->has('breaks.'.$index.'.break_end'))
                    <div class="error-text">
                        {{ $errors->first('breaks.'.$index.'.break_start') ?: $errors->first('breaks.'.$index.'.break_end') }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- 追加の入力フィールド（休憩回数 + 1） -->
            @php
            $breakCount = $attendance->breaks->count();
            @endphp

            <div class="detail-row">
                <div class="detail-label">休憩{{ $breakCount > 0 ? $breakCount + 1 : '' }}</div>
                <div class="detail-value">
                    <div class="time-range">
                        <input type="time" name="breaks[{{ $breakCount }}][break_start]" class="time-input {{ $errors->has('breaks.'.$breakCount.'.break_start') || $errors->has('breaks.'.$breakCount.'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.$breakCount.'.break_start') }}">
                        <span class="separator">～</span>
                        <input type="time" name="breaks[{{ $breakCount }}][break_end]" class="time-input {{ $errors->has('breaks.'.$breakCount.'.break_start') || $errors->has('breaks.'.$breakCount.'.break_end') ? 'error' : '' }}"
                            value="{{ old('breaks.'.$breakCount.'.break_end') }}">
                    </div>
                    @if($errors->has('breaks.'.$breakCount.'.break_start') || $errors->has('breaks.'.$breakCount.'.break_end'))
                    <div class="error-text">
                        {{ $errors->first('breaks.'.$breakCount.'.break_start') ?: $errors->first('breaks.'.$breakCount.'.break_end') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- 備考 -->
            <div class="detail-row">
                <div class="detail-label">備考</div>
                <div class="detail-value">
                    <textarea name="note" class="note-textarea {{ $errors->has('note') ? 'error' : '' }}"
                        rows="4">{{ old('note', $attendance->note) }}</textarea>
                    @if($errors->has('note'))
                    <div class="error-text">
                        {{ $errors->first('note') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="button-container">
            <button type="submit" class="correction-button">
                修正
            </button>
        </div>

    </form>
    @endif
</div>
@endsection