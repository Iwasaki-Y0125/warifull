# ワリフル MVP ER図（要件書ベース初版）

`warifull_mvp_proposal.md` の要件から、MVP実装向けにテーブルを整理した初版ER図です。

```mermaid
erDiagram
    members {
        bigint   id         PK "メンバーID"
        string   name          "氏名"
        datetime created_at    "作成日時"
        datetime updated_at    "更新日時"
    }

    weekly_tasks {
        bigint   id         PK "週次業務ID"
        string   name          "業務名"
        text     description   "業務説明"
        tinyint  weekday       "実施曜日(1=月 ... 5=金)"
        time     start_time    "実施開始時刻"
        datetime created_at    "作成日時"
        datetime updated_at    "更新日時"
    }

    member_task_skills {
        bigint   id             PK "スキルレコードID"
        bigint   member_id      FK "メンバーID"
        bigint   weekly_task_id FK "週次業務ID"
        tinyint  skill_level       "0-3の習熟度"
        datetime created_at        "作成日時"
        datetime updated_at        "更新日時"
    }

    vacations {
        bigint   id            PK "休暇ID"
        bigint   member_id     FK "休暇取得メンバーID"
        date     vacation_date    "休暇日"
        datetime created_at       "作成日時"
        datetime updated_at       "更新日時"
    }

    weekly_task_owners {
        bigint   id             PK "通常担当レコードID"
        bigint   weekly_task_id FK "対象の週次業務ID"
        bigint   member_id      FK "通常担当メンバーID"
        tinyint  priority          "担当優先度(1=主担当,2=サブ担当)"
        date     effective_from    "担当開始日"
        date     effective_to      "担当終了日(NULLで現行)"
        datetime created_at        "作成日時"
        datetime updated_at        "更新日時"
    }

    task_substitutions {
        bigint   id                   PK "振替レコードID"
        bigint   vacation_id          FK "起点となる休暇ID"
        bigint   weekly_task_id       FK "振替対象の週次業務ID"
        bigint   original_member_id   FK "元担当メンバーID"
        bigint   substitute_member_id FK "振替担当メンバーID"
        string   status                  "proposed/confirmed/cancelled"
        datetime created_at              "作成日時"
        datetime updated_at              "更新日時"
    }

    members ||--o{ vacations : takes
    members ||--o{ member_task_skills : has
    weekly_tasks ||--o{ member_task_skills : requires

    weekly_tasks ||--o{ weekly_task_owners : has_owner
    members ||--o{ weekly_task_owners : owns

    vacations ||--o{ task_substitutions : triggers
    weekly_tasks ||--o{ task_substitutions : substituted_task
    members ||--o{ task_substitutions : original_member
    members ||--o{ task_substitutions : substitute_member

```

## 設計メモ（MVP）
- 通常担当は `weekly_task_owners`、振替担当は `task_substitutions` に分離。
- `weekly_task_owners.priority` は主担当/副担当の順序表現に利用可能（単一担当なら固定値で運用）。
- `task_substitutions.status` は `proposed` / `confirmed` などを想定。
- 候補提案は都度計算し、MVPでは `reassignment_suggestions` テーブルは持たない。
- `member_task_skills.skill_level` は要件に合わせて `0-3` を使用。
