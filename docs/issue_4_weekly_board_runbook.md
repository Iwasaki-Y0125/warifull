# Issue #4 実装手順書（トップ / 週次業務表画面）

対象Issue: `feat: トップ/週次業務表画面を実装する（screen_mvp01）`（#4）

## 1. 参照した資料

### リポジトリ内
- Issueキャプチャ（#4）
- `docs/screen_image/screen_mvp01.png`
- `docs/warifull_mvp_proposal.md`
- `docs/issue_2_schema_models_runbook.md`
- `docs/issue_3_seed_data_runbook.md`
- `database/seeders/DemoDataSeeder.php`
- `routes/web.php`
- `app/Models/*.php`（`WeeklyTask`, `WeeklyTaskOwner`, `TaskSubstitution`, `Vacation`, `Member`）
- `AGENTS.md`（Sailコマンド、Laravel Way、PHPUnit方針）

### 一次ソース（Laravel公式）
- Routing: https://laravel.com/docs/13.x/routing
- Blade Templates: https://laravel.com/docs/13.x/blade
- Eloquent Relationships / Eager Loading: https://laravel.com/docs/13.x/eloquent-relationships
- HTTP Tests: https://laravel.com/docs/13.x/http-tests

## 2. ゴール（Issue #4 の完了条件）
- 週次（平日）表示UIをトップ画面で表示できる。
- タスクカードに「担当者・時刻」を表示できる。
- メンバー一覧サイドバーを表示できる。
- `screen_mvp01` 相当の情報構造（左: メンバー、右: 週次タスク一覧）が再現される。

## 3. 実装方針
- ルートは `/` をトップ画面として維持し、ClosureではなくControllerへ移行する。
- 週次タスクは `weekday`・`start_time` で並べ、`with()` で必要リレーションを先読みしてN+1を避ける。
- 欠員判定・振替候補表示のロジックは本Issueのスコープ外とする。
- MVPでは「平日5日ヘッダー + 週次タスク表示」を先に成立させる。
- 見た目は `screen_mvp01` の情報優先（完全一致デザインは次段階）で実装する。

## 4. 実装手順

## 4-1. コントローラ作成
```bash
vendor/bin/sail artisan make:controller WeeklyBoardController --no-interaction
```

実装内容:
- `WeeklyTask` を `weekday`,`start_time` 順で取得する。
- `owners.member` を eager load する。（N＋1対策）
- `Member` を取得し、`vacations` を eager load する。
- メンバーごとに「今日以降の有給予定を直近3件（`n/j` 形式）」へ整形する。
- 上部曜日タブ用に、今週の月〜金日付（`n/j` 形式）を生成する。
- 画面描画用に次を整形する。
  - タスク: タスク名 / 説明 / 時刻 / main担当者
  - メンバー: 氏名 / 直近有給3件

## 4-2. ルーティング更新
`routes/web.php` を更新し、`/` を `WeeklyBoardController` に紐付ける。

実装方針:
- `Route::get('/', WeeklyBoardController::class)->name('weekly-board.index');`
- 将来のURL生成を考慮して named route を付与する。

## 4-3. ビュー作成（トップ画面）
`resources/views/weekly_board/index.blade.php` を作成する。

表示要素:
- 左カラム
  - メンバー一覧（氏名、今日以降の直近有給3件）
- 右カラム
  - 平日5日ヘッダー（月〜金 + 日付）
  - タスクカード一覧（曜日ごと）
- 各カードに `担当者 / 時刻`
- 上部の `<` `>` ボタンは表示のみ（週移動ロジックは未実装）

実装ポイント:
- Bladeの `@foreach` で曜日別グループを描画。
- `screen_mvp01` に寄せて、ヘッダー / サイドバー / タブ / カードの見た目を調整する。

## 4-4. データ取得ロジックの補強（必要なら）
Controller内ロジックが肥大化したら、次の順で切り出す。
1. privateメソッド化（曜日整形 / 表示整形）
2. `app/Services` への抽出（このIssueでは必須ではない）

## 4-5. Featureテスト追加
```bash
vendor/bin/sail artisan make:test --phpunit Feature/WeeklyBoardScreenTest --no-interaction
```

最低限の観点:
- `/` が `200` を返す。
- レスポンスに主要見出し（例: ワリフル、チームメンバー、タスク名）が含まれる。
- 平日5日分のヘッダー情報（日付 `n/j`）が出る。

実行:
```bash
vendor/bin/sail artisan test --compact tests/Feature/WeeklyBoardScreenTest.php
```

## 4-6. 手動確認
```bash
vendor/bin/sail up -d
vendor/bin/sail open
```

確認項目:
- 左にメンバー一覧が表示される。
- 直近の有給予定が `5/15, 5/28` 形式で表示される。
- 右に平日ヘッダーとタスク一覧が表示される。
- タスクカードに担当者名と時刻が表示される。

## 4-7. 仕上げ
PHPファイルを変更した場合は整形する。

```bash
vendor/bin/sail bin pint --dirty --format agent
```

最終確認:
```bash
git status -sb
vendor/bin/sail artisan test --compact --filter=WeeklyBoardScreenTest
```

## 5. 補足
- `/` を週次業務表の本番ルートに切り替える（`welcome` は置き換え）。
- `welcome` 置換後の戻し導線は今回作らない。
- 画面は `DemoDataSeeder` 投入済みデータを前提にしつつ、データ0件時は空状態UIを表示する。
- 欠員判定・振替候補ロジックは後続Issueで実装する（今回は表示のみ）。
- 受け入れ基準は「`/` が200」「主要見出し表示」「seed済みタスク名表示」「平日5日ヘッダー（日付付き）表示」とする。

## 6. テスト実績
- `vendor/bin/sail artisan test --compact tests/Feature/WeeklyBoardScreenTest.php`
  - `2 passed (13 assertions)`
- `vendor/bin/sail artisan test --compact`
  - `19 passed (75 assertions)`

## 7. CSS表示反映されない場合
フロント変更が反映されない場合は、以下のいずれかを実行する。

```bash
vendor/bin/sail npm run dev
# or
vendor/bin/sail npm run build
# or
vendor/bin/sail composer run dev
```
