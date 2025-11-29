@extends('layouts.auth')

@section('title', 'ログイン - COACHTECH')

@section('content')
<div class="auth-form-container">
    <h2 class="auth-form-title">ログイン</h2>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" id="password" name="password" class="form-input" required>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="form-button">ログインする</button>
    </form>

    <a href="{{ route('register') }}" class="form-link">会員登録はこちら</a>
</div>
@endsection