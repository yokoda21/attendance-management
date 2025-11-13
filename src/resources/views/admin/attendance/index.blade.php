@extends('layouts.admin')

@section('title', '勤怠一覧 - 管理者')

@section('content')
<div class="attendance-list-container">
    <h2>{{ $targetDate->format('Y年n月j日') }}の勤怠</h2>
    
    <div class="date-navigation">
        <a href="{{ route('admin.attendances.index', ['date' => $previousDate]) }}" class="nav-button">
            ← 前日
        </a>
        
        <div class="current-date">
            <span class="calendar-icon">📅</span>
            <span class="date-text">{{ $targetDate->format('Y/m/d') }}</span>
        </div>
        
        <a href="{{ route('admin.attendances.index', ['date' => $nextDate]) }}" class="nav-button">
            翌日 →
        </a>
    </div>
    
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->total_break ?? '-' }}</td>
                <td>{{ $attendance->total_work ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.attendances.show', $attendance->id) }}" class="detail-link">
                        詳細
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="no-data">勤怠データがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
