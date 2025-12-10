<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>

<body>
    <header class="auth-header">
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="header-logo">
    </header>

    <main class="auth-main">
        @yield('content')
    </main>
</body>

</html>