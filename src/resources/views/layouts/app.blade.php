<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('styles')
</head>

<body>
    <header>
        <div class="header-container">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header-logo">
            <nav>
                <ul>
                    @if(isset($todayAttendance) && $todayAttendance && $todayAttendance->status == \App\Models\Attendance::STATUS_CLOCKED_OUT)
                    <!-- 退勤後のナビゲーション -->
                    <li><a href="{{ route('attendance.list') }}">今月の出勤一覧</a></li>
                    <li><a href="{{ route('attendance-correction.index') }}">申請一覧</a></li>
                    @else
                    <!-- 通常のナビゲーション -->
                    <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                    <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('attendance-correction.index') }}">申請</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>