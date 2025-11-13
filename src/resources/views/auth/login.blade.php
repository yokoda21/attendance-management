<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
</head>
<body>
    <header>
        <h1>COACHTECH</h1>
    </header>

    <main>
        <div>
            <h2>ログイン</h2>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <!-- メールアドレス -->
                <div>
                    <label for="email">メールアドレス</label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                    >
                    @error('email')
                        <p>{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード -->
                <div>
                    <label for="password">パスワード</label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required
                    >
                    @error('password')
                        <p>{{ $message }}</p>
                    @enderror
                </div>

                <!-- ログインボタン -->
                <div>
                    <button type="submit">ログインする</button>
                </div>

                <!-- 会員登録リンク -->
                <div>
                    <a href="{{ route('register') }}">会員登録はこちら</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
