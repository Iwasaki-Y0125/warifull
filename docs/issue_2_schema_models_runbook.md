# Issue #2 実装手順書（DBスキーマ + モデル）

対象Issue: `feat: メンバー・週次業務・スキル・休暇のDBスキーマとモデルを作成する`（#2）

## 1. 参照した資料

### リポジトリ内
- `docs/warifull_er_diagram.md`
- `docs/warifull_mvp_proposal.md`
- `docs/laravel_setup_and_predeploy.md`
- `AGENTS.md`（Sail実行・Artisan利用・テスト方針）

### 一次ソース（Laravel公式）
- Migrations: https://laravel.com/docs/13.x/migrations
- Eloquent Relationships: https://laravel.com/docs/13.x/eloquent-relationships
- Eloquent Mutators / Casts: https://laravel.com/docs/13.x/eloquent-mutators
- Database Testing: https://laravel.com/docs/13.x/database-testing
- Eloquent Factories: https://laravel.com/docs/13.x/eloquent-factories
- Sail: https://laravel.com/docs/13.x/sail

## 2. ゴール（Issue #2 の完了条件）
- `members`
- `weekly_tasks`
- `member_task_skills`
- `vacations`
- `weekly_task_owners`
- `task_substitutions`

上記6テーブルの migration が作成され、`vendor/bin/sail artisan migrate` が成功すること。  
加えて、基本CRUDに必要な Eloquent モデルとリレーション定義が揃っていること。

## 3. 実装順

## 3-1. モデル + migration + factory を生成
以下を順に実行する。

```bash
vendor/bin/sail artisan make:model Member -mf --no-interaction
vendor/bin/sail artisan make:model WeeklyTask -mf --no-interaction
vendor/bin/sail artisan make:model MemberTaskSkill -mf --no-interaction
vendor/bin/sail artisan make:model Vacation -mf --no-interaction
vendor/bin/sail artisan make:model WeeklyTaskOwner -mf --no-interaction
vendor/bin/sail artisan make:model TaskSubstitution -mf --no-interaction
```

補足:
- 今回は将来のテストで使うため、全モデルに factory を付ける。
- seeder はこのIssueの必須ではないため、必要になった時点で追加する。

## 3-2. migration 実装
`docs/warifull_er_diagram.md` を基準に、各 migration を編集する。

実装時の要点:
- 外部キーは `foreignId()->constrained()` を基本にする。
- MVPでは外部キー削除ルールを `cascadeOnDelete` 寄せで統一する（親削除時に子も削除）。
- `task_substitutions.status` は `string` カラム + Enum Cast で扱い、値は `pending/assigned` の2値に固定する（DBデフォルトは `pending`）。
- `member_task_skills` は `member_id + weekly_task_id` の重複を防ぐユニーク制約を付ける。
- `weekly_task_owners` は「現在の担当」管理を目的にし、`effective_from/effective_to` は持たない。
- `weekly_task_owners` は `role`（`main` / `sub`）により、1タスクに複数担当を持てる前提で実装する。
- `task_substitutions` は `vacation_id + weekly_task_id` のユニーク制約を付ける。
- `weekly_tasks.weekday` は MVP では `1=月 ... 5=金` のみ扱う。

## 3-3. モデルのリレーション実装
各モデルで最低限次を定義する。

- `Member`
  - `vacations(): HasMany`
  - `taskSkills(): HasMany`（`MemberTaskSkill`）
  - `weeklyTaskOwners(): HasMany`
  - `originalTaskSubstitutions(): HasMany`（`original_member_id`）
  - `substituteTaskSubstitutions(): HasMany`（`substitute_member_id`）

- `WeeklyTask`
  - `taskSkills(): HasMany`
  - `owners(): HasMany`（`WeeklyTaskOwner`）
  - `substitutions(): HasMany`

- `MemberTaskSkill`
  - `member(): BelongsTo`
  - `weeklyTask(): BelongsTo`

- `Vacation`
  - `member(): BelongsTo`
  - `substitutions(): HasMany`

- `WeeklyTaskOwner`
  - `member(): BelongsTo`
  - `weeklyTask(): BelongsTo`

- `TaskSubstitution`
  - `vacation(): BelongsTo`
  - `weeklyTask(): BelongsTo`
  - `originalMember(): BelongsTo`
  - `substituteMember(): BelongsTo`

## 3-4. factory の最小整備
factory は migration 制約を満たす最小値を返すように設定する。

実装方針:
- `weekday`: 1〜5
- `skill_level`: 0〜3
- `status`: `pending` / `assigned`
- 外部キー列は関連モデル factory を使って生成可能にしておく

## 3-5. マイグレーション実行と検証
1. migration 実行

```bash
vendor/bin/sail artisan migrate
```

2. rollback / 再実行で整合性確認

```bash
vendor/bin/sail artisan migrate:rollback
vendor/bin/sail artisan migrate
```

## 3-6. テスト追加
Issue完了の根拠を残すため、DB構造とリレーションの最小テストを追加する。

```bash
vendor/bin/sail artisan make:test --phpunit Feature/SchemaModelsTest --no-interaction
vendor/bin/sail artisan test --compact tests/Feature/SchemaModelsTest.php
```

テスト観点:
- 6テーブルが存在する
- 主要外部キー制約が有効
- モデルリレーション経由で関連取得できる

## 3-7. 仕上げ
PHPファイルを変更した場合は整形を実行。

```bash
vendor/bin/sail bin pint --dirty --format agent
```

最終確認:

```bash
git status -sb
vendor/bin/sail artisan test --compact --filter=SchemaModelsTest
```

## 4. 設計の決定事項
- `task_substitutions.status` は DB enum を使わず、`string` + Enum Cast で実装する（値は `pending/assigned`）。
- `task_substitutions.status` の DB デフォルト値は `pending` とする。
- `weekly_task_owners` は履歴期間カラム（`effective_from/effective_to`）を持たない。
- `weekly_task_owners` は `role`（`main` / `sub`）により、1タスクに複数担当を持てる前提で実装する。
- `task_substitutions` は `vacation_id + weekly_task_id` で一意制約を持たせる。
- 外部キー削除ルールは MVP では `cascadeOnDelete` を基本方針とする。
- `weekly_tasks.weekday` は MVP では月〜金（`1..5`）固定で運用する。

## 5. 今回の方針理由（MVP）
- このツールの主目的は「現時点の割り振り管理」であり、履歴管理は主目的ではない。
- `softDeletes` は将来の拡張候補とし、MVP時点では実装コストを抑えるため採用しない。
- まずは物理削除 + `cascadeOnDelete` でシンプルに実装し、要件が出た時点で対象テーブルを絞って履歴対応する。
- テストの最低ライン（テーブル作成/FK/ユニーク制約/Enum Cast）は次チャットで別途合意して確定する。
