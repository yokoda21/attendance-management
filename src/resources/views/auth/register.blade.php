<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録 - COACHTECH</title>
</head>
<body>
    <header>
        <h1>COACHTECH</h1>
    </header>

    <main>
        <div class="register-container">
            <h2>会員登録</h2>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <!-- 名前 -->
                <div class="form-group">
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        placeholder="名前"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="メールアドレス"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード -->
                <div class="form-group">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="パスワード"
                        required
                    >
                    @error('password')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード確認 -->
                <div class="form-group">
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        placeholder="確認用パスワード"
                        required
                    >
                </div>

                <!-- 登録ボタン -->
                <div class="form-group">
                    <button type="submit" class="btn-submit">登録する</button>
                </div>
            </form>

            <!-- ログインリンク -->
            <div class="link-container">
                <p>アカウントをお持ちの方はこちらから</p>
                <a href="{{ route('login') }}">ログインはこちら</a>
            </div>
        </div>
    </main>
</body>
</html>
