# ワリフル Laravel Cloud 先行デプロイ手順（MVP）

このドキュメントは、実装途中でも先に公開URLを確保して、
デモ準備を早めに進めるための手順です。

環境構築手順は [docs/laravel_setup_and_predeploy.md](./laravel_setup_and_predeploy.md) を参照してください。

## 0. この手順のゴール

- GitHub連携済みの Laravel プロジェクトを Laravel Cloud に先行デプロイできる
- 公開URLでアプリが表示される
- 今後の実装で継続的にデプロイ確認できる

## 1. 先にデプロイを通す（Laravel Cloud）

実装途中でも、まず「空でも動くデプロイ」を先に通します。

### 1-1. GitHubにpush

```bash
git add .
git commit -m "chore: bootstrap Laravel app with Sail and pgsql"
git push -u origin main
```

## 2. Laravel Cloudで新規プロジェクト作成

1. Laravel Cloudにログイン
2. `New Project` からGitHubリポジトリを接続
3. Branchは `main` を選択
4. Runtimeは標準Laravel構成を選択
5. PostgreSQLアドオン（Managed DB）を有効化

## 3. 環境変数を設定

Cloud側で最低限以下を設定:

- `APP_NAME=Warifull`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY`（未設定なら生成）
- `APP_URL`（発行されたURL）
- `DB_CONNECTION=pgsql`
- DB接続情報（CloudのDB情報をそのまま設定）

## 4. デプロイ後に実行するコマンド

Cloudのデプロイフックまたはコンソールで:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

公開URLにアクセスし、画面表示できれば先行デプロイ完了です。

## 5. MVP期間中の運用ルール（おすすめ）

- 開発中は「小さくpush→Cloud自動デプロイ」で確認する
- DB変更を入れたら `migrate --force` の反映を必ず確認
- 不具合切り分け用に `APP_DEBUG=false` は維持し、ログ確認で追う

## 6. 任意: 最低限の公開保護（HTTP Basic）

公開URLを完全オープンにせず軽く保護したい場合は、
本番のみHTTP Basicミドルウェアを噛ませます。

実装方針:

1. カスタムミドルウェアを作成
2. `APP_ENV=production` の時だけ有効化
3. `BASIC_AUTH_USER` / `BASIC_AUTH_PASSWORD` を環境変数で管理

※ 面接デモ時にログイン情報を口頭で渡せるため、簡易保護として有効です。

## 7. つまずきポイントと対処

- Cloudで500エラー
  - `APP_KEY` 未設定の可能性
  - migration未実行の可能性
  - Cloudログで `storage/logs/laravel.log` 相当を確認
