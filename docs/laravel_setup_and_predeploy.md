# ワリフル Laravel 環境構築（最小手順）

ローカルで Laravel を起動するための最低限手順です。

## 1. 前提

- Docker
- Git

## 2. プロジェクト作成

```bash
cd /path/to/workspace
curl -s "https://laravel.build/warifull?with=pgsql" | bash
cd warifull
./vendor/bin/sail up -d
```

## 3. 初回確認

```bash
./vendor/bin/sail artisan -V
./vendor/bin/sail artisan about
./vendor/bin/sail artisan migrate
```

- 先に `migrate` を実行してから、`http://localhost` を開く
- Laravel 初期画面が出れば完了

デプロイ手順は [docs/laravel_cloud_predeploy.md](./laravel_cloud_predeploy.md) を参照。
