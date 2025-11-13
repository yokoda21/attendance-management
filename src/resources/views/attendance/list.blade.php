@extends('layouts.app')

@section('title', '勤怠一覧 - COACHTECH')

@section('content')
<div class="attendance-list-container">
    <h2>勤怠一覧</h2>

    <!-- 月選択 -->
    <div class="month-selector">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="btn-prev-month">← 前月</a>
        <span class="current-month">{{ $date->format('Y/m') }}</span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="btn-next-month">翌月 →</a>
    </div>

    <!-- 勤怠一覧テーブル -->
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
            @foreach($days as $day)
                <tr>
                    <!-- 日付 -->
                    <td>{{ $day['date']->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$day['date']->dayOfWeek] }})</td>
                    
                    @if($day['attendance'])
                        <!-- 出勤 -->
                        <td>{{ $day['attendance']->clock_in ? $day['attendance']->clock_in->format('H:i') : '' }}</td>
                        
                        <!-- 退勤 -->
                        <td>{{ $day['attendance']->clock_out ? $day['attendance']->clock_out->format('H:i') : '' }}</td>
                        
                        <!-- 休憩 -->
                        <td>
                            @php
                                $totalBreakMinutes = 0;
                                foreach ($day['attendance']->breaks as $break) {
                                    if ($break->break_start && $break->break_end) {
                                        $totalBreakMinutes += $break->break_start->diffInMinutes($break->break_end);
                                    }
                                }
                                $breakHours = floor($totalBreakMinutes / 60);
                                $breakMinutes = $totalBreakMinutes % 60;
                            @endphp
                            @if($totalBreakMinutes > 0)
                                {{ $breakHours }}:{{ str_pad($breakMinutes, 2, '0', STR_PAD_LEFT) }}
                            @endif
                        </td>
                        
                        <!-- 合計 -->
                        <td>
                            @if($day['attendance']->clock_in && $day['attendance']->clock_out)
                                @php
                                    $totalWorkMinutes = $day['attendance']->clock_in->diffInMinutes($day['attendance']->clock_out);
                                    $workMinutes = $totalWorkMinutes - $totalBreakMinutes;
                                    $workHours = floor($workMinutes / 60);
                                    $workMinutesRemainder = $workMinutes % 60;
                                @endphp
                                {{ $workHours }}:{{ str_pad($workMinutesRemainder, 2, '0', STR_PAD_LEFT) }}
                            @endif
                        </td>
                        
                        <!-- 詳細 -->
                        <td>
                            <a href="{{ route('attendance.detail', ['id' => $day['attendance']->id]) }}" class="btn-detail">詳細</a>
                        </td>
                    @else
                        <!-- 勤務データがない場合 -->
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
