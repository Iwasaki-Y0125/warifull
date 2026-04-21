# ワリフル Laravel Cloud 先行デプロイ（最小手順）

面接デモ用に公開URLを先に作るための最低限手順です。

## 1. GitHubへpush

```bash
git add .
git commit -m "chore: bootstrap laravel app"
git push -u origin main
```

## 2. Laravel Cloud で作成

1. `New Project` で `warifull` リポジトリを選ぶ
2. Branch は `main`
3. Region は任意（例: Singapore）
4. Database は Postgres を1つ追加

## 3. 環境変数を確認

`Settings` → `Environment Variables` で `Injected variables` を確認。

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY`
- `DB_CONNECTION=pgsql`
- DB接続情報

不足がある時だけ `Custom environment variables` に追加。

HTTP Basic認証を使う場合は、`Custom environment variables` に以下も追加する。

- `MIDDLEWARE_AUTH_ENABLED=true`
- `MIDDLEWARE_AUTH_USER=<demo_user>`
- `MIDDLEWARE_AUTH_PASSWORD=<demo_password>`
- `MIDDLEWARE_AUTH_REALM="Warifull Demo"`

## 4. デプロイ

- `Deploy` 実行
- 公開URL（`*.free.laravel.cloud`）で初期画面を確認

## 5. デプロイ後コマンド

`Commands` で以下のみ実行。

```bash
php artisan migrate --force
```

- `Nothing to migrate.` でも正常
