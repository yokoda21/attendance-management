@extends('layouts.admin')

@section('title', $user->name . 'さんの勤怠 - 管理者')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-user-attendance.css') }}">
@endsection

@section('content')
<div class="user-attendance-list-container">
    <h2>{{ $user->name }}さんの勤怠</h2>

    <div class="date-selector">
        <a href="{{ route('admin.attendance.staff', ['user_id' => $user->id]) }}" class=" btn-prev-date">
            <img src="{{ asset('images/arrow.png') }}" alt="前月" class="arrow-icon">
            前月
        </a>

        <div class="current-date-wrapper">
            <img src="{{ asset('images/calendar-icon.png') }}" alt="カレンダー" class="calendar-icon">
            <span class="current-date">{{ $targetMonth->format('Y/m') }}</span>
        </div>

        <a href="{{ route('admin.attendance.staff', ['user_id' => $user->id, 'month' => $nextMonth]) }}" class="btn-next-date">
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
            @php
            // 月の全日付を生成
            $daysInMonth = $targetMonth->daysInMonth;
            $attendancesByDate = $attendances->keyBy(function($item) {
            return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            });
            @endphp

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                $date=$targetMonth->copy()->day($day);
                $dateStr = $date->format('Y-m-d');
                $attendance = $attendancesByDate->get($dateStr);

                // 曜日を取得
                $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
                @endphp
                <tr>
                    <td>{{ $date->format('m/d') }}({{ $dayOfWeek }})</td>
                    @if($attendance)
                    <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->total_break }}</td>
                    <td>{{ $attendance->total_work }}</td>
                    <td>
                        <a href="{{ route('admin.attendances.show', ['id' => $attendance->id]) }}" class="btn-detail">
                            詳細
                        </a>
                    </td>
                    @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <span class="btn-detail disabled">詳細</span>
                    </td>
                    @endif
                </tr>
                @endfor
        </tbody>
    </table>

    <div class="csv-export-container">
        <a href="{{route('admin.attendance.staff.csv',  ['user_id' => $user->id, 'month' => $targetMonth->format('Y-m')]) }}" class="csv-button">
            CSV出力
        </a>
    </div>
</div>
@endsection