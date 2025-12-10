@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-user-attendance.css') }}">
@endsection

@section('content')
<div class="user-attendance-list-container">
    <h2>{{ $user->name }}さんの勤怠</h2>

    <div class="date-selector">
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $previousMonth]) }}" class="btn-prev-date">
            <img src="{{ asset('images/arrow.png') }}" alt="前月" class="arrow-icon">
            前月
        </a>

        <div class="current-date-wrapper">
            <img src="{{ asset('images/calendar-icon.png') }}" alt="カレンダー" class="calendar-icon">
            <span class="current-date">{{ $targetMonth->format('Y/m') }}</span>
        </div>

        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="btn-next-date">
            翌月
            <img src="{{ asset('images/arrow.png') }}" alt="翌月" class="arrow-icon arrow-right">
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$attendance->date->dayOfWeek] }})</td>
                <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                <td>{{ $attendance->total_break ?: '' }}</td>
                <td>{{ $attendance->total_work ?: '' }}</td>
                <td>
                    @if($attendance->id)
                    <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="btn-detail">詳細</a>
                    @else
                    <a href="{{ route('admin.attendance.show', ['id' => $attendance->date->format('Y-m-d'), 'user_id' => $user->id]) }}" class="btn-detail">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="csv-export-container">
        <form action="{{ route('admin.attendance.staff.csv', ['id' => $user->id]) }}" method="GET">
            <input type="hidden" name="month" value="{{ $targetMonth->format('Y-m') }}">
            <button type="submit" class="csv-button">CSV出力</button>
        </form>
    </div>
</div>
@endsection
