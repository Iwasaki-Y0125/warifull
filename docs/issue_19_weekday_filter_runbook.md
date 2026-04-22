# Issue #19 実装手順書（日付タブクリックで当日タスクのみ表示）

対象Issue: `週表示の日付クリックで曜日別タスクのみ表示する機能を実装`（#19）

## 1. 参照した資料

### リポジトリ内
- Issue #19 本文
- `README.md`
- `docs/issue_4_weekly_board_runbook.md`
- `docs/issue_5_vacations_modal_runbook.md`
- `app/Http/Controllers/WeeklyBoardController.php`
- `resources/views/weekly_board/index.blade.php`
- `tests/Feature/WeeklyBoardScreenTest.php`
- `database/seeders/DemoDataSeeder.php`
- `AGENTS.md`（Sail / Laravel Way / PHPUnit方針）

### 一次ソース（公式ドキュメント）
- Laravel Routing: https://laravel.com/docs/13.x/routing
- Laravel Requests（入力取得・クエリ）: https://laravel.com/docs/13.x/requests
- Laravel Blade: https://laravel.com/docs/13.x/blade
- Laravel Collections: https://laravel.com/docs/13.x/collections
- Laravel HTTP Tests: https://laravel.com/docs/13.x/http-tests

## 2. 現状整理（実装前）
- 曜日タブ（月〜金）は表示されるが、クリック不可。
- タスク表示は「月〜金の全曜日セクションを縦に列挙」する構成。
- `WeeklyBoardController` は `activeWeekday` を算出しているが、表示フィルタには使っていない。
- 週移動ボタン（`‹` / `›`）は `disabled` で未実装。
- 将来的に「日付ごとのフリカエ担当者表示」が必要になるため、状態は曜日ではなく日付で持つ必要がある。

## 3. ゴール（Issue #19 完了条件）
- 上部の日付タブをクリックすると、選択日（`date=YYYY-MM-DD`）に対応するタスクだけが表示される。
- 非選択日のタスクは表示されない。
- 選択状態の見た目（activeスタイル）が維持される。
- 該当日のタスクが0件なら空状態メッセージを表示する。
- 週移動後も「選択日」と「表示内容」が矛盾しない。
- 将来の日付別フリカエ担当者表示に拡張しやすい構造にする。

## 4. 実装方針
- 状態はURLクエリで管理する（`date=YYYY-MM-DD` を主軸）。
- ルート追加はせず、既存 `GET /`（`weekly-board.index`）を利用する。
- サーバー側で `selectedDate` を決定し、`selectedDate` の曜日をキーに表示タスクを抽出する。
- Blade側は日付タブをリンク化し、タスク一覧は「選択日」セクションのみ描画する。
- 将来は同じ `selectedDate` をキーに、日付単位のフリカエ担当者を上書き表示できる設計にする。

## 5. 実装手順

### 5-1. Controllerで選択日をクエリから解決
対象: `app/Http/Controllers/WeeklyBoardController.php`

実装内容:
- `__invoke()` に `Illuminate\Http\Request $request` を受ける。
- `date` クエリを `Y-m-d` として検証し、妥当なら `selectedDate` に採用する。
- `date` 未指定時は「今日」。土日なら今週月曜を `selectedDate` にする。
- `selectedDate` から `selectedWeekday`（`1..5`）を算出する。
- 既存の `$weeklyTasks->groupBy('weekday')` は維持しつつ、
  - `selectedDateTasks = $weeklyTasks->get($selectedWeekday, collect())`
  を作ってViewへ渡す。

注意点:
- 不正クエリ（例: `date=2026-13-99`）はデフォルト値へフォールバックする。
- 型を崩さない（Carbon/Collectionを明示的に扱う）。

### 5-2. 選択日基準で表示週とタブ日付を生成
対象: `app/Http/Controllers/WeeklyBoardController.php`

実装内容:
- `weekStart = selectedDate->copy()->startOfWeek(MONDAY)` でヘッダー週を生成する。
- `weekdayTabs` には `label` / `display_date(n/j)` / `raw_date(Y-m-d)` を持たせる。

補足:
- 週移動リンク用に `previousWeekDate` / `nextWeekDate`（`selectedDate ± 1week`）を同時に生成する。

### 5-3. Bladeで日付タブをクリック可能にする
対象: `resources/views/weekly_board/index.blade.php`

実装内容:
- タブ要素を `<a>` 化し、`route('weekly-board.index', ['date' => $tab['raw_date']] + [...])` を設定する。
- active判定は `$selectedDate->toDateString() === $tab['raw_date']` とする。
- 視覚状態（active色・非active色）は既存スタイルを維持。
- 現在選択日には `aria-current="page"` を付与してアクセシビリティ改善。

### 5-4. タスク表示を「選択日（の曜日）分のみ」に変更
対象: `resources/views/weekly_board/index.blade.php`

実装内容:
- 既存の `@foreach ($orderedWeekdays as $weekday)` で全曜日を描画しているブロックを、
  `selectedDate` 単一セクション表示に置き換える。
- 見出しは `{{ $selectedDate->format('n/j') }}（{{ $weekdayTabs[$activeWeekday]['label'] ?? '' }}）` で日付中心に表示する。
- タスク0件時は空状態文言を表示（`この日のタスクはありません。`）。
- 将来拡張点として、タスクカード内の担当者表示は `selectedDate` をキーに上書き可能な構造で描画する。

### 5-5. 週移動ボタンを有効化
対象: `resources/views/weekly_board/index.blade.php`

実装内容:
- `‹` / `›` を `disabled button` からリンク（またはsubmit）に変更。
- `‹` は `date=previousWeekDate`、`›` は `date=nextWeekDate` へ遷移する。
- 遷移先でも `selectedDate` を基点に activeタブとタスク表示が一致することを担保する。

## 6. テスト手順（PHPUnit）

### 6-1. 既存テスト更新
対象: `tests/Feature/WeeklyBoardScreenTest.php`

追加・更新観点:
- `GET /?date=2026-04-21` で火曜タスク（例: `請求確認`）は表示され、月曜タスク（例: `開通確認`）は非表示。
- `GET /?date=2026-04-22` で水曜タスク（例: `納品報告`）が表示される。
- `date` 未指定時は現行デフォルト（当日、平日外は月曜）で表示される。
- 不正な `date` クエリはフォールバックされる。
- 該当曜日0件時の空メッセージを確認（テスト内で特定曜日タスクを削除して検証）。

実行コマンド:
```bash
vendor/bin/sail artisan test --compact tests/Feature/WeeklyBoardScreenTest.php
```

必要に応じて:
```bash
vendor/bin/sail artisan test --compact --filter=WeeklyBoardScreenTest
```

## 7. 手動確認

実行:
```bash
vendor/bin/sail up -d
vendor/bin/sail open
```

確認項目:
- 月〜金の日付タブをクリックすると、表示タスクが選択日分のみになる。
- activeタブのハイライトがクリック先の日付と一致する。
- タスク0件日（該当曜日0件）で空状態が出る。
- （週移動を実装した場合）前週/翌週へ移動しても選択日と表示内容が一致する。

## 8. 仕上げ
- PHPファイルを変更した場合は整形を実行する。

```bash
vendor/bin/sail bin pint --dirty --format agent
```

- 最終確認:

```bash
git status -sb
vendor/bin/sail artisan test --compact tests/Feature/WeeklyBoardScreenTest.php
```

## 9. リスクと対策
- 不正クエリで表示崩れするリスク:
  - `date` を厳密検証し、失敗時は安全なデフォルト日へフォールバック。
- 週移動と選択日の不整合リスク:
  - すべてのリンクで `date` を明示的に引き回す。
- 既存UI回帰のリスク:
  - activeスタイルは既存クラスを流用し、色・余白を変更しない。
- 将来のフリカエ担当者反映で二重ロジック化するリスク:
  - 担当者解決処理は `selectedDate` を引数に受ける単一メソッド（またはService）へ集約する。

## 10. 実装反映状況（2026-04-22）
- ✅ `date` クエリを `WeeklyBoardController` で解決し、土日・不正値フォールバックを実装
- ✅ `weekdayTabs` に `raw_date` を追加し、日付タブをリンク化
- ✅ タスク表示を「選択日（の曜日）分のみ」に変更
- ✅ `‹` / `›` を有効化し、`date` の週移動（±1week）を実装
- ✅ `WeeklyBoardScreenTest` に絞り込み・不正日付フォールバック・空状態の検証を追加
- ✅ `WeeklyBoardScreenTest` のテストケースコメント（PHPDoc）を追加
