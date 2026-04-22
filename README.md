# ワリフル（デモ版）

休暇時の週次業務再割当ツールのMVPです。  
企画書: `docs/warifull_mvp_proposal.md`

## 解決したい課題
週次の業務が属人化している職場では、  
**「この曜日は週次の業務があるから休みづらいな」**  
**「代わりを誰かに頼むのも申し訳ないな」**  
といった状況が起こりがち。

その結果、担当者は希望の休みを取りづらくなり、  
管理者にとっても割り振り判断が負担に。

ワリフルは、週次業務と担当者を可視化し、  
**休暇登録時に『代替担当候補を自動で提案』することで、**  
属人化の緩和とタスクの割り振りを支援するツールです。

## プロダクト概要
- メンバーの有給休暇時に、週次業務の担当状況を可視化する業務支援ツール
- 進捗は「週次ボード表示」と「有給登録」のMVP機能を中心に実装済み

## 実装状況（企画書ベース）
最終更新: 2026-04-22

| 企画書の機能 | 状態 | 現在の実装内容 |
|---|---|---|
| メンバー管理 | 一部実装 | メンバー表示は実装済み。登録・編集・削除UIは未実装 |
| 週次業務管理 | 一部実装 | 週次業務表示は実装済み。登録・編集・削除UIは未実装 |
| スキルレベル管理 | 一部実装 | テーブル/モデル/Seederは実装済み。管理UIと運用ロジックは未実装 |
| 週次表示 | 実装済み（MVP範囲） | `/` で月〜金表示、日付タブ選択、選択日タスクのみ表示、週移動（前週/翌週） |
| 休暇登録 | 実装済み（MVP範囲） | メンバーごとの有給追加モーダル、複数日選択、今日以降の有給同期更新 |
| 代替担当候補の自動提案 | 未実装 | ルールベース自動選定ロジックは未着手 |
| 再割当結果表示画面 | 未実装 | 一覧表示画面は未着手 |
| デモ用サンプルデータ表示 | 実装済み | `DemoDataSeeder` でメンバー・週次業務・スキル・有給・振替サンプルを投入 |
| デプロイ | 準備済み | 手順書あり。README時点で「完了状態」は未記載 |

## 現在できるデモ
- 週次ボードの表示
- 日付タブの選択による当日タスク表示
- 週移動
- メンバー別の有給登録・更新

## これから実装する部分
- 代替担当候補の自動提案ロジック
- 再割当結果表示画面
- メンバー管理画面の登録・編集・削除
- 週次業務管理画面の登録・編集・削除
- スキルレベル管理UI

## 主なルート
- `GET /` : 週次ボード表示
- `PUT /members/{member}/vacations` : 有給更新

## 開発環境
- PHP 8.5
- Laravel 13
- PostgreSQL
- Laravel Sail
- PHPUnit

## セットアップ
```bash
vendor/bin/sail up -d
vendor/bin/sail composer install
vendor/bin/sail artisan migrate --seed
vendor/bin/sail npm install
vendor/bin/sail npm run dev
```

## テスト
```bash
make test-all
# or
vendor/bin/sail artisan test --compact
```

## 関連ドキュメント
- 企画書: `docs/warifull_mvp_proposal.md`
- ER図: `docs/warifull_er_diagram.md`
- 週次画面実装手順: `docs/issue_4_weekly_board_runbook.md`
- 有給モーダル実装手順: `docs/issue_5_vacations_modal_runbook.md`
- 日付タブ絞り込み実装手順: `docs/issue_19_weekday_filter_runbook.md`
- デプロイ準備: `docs/laravel_setup_and_predeploy.md`, `docs/laravel_cloud_predeploy.md`
