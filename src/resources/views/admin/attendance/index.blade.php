@extends('layouts.admin')

@section('title', '勤怠一覧 - 管理者')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-daily.css') }}">
@endsection

@section('content')
<div class="admin-container">
    <h2>{{ $targetDate->format('Y年n月j日') }}の勤怠</h2>
    
    <div class="date-selector">
        <a href="{{ route('admin.attendance.list', ['date' => $previousDate]) }}" class="btn-prev-date">
            <img src="{{ asset('images/arrow.png') }}" alt="前日" class="arrow-icon">
            前日
        </a>
        
        <div class="current-date-wrapper">
            <img src="{{ asset('images/calendar-icon.png') }}" alt="カレンダー" class="calendar-icon">
            <span class="current-date">{{ $targetDate->format('Y/m/d') }}</span>
        </div>
        
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="btn-next-date">
            翌日
            <img src="{{ asset('images/arrow.png') }}" alt="翌日" class="arrow-icon">
        </a>
    </div>
    
    <table class="admin-attendance-table">
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
                    <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="btn-detail">
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
