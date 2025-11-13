<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>COACHTECH</h1>
            <nav>
                <ul>
                    <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                    <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('attendance-correction.index') }}">申請</a></li>
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
