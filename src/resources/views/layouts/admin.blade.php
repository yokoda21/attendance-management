<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理者 - COACHTECH')</title>

    <!-- CSS読み込み -->
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('styles')

</head>

<body>
    <header>
        <div class="header-container">
            <h1>
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" style="height: 36px;">
            </h1>
            <nav>
                <ul>
                    <li><a href="{{ route('admin.attendances.index') }}">勤怠一覧</a></li>
                    <li><a href="{{ route('admin.users.index') }}">スタッフ一覧</a></li>
                    <li><a href="{{ route('admin.corrections.index') }}">申請一覧</a></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
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