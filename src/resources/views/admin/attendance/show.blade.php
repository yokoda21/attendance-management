@extends('layouts.admin')

@section('title', '勤怠詳細 - 管理者')

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
    <div class="error-message">
        <p>承認待ちのため修正はできません。</p>
    </div>
    
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
            <div class="detail-value time-range">
                <span class="time">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span>
                <span class="separator">～</span>
                <span class="time">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
            </div>
        </div>
        
        @foreach($attendance->breaks as $index => $break)
        <div class="detail-row">
            <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
            <div class="detail-value time-range">
                <span class="time">{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</span>
                <span class="separator">～</span>
                <span class="time">{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
            </div>
        </div>
        @endforeach
        
        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
                <div class="note-area"></div>
            </div>
        </div>
    </div>
    
    @else
    <!-- 承認待ちでない場合は編集可能（FN040） -->
    <form action="{{ route('admin.attendances.update', $attendance->id) }}" method="POST">
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
                <div class="detail-value time-range">
                    <input type="time" name="clock_in" class="time-input" 
                           value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" 
                           required>
                    <span class="separator">～</span>
                    <input type="time" name="clock_out" class="time-input" 
                           value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" 
                           required>
                </div>
                @error('clock_in')
                <div class="error-text">{{ $message }}</div>
                @enderror
                @error('clock_out')
                <div class="error-text">{{ $message }}</div>
                @enderror
            </div>
            
            @foreach($attendance->breaks as $index => $break)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                <div class="detail-value time-range">
                    <input type="time" name="breaks[{{ $index }}][break_start]" class="time-input" 
                           value="{{ old('breaks.'.$index.'.break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                    <span class="separator">～</span>
                    <input type="time" name="breaks[{{ $index }}][break_end]" class="time-input" 
                           value="{{ old('breaks.'.$index.'.break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                </div>
                @error('breaks.'.$index.'.break_start')
                <div class="error-text">{{ $message }}</div>
                @enderror
                @error('breaks.'.$index.'.break_end')
                <div class="error-text">{{ $message }}</div>
                @enderror
            </div>
            @endforeach
            
            @php
                $breakCount = $attendance->breaks->count();
            @endphp
            
            @if($breakCount < 3)
            @for($i = $breakCount; $i < 3; $i++)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $i > 0 ? $i + 1 : ($i === 0 ? '' : '2') }}</div>
                <div class="detail-value time-range">
                    <input type="time" name="breaks[{{ $i }}][break_start]" class="time-input" 
                           value="{{ old('breaks.'.$i.'.break_start') }}">
                    <span class="separator">～</span>
                    <input type="time" name="breaks[{{ $i }}][break_end]" class="time-input" 
                           value="{{ old('breaks.'.$i.'.break_end') }}">
                </div>
            </div>
            @endfor
            @endif
            
            <div class="detail-row">
                <div class="detail-label">備考</div>
                <div class="detail-value">
                    <textarea name="note" class="note-textarea" placeholder="備考を入力してください">{{ old('note') }}</textarea>
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
