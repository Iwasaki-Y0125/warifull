# ワリフル Laravel 環境構築手順（MVP要件対応）

このドキュメントは、`docs/warifull_mvp_proposal.md` の要件に合わせて、
Laravel未経験でも最短でローカル起動まで進めるための手順です。

デプロイ手順は [docs/laravel_cloud_predeploy.md](./laravel_cloud_predeploy.md) に分離しています。

## 0. この手順のゴール

- ローカルで Laravel アプリを起動できる
- PostgreSQL を使って初回マイグレーションを実行できる

## 1. 前提（今回の要件）

- バックエンド: PHP / Laravel
- DB: PostgreSQL
- ローカル開発: Docker + Laravel Sail
- フロント: Blade（最小限）

## 2. 事前インストール（ローカルPC）

最初に以下だけ入れておきます。

1. Docker Desktop（またはDocker Engine + Docker Compose）
2. Git

補足:
- この手順では、ローカルにPHPやComposerを直接入れなくても進められます（Sail経由）。

## 3. Laravel プロジェクト作成（Sail + PostgreSQL）

作業ディレクトリで以下を実行します。

```bash
cd /path/to/workspace
curl -s "https://laravel.build/warifull?with=pgsql" | bash
cd warifull
./vendor/bin/sail up -d
```

初回起動確認:

```bash
./vendor/bin/sail artisan -V
./vendor/bin/sail artisan about
```

## 4. DB接続と初回マイグレーション

`.env` は作成時点でSail用設定が入っています。`pgsql` 設定が入っていることを確認します。

確認ポイント（例）:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

マイグレーション実行:

```bash
./vendor/bin/sail artisan migrate
```

補足:
- 初期状態で `CACHE_DRIVER=database` / `SESSION_DRIVER=database` を使う構成では、未マイグレーションのまま `http://localhost` を開くとエラーになる場合があります。
- そのため、まず `migrate` を実行してからブラウザ確認します。

ブラウザで `http://localhost` を開き、Laravel初期画面が出ればOKです。

## 5. つまずきポイントと対処

- `localhost` が開けない
  - `./vendor/bin/sail ps` でコンテナ起動状態を確認
  - `./vendor/bin/sail up -d` を再実行

- DB接続エラー
  - `.env` の `DB_HOST=pgsql` になっているか確認
  - `./vendor/bin/sail artisan config:clear` 実行後に再試行
