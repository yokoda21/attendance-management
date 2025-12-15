# 勤怠管理アプリ

## 概要
従業員の勤怠情報を管理するWebアプリケーションです。  
従業員は一般ユーザーとして会員登録でき、出勤・退勤・休憩の打刻、勤怠履歴の確認、修正が可能。
管理者は、勤怠管理・承認機能を備えています。
メール認証にはMailtrapというツールを使用しています。


---

## 使用技術

| カテゴリ | 技術 |
|---------|------|
| **バックエンド** | Laravel 8.83.8 |
| **言語** | PHP 8.1.33 |
| **データベース** | MySQL 8.0.26 |
| **インフラ** | Docker / Docker Compose |
| **認証** | Laravel Fortify |
| **テスト** | PHPUnit |
| **メール** | Mailtrap（開発環境） |

---

## 主な機能

### 一般ユーザー機能
-  会員登録・ログイン（メール認証機能付き）
-  出勤・退勤・休憩の打刻
-  勤怠一覧表示（月次）
-  勤怠詳細表示
-  勤怠修正申請
-  修正申請一覧表示（承認待ち・承認済み）

### 管理者機能
-  管理者ログイン
-  日次勤怠一覧表示
-  スタッフ一覧表示
-  スタッフ別勤怠一覧表示
-  勤怠情報の修正
-  修正申請の承認
-  CSV出力機能

---

## 環境構築

### 必要な環境
- Docker
- Docker Compose
- Git

### セットアップ手順

#### 1. リポジトリのクローン
```bash
git clone git@github.com:yokoda21/attendance-management.git
cd attendance-management
```

#### 2. Dockerコンテナの起動
```bash
docker-compose up -d --build
```
**注意**: MySQLはOSによって起動しない場合があります。その場合は`docker-compose.yml`を編集してください。

#### 3. PHPコンテナに入る
```bash
docker-compose exec php bash
```

#### 4. Composerパッケージのインストール
```bash
composer install
```
#### 5. 環境変数の設定
`.env.example`を`.env`にコピー：
```bash
cp .env.example .env
```

または、新しく`.env`ファイルを作成し、以下を記述：
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

#### 6. メール設定（Mailtrap）
Mailtrapというツールを使用しています。

1. 以下のリンクから会員登録：  
   https://mailtrap.io/

2. メールボックスのIntegrationsから「Laravel 7.x and 8.x」を選択

3. `.env`ファイルの`MAIL_MAILER`から`MAIL_ENCRYPTION`までの項目をコピー＆ペースト

4. `MAIL_FROM_ADDRESS`に任意のメールアドレスを入力

#### 7. アプリケーションキーの生成
```bash
docker-compose exec php php artisan key:generate

#### マイグレーションの実行
```bash
docker-compose exec php php artisan migrate
```

#### シーディング
テストデータを投入する場合：
```bash
docker-compose exec php php artisan db:seed
```

---

## アクセス方法

### アプリケーション
```
http://localhost
```

### phpMyAdmin
```
http://localhost:8080
```

---

## テスト

### テスト実行
```bash
# 全テストの実行
docker-compose exec php php artisan test

# または
docker-compose exec php ./vendor/bin/phpunit

# 詳細出力で実行
docker-compose exec php php artisan test --verbose

# 特定のテストファイルのみ実行
docker-compose exec php php artisan test --filter=UserRegistrationTest
```

### テストアカウント
# 動作確認用のテストアカウントは、シーダー実行時に自動作成されます。

一般ユーザー(10名いますが、一人のみ記載しています)
name: 山田花子
email: yamada@example.com
パスワード: password123

管理者
name: 管理者太郎
email: admin@example.com
パスワード: password123


#### 1. 一般ユーザー会員登録
1. トップページにアクセス
2. 「会員登録」ボタンをクリック
3. 名前・メールアドレス・パスワードを入力
4. 登録確認メールを受信
5. メール内の認証リンクをクリック
6. ログイン画面にリダイレクト

#### 2. ログイン
1. メールアドレスとパスワードを入力
2. 打刻画面にリダイレクト

#### 3. 出勤・退勤・休憩
1. 打刻画面で該当するボタンをクリック
   - **出勤**: 1日1回のみ
   - **休憩入/休憩戻**: 複数回可能
   - **退勤**: 1日1回のみ

#### 4. 勤怠一覧・詳細
1. 「勤怠一覧」メニューをクリック
2. 月次で勤怠情報を確認
3. 「詳細」ボタンで詳細情報を表示

#### 5. 修正申請
1. 勤怠詳細画面で修正したい項目を入力
2. 「修正する」ボタンをクリック
3. 管理者の承認を待つ

---

### 管理者

#### 1. ログイン
```
URL: http://localhost/admin/login
```

#### 2. 日次勤怠一覧
- 全スタッフの勤怠情報を日次で確認
- 前日・翌日ボタンで日付を移動

#### 3. スタッフ一覧
- 全スタッフの一覧を表示
- スタッフ名をクリックで個別の勤怠一覧へ

#### 4. 勤怠修正
1. スタッフの勤怠詳細画面を表示
2. 修正したい項目を入力
3. 「更新する」ボタンをクリック

#### 5. 修正申請の承認
1. 修正申請一覧を表示
2. 申請詳細を確認
3. 「承認する」ボタンをクリック

#### 6. CSV出力
1. スタッフ別勤怠一覧画面を表示
2. 「CSV出力」ボタンをクリック
3. ダウンロードされたCSVファイルを確認

---
