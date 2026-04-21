# HTTP Basic認証 実装方針（Issue #1）

対象Issue: `feat: デモ環境にHTTP Basic認証ミドルウェアを追加する`（#1）

## 1. 目的

- 面接デモ用の公開URLに最低限のアクセス制御をかける
- 本番環境のみ保護し、ローカル開発体験は維持する

## 2. 方針

- Laravel標準の `auth.basic` は使わず、カスタムミドルウェアを実装する
- 認証情報は環境変数で管理する
- ミドルウェアはルートに適用し、対象画面を保護する

### この方針にする理由

- `auth.basic` は `users` テーブルとログイン前提の認証基盤に寄るため、MVP要件に対して過剰
- 今回は「簡易な公開保護」が目的なので、固定ID/PWのHTTP Basicが最短

## 3. 実装スコープ

### 3-1. 追加ファイル

- `app/Http/Middleware/MiddlewareAuth.php`
- `config/middleware_auth.php`

### 3-2. 変更ファイル

- `bootstrap/app.php`（middleware alias登録 + webグループへ追加）
- `.env.example`（必要な環境変数を追加）
- `README.md`（デモアクセス手順を追記）
- `docs/laravel_cloud_predeploy.md`（Cloud設定手順を追記）

## 4. 環境変数設計

以下を利用する。

- `MIDDLEWARE_AUTH_ENABLED`
- `MIDDLEWARE_AUTH_USER`
- `MIDDLEWARE_AUTH_PASSWORD`
- `MIDDLEWARE_AUTH_REALM`

推奨:

- ローカル: `MIDDLEWARE_AUTH_ENABLED=false`
- 本番: `MIDDLEWARE_AUTH_ENABLED=true`

## 5. ミドルウェア仕様

- `MIDDLEWARE_AUTH_ENABLED=false` の場合は何もせず通過
- 有効時、`PHP_AUTH_USER` / `PHP_AUTH_PW` を取得
- 設定値との照合に失敗したら `401 Unauthorized` を返却
- レスポンスヘッダに `WWW-Authenticate: Basic realm=...` を付与

## 6. ルーティング適用方針

- `bootstrap/app.php` で `MiddlewareAuth` を web ミドルウェアグループに追加する
- これにより `routes/web.php` 配下のページへ自動適用される

## 7. Laravel Cloud 反映方針

`Settings > Environment Variables` で `Custom environment variables` に以下を設定する。

- `MIDDLEWARE_AUTH_ENABLED=true`
- `MIDDLEWARE_AUTH_USER=<demo_user>`
- `MIDDLEWARE_AUTH_PASSWORD=<demo_password>`
- `MIDDLEWARE_AUTH_REALM=Warifull Demo`

## 8. 動作確認チェックリスト

1. ローカル（`MIDDLEWARE_AUTH_ENABLED=false`）
- 認証ダイアログが出ないこと

2. ローカル（`MIDDLEWARE_AUTH_ENABLED=true`）
- 未認証アクセス時に認証ダイアログが出ること
- 正しいID/PWで画面表示できること
- 間違ったID/PWでは表示できないこと

3. Cloud（本番）
- 公開URLアクセス時に認証ダイアログが出ること
- 面接用アカウントでログインできること

## 9. 完了条件（Issue #1）

- 本番環境でHTTP Basic認証が有効
- 環境変数でユーザー名/パスワードを管理
- READMEまたはdocsにデモアクセス手順が記載されている

## 10. 参考（一次ソース）

- Laravel Middleware: https://laravel.com/docs/13.x/middleware
- Laravel Authentication（HTTP Basic）: https://laravel.com/docs/13.x/authentication#http-basic-authentication
- Laravel Configuration（envの扱い）: https://laravel.com/docs/13.x/configuration#environment-configuration

## 11. 実装手順

補足:
- `BASIC_AUTH_*` は Laravel 標準の認証命名（`auth` / `auth.basic`）と命名が近く紛らわしいため、この実装では `MIDDLEWARE_AUTH_*` を使用する。

1. `config/middleware_auth.php` を作成する
- `MIDDLEWARE_AUTH_ENABLED` / `MIDDLEWARE_AUTH_USER` / `MIDDLEWARE_AUTH_PASSWORD` / `MIDDLEWARE_AUTH_REALM` を定義する

2. `app/Http/Middleware/MiddlewareAuth.php` を作成する
- `enabled=false` なら素通し
- 有効時は `PHP_AUTH_USER` / `PHP_AUTH_PW` を照合
- 不一致なら `401` と `WWW-Authenticate` ヘッダを返す

3. `bootstrap/app.php` で middleware alias を登録する
- 例: `'middleware.auth' => \App\Http\Middleware\MiddlewareAuth::class`
- あわせて web ミドルウェアグループへ `MiddlewareAuth` を追加する

4. `routes/web.php` に適用する
- 個別ルートへの指定は不要（webグループで自動適用）

5. `.env.example` を更新する
- `MIDDLEWARE_AUTH_ENABLED=false`
- `MIDDLEWARE_AUTH_USER=`
- `MIDDLEWARE_AUTH_PASSWORD=`
- `MIDDLEWARE_AUTH_REALM="Warifull Demo"`

6. ローカルで動作確認する
- `MIDDLEWARE_AUTH_ENABLED=true` で認証ダイアログが出る
- 正しいID/PWで通る
- 間違ったID/PWでは通らない

7. ドキュメントを更新する
- `docs/laravel_cloud_predeploy.md` に Cloud 側の `MIDDLEWARE_AUTH_*` 設定手順を追記
- `README.md` にデモ時のアクセス方法を追記
