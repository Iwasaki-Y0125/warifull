# Issue #5 実装手順書（有給追加モーダル / screen_mvp02）

対象Issue: `feat: 有給追加モーダルを実装する（screen_mvp02）`（#5）

## 1. 参照した資料

### リポジトリ内
- Issueキャプチャ（#5）
- `docs/screen_image/screen_mvp02.png`
- `README.md`
- `docs/warifull_mvp_proposal.md`
- `docs/issue_2_schema_models_runbook.md`
- `docs/issue_3_seed_data_runbook.md`
- `docs/issue_4_weekly_board_runbook.md`
- `app/Http/Controllers/WeeklyBoardController.php`
- `resources/views/weekly_board/index.blade.php`
- `app/Models/Member.php`
- `app/Models/Vacation.php`
- `database/migrations/2026_04_21_085221_create_vacations_table.php`
- `tests/Feature/WeeklyBoardScreenTest.php`
- `AGENTS.md`（Sail、Laravel Way、PHPUnit方針）

### 一次ソース（公式ドキュメント）
- Laravel Routing: https://laravel.com/docs/13.x/routing
- Laravel Controllers: https://laravel.com/docs/13.x/controllers
- Laravel Validation / Form Request: https://laravel.com/docs/13.x/validation#form-request-validation
- Laravel Eloquent Relationships: https://laravel.com/docs/13.x/eloquent-relationships
- Laravel Database Transactions: https://laravel.com/docs/13.x/database#database-transactions
- Laravel HTTP Tests: https://laravel.com/docs/13.x/http-tests
- MDN `<dialog>`: https://developer.mozilla.org/docs/Web/HTML/Element/dialog

## 2. ゴール（Issue #5 の完了条件）
- メンバー単位で「有給追加モーダル」を開ける。
- カレンダーUIで複数日を選択して保存できる。
- 保存した有給日が次回表示時に反映される。

## 3. 実装スコープと前提
- MVPでは `screen_mvp02` の情報構造を優先し、細部のUI差分は後続で調整可能とする。
- 保存仕様は「対象メンバーの今日以降の有給日を、選択済み日セットで同期（全置換）」とする。
- `vacation_dates` は空配列を許可し、全解除できるようにする。
- 過去日データは削除しない。
- タイムゾーンはアプリ標準設定（`Asia/Tokyo`）を前提に日付を扱う。

## 4. 実装手順

### 4-1. ルートと保存用エンドポイントを追加
`routes/web.php` にメンバー有給更新ルートを追加する。

方針:
- 既存のトップ表示 `GET /` は維持する。
- 更新は `PUT /members/{member}/vacations` とし、`member` は暗黙的ルートモデルバインディングを使う。
- named route を付ける（例: `members.vacations.update`）。

### 4-2. Form Request を作成して入力を検証
```bash
vendor/bin/sail artisan make:request UpdateMemberVacationsRequest --no-interaction
```

検証ルール（例）:
- `vacation_dates`: `nullable|array`
- `vacation_dates.*`: `required|date_format:Y-m-d|after_or_equal:today|distinct`

補足:
- `after_or_equal:today` はアプリ側タイムゾーン基準の日付判定になる。
- 過去日はUI上でも選択不可にする。
- 更新処理では必ず `$request->validated()` を使う。

### 4-3. 保存処理をControllerへ実装
```bash
vendor/bin/sail artisan make:controller MemberVacationController --no-interaction
```

実装方針:
- `__invoke(UpdateMemberVacationsRequest $request, Member $member): RedirectResponse`
- `DB::transaction()` で「削除 + 追加」を1トランザクションにまとめる。
- 今日以降の既存有給のうち、未選択日を削除する。
- 選択日は `updateOrCreate(['member_id' => ..., 'vacation_date' => ...])` で保存する。
- 保存後は `to_route('weekly-board.index')` で戻し、フラッシュメッセージを返す。
- フラッシュには「対象メンバー名」と「保存した有給日（m/d のカンマ区切り）」を含める。
- 有給日表示順は昇順に統一する。

### 4-4. モデルとDB制約を必要最小限で補強
対象:
- `app/Models/Vacation.php`

推奨:
- `vacation_date` を `date` キャストする。
- 複数経路からの重複登録を防ぐため、`vacations(member_id, vacation_date)` のユニーク制約を追加する。

制約追加が必要な場合:
```bash
vendor/bin/sail artisan make:migration add_member_id_vacation_date_unique_to_vacations_table --table=vacations --no-interaction
```

### 4-5. 週次ボード画面にモーダル起点を追加
対象:
- `resources/views/weekly_board/index.blade.php`
- `resources/views/weekly_board/_vacation_modal.blade.php`（新規）
- `app/Http/Controllers/WeeklyBoardController.php`

実装内容:
- メンバーカードに「有給追加」ボタンを追加。
- 押下時に対象メンバー情報（id/name/既存有給日）をモーダルへ受け渡す。
- モーダル本体は `resources/views/weekly_board/_vacation_modal.blade.php` に切り出す。
- `index.blade.php` から `@include('weekly_board._vacation_modal')` で読み込む。
- モーダル内に以下を表示:
  - タイトル（有給追加）
  - 対象メンバー名
  - 月送りカレンダー
  - 選択中有給日リスト
  - `キャンセル` / `保存` ボタン
- 保存は `PUT /members/{member}/vacations` へ送信する。

### 4-6. カレンダーUIロジックをJSへ分離
対象:
- `resources/js/app.js`
- `resources/js/modal-manager.js`（新規）
- `resources/js/weekly-board-vacation-calendar.js`（新規）
- `resources/js/weekly-board-vacation-modal.js`（新規）

実装方針:
- Bladeに長いJSを書かず、モーダル制御をJSファイルへ切り出す。
- モーダル開閉の共通処理は `modal-manager.js` に寄せる。
- 状態管理は `selectedDates: Set<string>`（`Y-m-d`）で統一する。
- 月移動しても `selectedDates` は破棄しない（モーダルを閉じるまで保持）。
- 月移動時にカレンダー再描画し、選択済み日は強調表示する。
- 送信前に hidden input（`vacation_dates[]`）を再生成し、全月ぶんの選択日をまとめてフォームに詰める。
- 過去日はカレンダー上で選択不可にする。

### 4-7. Featureテストを追加
```bash
vendor/bin/sail artisan make:test --phpunit Feature/MemberVacationModalTest --no-interaction
```

最低限の観点:
- 正常系:
  - `PUT /members/{member}/vacations` が成功し、選択日が保存される。
  - 未選択日に変わった既存有給が削除される（今日以降のみ）。
- 異常系:
  - 不正日付フォーマットでバリデーションエラー。
  - 重複日入力でバリデーションエラー。
- 表示反映:
  - 保存後にトップ画面で「直近の有給予定」に反映される。
  - 保存後のフラッシュにメンバー名と保存有給日が表示される。

実行:
```bash
vendor/bin/sail artisan test --compact tests/Feature/MemberVacationModalTest.php
```

### 4-8. 手動確認
```bash
vendor/bin/sail up -d
vendor/bin/sail open
```

確認項目:
- メンバーごとに有給追加モーダルを開ける。
- 1件以上の日付を複数選択できる。
- `保存` 後に再度開いたとき、選択済み日が保持されている。
- サイドバーの「直近の有給予定」に保存内容が反映される。

### 4-9. 仕上げ
PHPファイルを変更した場合:
```bash
vendor/bin/sail bin pint --dirty --format agent
```

最終確認:
```bash
git status -sb
vendor/bin/sail artisan test --compact --filter=MemberVacationModalTest
```

## 5. 実装順の推奨
1. `UpdateMemberVacationsRequest` と `MemberVacationController` を先に作る。
2. ルートを追加し、テストで保存APIを先に固める。
3. 画面側（ボタン/モーダル/カレンダーJS）を実装する。
4. 表示反映（`WeeklyBoardController` データ整形）を微調整する。
5. 手動確認とテスト実行で完了。

## 6. リスクと対策
- 日付重複登録のリスク:
  - アプリ側 `distinct` + DBユニーク制約で二重防止。
- タイムゾーン差分による日付ズレ:
  - サーバー保存値は `Y-m-d` のみを受け取り、時刻を持たせない。
- JS状態とフォーム値の不一致:
  - 送信直前に hidden input を毎回再構築する。

## 7. フロント変更が反映されない場合
```bash
vendor/bin/sail npm run dev
# or
vendor/bin/sail npm run build
# or
vendor/bin/sail composer run dev
```

## 8. 実装反映状況（2026-04-22）
- ✅ `PUT /members/{member}/vacations` を追加（暗黙的ルートモデルバインディング）
- ✅ `UpdateMemberVacationsRequest` で `nullable|array` + `after_or_equal:today|distinct` を適用
- ✅ `MemberVacationController` で「今日以降のみ同期（全置換）」を実装
- ✅ フラッシュは「メンバー名 + 有給日（m/d）」、全解除時は「有給予定なし」を表示
- ✅ `vacations(member_id, vacation_date)` ユニーク制約 migration を追加済み
- ✅ `Vacation` モデルに `fillable` と `vacation_date` の `date` cast を追加済み
- ✅ モーダル本体を `_vacation_modal.blade.php` に分離し、`index.blade.php` から `@include`
- ✅ カレンダー/モーダル制御を `resources/js/*` へ分離済み
- ✅ `MemberVacationModalTest` を追加（5ケース）

未対応:
- バリデーションエラー時に対象モーダルを自動再オープンする処理は未実装（通常のエラー表示戻りのみ）

## 9. テスト実績（2026-04-22）
- `vendor/bin/sail artisan test --compact tests/Feature/MemberVacationModalTest.php`
  - `5 passed (24 assertions)`
- `vendor/bin/sail artisan test --compact`
  - `24 passed (99 assertions)`
