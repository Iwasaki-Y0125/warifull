# Issue #3 実装手順書（デモ用 seed データ投入）

対象Issue: `chore: デモ用seedデータを投入`（#3）

## 1. 参照した資料

### リポジトリ内
- Issueキャプチャ（#3）
- `docs/issue_2_schema_models_runbook.md`
- `docs/warifull_er_diagram.md`
- `docs/warifull_mvp_proposal.md`
- `AGENTS.md`（Sail実行・Artisan利用・テスト方針）
- `database/migrations/*.php`
- `database/seeders/DatabaseSeeder.php`

### 一次ソース（Laravel公式）
- Database: Seeding: https://laravel.com/docs/13.x/seeding
- Database Testing: https://laravel.com/docs/13.x/database-testing
- Sail: https://laravel.com/docs/13.x/sail

## 2. ゴール（Issue #3 の完了条件）
- `migrate:fresh --seed` で、トップ画面表示に必要な初期データが投入されること。
- `DatabaseSeeder` から、Issue #2 で作成済みの業務ドメイン（`members`, `weekly_tasks`, `member_task_skills`, `vacations`, `weekly_task_owners`, `task_substitutions`）に対して一貫したサンプルデータを投入できること。

## 3. 実装方針
- 開発環境のseedはFactoryを使わず、デモで説明しやすい固定データをSeederで明示投入する。
- 参照関係の都合上、投入順は `members` -> `weekly_tasks` -> `member_task_skills` -> `vacations` -> `weekly_task_owners` -> `task_substitutions` とする。
- 制約違反を避けるため、次を必ず満たす。
  - `weekly_tasks.weekday`: `1..5`
  - `member_task_skills.skill_level`: `0..3`
  - `weekly_task_owners.role`: `main` or `sub`
  - `task_substitutions.status`: `pending` or `assigned`
  - `member_task_skills (member_id, weekly_task_id)` は重複禁止
  - `task_substitutions (vacation_id, weekly_task_id)` は重複禁止

## 4. 実装手順

## 4-1. Seederクラスを作成
以下を実行する。

```bash
vendor/bin/sail artisan make:seeder DemoDataSeeder --no-interaction
```

必要に応じて、テーブル単位で分割したい場合は次を追加する。

```bash
vendor/bin/sail artisan make:seeder MemberSeeder --no-interaction
vendor/bin/sail artisan make:seeder WeeklyTaskSeeder --no-interaction
vendor/bin/sail artisan make:seeder MemberTaskSkillSeeder --no-interaction
vendor/bin/sail artisan make:seeder VacationSeeder --no-interaction
vendor/bin/sail artisan make:seeder WeeklyTaskOwnerSeeder --no-interaction
vendor/bin/sail artisan make:seeder TaskSubstitutionSeeder --no-interaction
```

MVPではまず `DemoDataSeeder` 1本で十分。肥大化したら分割する。

## 4-2. デモデータの中身を決める
最低限、次の説明シナリオが成立する件数を投入する。

- メンバー: 4〜6件
- 週次業務: 各曜日2〜3件（MVP現行は各曜日2件、合計10件）
- スキル: 各業務に対して候補者が1名以上 `skill_level >= 2` になるように作成
- 休暇: 2〜3件（欠員が発生するケースを含む）
- 通常担当: 各業務に `main` を1名、必要に応じて `sub` を追加
- 振替: `pending` と `assigned` を最低1件ずつ

補足:
- 画面デモで「誰が休みで、誰に振替されたか」を説明できるよう、名前・業務名・曜日は読みやすい値にする。
- `task_substitutions.original_member_id` は、該当業務の通常担当者（`weekly_task_owners.role = main`）と整合させる。

## 4-3. `DatabaseSeeder` から呼び出す
`database/seeders/DatabaseSeeder.php` で `DemoDataSeeder` を呼び出す。  
既存の `User::factory()->create(...)` は開発seed方針（固定データ）に合わせて削除する。

実装例（方針）:
- `\$this->call([DemoDataSeeder::class]);`

## 4-4. `migrate:fresh --seed` で検証
以下を実行して、Seedが初期構築で再現可能か確認する。

```bash
vendor/bin/sail artisan migrate:fresh --seed
```

確認観点:
- エラーなく完了する
- 各テーブルに想定件数以上のレコードがある
- FK / CHECK / UNIQUE 制約違反が出ない

## 4-5. 本番環境での投入コマンド
Laravel Cloud の `Deploy commands` に以下2行を設定する。

```bash
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\DemoDataSeeder --force
```

補足:
- `--force` を付けると production 環境でも確認プロンプトなしで実行できる。
- 既存データに影響が出ないよう、Seeder側は `updateOrCreate` で冪等性を持たせる。

## 4-6. テストを追加
Seed投入の回帰防止として、最小のFeatureテストを追加する。

```bash
vendor/bin/sail artisan make:test --phpunit Feature/DemoSeedDataTest --no-interaction
vendor/bin/sail artisan test --compact tests/Feature/DemoSeedDataTest.php
```

テスト観点（最小）:
- `seed` 後に6テーブルへデータが入っている
- `weekly_task_owners.role` に `main` が存在する
- `task_substitutions.status` に `pending` または `assigned` が存在する

## 4-7. 仕上げ
PHPファイルを変更した場合は整形を実行。

```bash
vendor/bin/sail bin pint --dirty --format agent
```

最終確認:

```bash
git status -sb
vendor/bin/sail artisan test --compact --filter=DemoSeedDataTest
```

## 5. 実装時の注意点
- 固定データの名前・日付・曜日は、デモ台本と一致する値に固定する。
- 時間・曜日・休暇日が矛盾しないように、タスクと休暇の組み合わせを先に表で決める。
- `DatabaseSeeder` の責務は「このプロジェクトの初期データ投入」に寄せる。
