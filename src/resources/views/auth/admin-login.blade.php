<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>
</head>
<body>
    <header>
        <h1>COACHTECH</h1>
    </header>

    <main>
        <div>
            <h2>管理者ログイン</h2>

            <form method="POST" action="{{ route('admin.login') }}" novalidate>
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

                <!-- 管理者ログインボタン -->
                <div>
                    <button type="submit">管理者ログインする</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
