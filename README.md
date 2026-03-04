# coachtech 勤怠管理アプリ

ある企業が開発した独自の勤怠管理アプリケーションです。

## 作成目的
ユーザーの勤怠と管理を目的とする

## 機能一覧
- [一般] 会員登録、ログイン、ログアウト
- [一般] 勤怠打刻（出勤、退勤、休憩入、休憩戻）
- [一般] 勤怠一覧表示、詳細表示、修正申請
- [管理者] ログイン、ログアウト
- [管理者] 全ユーザーの日別・月別勤怠一覧表示、詳細表示
- [管理者] 勤怠情報の直接修正
- [管理者] 修正申請の承認
- [管理者] スタッフ一覧表示
- [管理者] 勤怠情報のCSV出力

## 使用技術
- PHP
- Laravel
- MySQL
- Docker

## テーブル設計

```mermaid
erDiagram
    users ||--o{ attendances : "1対多"
    attendances ||--o{ break_times : "1対多"
    attendances ||--o{ stamp_correction_requests : "1対多"

    users {
        bigint id PK
        string name
        string email
        string password
    }
    admins {
        bigint id PK
        string name
        string email
        string password
    }
    attendances {
        bigint id PK
        bigint user_id FK
        date date
        time start_time
        time end_time
    }
    break_times {
        bigint id PK
        bigint attendance_id FK
        time start_time
        time end_time
    }
    stamp_correction_requests {
        bigint id PK
        bigint attendance_id FK
        date date
        time start_time
        time end_time
        string reason
        boolean status
    }
    ```

## 🛠 環境構築

※ 事前に Docker Desktop を起動しておいてください。

### 1. リポジトリの取得
まずはこのプロジェクトをご自身のパソコンにクローン（コピー）してください。

\`\`\`bash
git clone https://github.com/megumi2233/attendance-app.git
cd attendance-app
\`\`\`

### 2. アプリケーションの起動（魔法のコマンド✨）
プロジェクトのフォルダに移動したら、以下のコマンドを**1回実行するだけ**で、環境構築（コンテナの起動からダミーデータの投入まで）がすべて完了します！

\`\`\`bash
make init
\`\`\`

---

## テスト用ログイン情報

採点・動作確認の際は、以下のテスト用アカウントをご利用ください。

**【管理者ユーザー】**
- メールアドレス: `admin@example.com`
- パスワード: `password`

**【一般ユーザー】**
- メールアドレス: `test@example.com`
- パスワード: `password`
