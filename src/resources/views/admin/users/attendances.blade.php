@extends('layouts.admin')

@section('title', $user->name . 'さんの勤怠 - 管理者')

@section('content')
<div class="user-attendance-list-container">
    <h2>{{ $user->name }}さんの勤怠</h2>
    
    <div class="month-navigation">
        <a href="{{ route('admin.users.attendances', ['user_id' => $user->id, 'month' => $previousMonth]) }}" class="nav-button">
            ← 前月
        </a>
        
        <div class="current-month">
            <span class="calendar-icon">📅</span>
            <span class="month-text">{{ $targetMonth->format('Y/m') }}</span>
        </div>
        
        <a href="{{ route('admin.users.attendances', ['user_id' => $user->id, 'month' => $nextMonth]) }}" class="nav-button">
            翌月 →
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
                    $date = $targetMonth->copy()->day($day);
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
                            <a href="{{ route('admin.attendances.show', $attendance->id) }}" class="detail-link">
                                詳細
                            </a>
                        </td>
                    @else
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <span class="detail-link disabled">詳細</span>
                        </td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>
    
    <div class="csv-export-container">
        <a href="{{ route('admin.users.attendances.csv', ['user_id' => $user->id, 'month' => $targetMonth->format('Y-m')]) }}" class="csv-button">
            CSV出力
        </a>
    </div>
</div>
@endsection
