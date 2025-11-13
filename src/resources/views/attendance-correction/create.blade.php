@extends('layouts.app')

@section('title', '勤怠修正申請 - COACHTECH')

@section('content')
<div class="attendance-correction-create-container">
    <h2>勤怠修正申請</h2>

    @if($hasPendingRequest)
        <div class="error-message">
            <p>承認待ちのため修正はできません。</p>
        </div>
        
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

        <div class="button-container">
            <a href="{{ route('attendance.list') }}" class="btn-back">戻る</a>
        </div>
    @else
        @if(session('error'))
            <div class="error-message">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('attendance-correction.store') }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

            <div class="correction-card">
                <!-- 名前 -->
                <div class="form-row">
                    <div class="form-label">名前</div>
                    <div class="form-value">{{ $attendance->user->name }}</div>
                </div>

                <!-- 日付 -->
                <div class="form-row">
                    <div class="form-label">日付</div>
                    <div class="form-value">
                        {{ $attendance->date->format('Y年') }}
                        {{ $attendance->date->format('n月j日') }}
                    </div>
                </div>

                <!-- 出勤・退勤 -->
                <div class="form-row">
                    <div class="form-label">出勤・退勤</div>
                    <div class="form-value time-range">
                        <input type="time" name="clock_in" 
                               value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}" 
                               class="time-input" required>
                        <span class="separator">～</span>
                        <input type="time" name="clock_out" 
                               value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}" 
                               class="time-input" required>
                    </div>
                </div>

                <!-- 休憩 -->
                @foreach($attendance->breaks as $index => $break)
                    <div class="form-row">
                        <div class="form-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                        <div class="form-value time-range">
                            <input type="time" name="breaks[{{ $index }}][break_start]" 
                                   value="{{ old('breaks.'.$index.'.break_start', $break->break_start ? $break->break_start->format('H:i') : '') }}" 
                                   class="time-input">
                            <span class="separator">～</span>
                            <input type="time" name="breaks[{{ $index }}][break_end]" 
                                   value="{{ old('breaks.'.$index.'.break_end', $break->break_end ? $break->break_end->format('H:i') : '') }}" 
                                   class="time-input">
                        </div>
                    </div>
                @endforeach

                <!-- 休憩2（追加用） -->
                <div class="form-row">
                    <div class="form-label">休憩{{ count($attendance->breaks) + 1 }}</div>
                    <div class="form-value time-range">
                        <input type="time" name="breaks[{{ count($attendance->breaks) }}][break_start]" 
                               value="{{ old('breaks.'.count($attendance->breaks).'.break_start') }}" 
                               class="time-input">
                        <span class="separator">～</span>
                        <input type="time" name="breaks[{{ count($attendance->breaks) }}][break_end]" 
                               value="{{ old('breaks.'.count($attendance->breaks).'.break_end') }}" 
                               class="time-input">
                    </div>
                </div>

                <!-- 備考 -->
                <div class="form-row">
                    <div class="form-label">備考</div>
                    <div class="form-value">
                        <textarea name="note" class="note-textarea" rows="5" required>{{ old('note', $attendance->note ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 修正ボタン -->
            <div class="button-container">
                <button type="submit" class="btn-submit">修正</button>
            </div>
        </form>
    @endif
</div>
@endsection
