# GitHub Actions ワークフロー変更メモ

## 2025年7月現在のビルド構成

### トリガー
- タグプッシュ時（`tags: '*'`）
- masterブランチへのプルリクエスト時

### ジョブ構成
1. **test**: コメントアウト（現在無効）
   - PHP 7.2, 7.4 × WordPress latest, 6.1 のマトリックステスト
   - MySQL 5.7 サービス
   - PHPUnit実行

2. **asset**: アセットビルド
   - Ubuntu latest
   - Node.js 14
   - PHP 7.2
   - Composer依存関係インストール（--no-dev）
   - npm install & npm run package

3. **vulnerability**: 脆弱性チェック（タグ時のみ）
   - Snyk使用
   - 全プロジェクト対象

4. **status-check**: ジョブ成功確認
   - assetジョブの完了待ち

5. **release**: GitHubリリース作成（タグ時のみ）
   - PHP 7.2, Node.js 14
   - `bin/build.sh` でビルド
   - `bin/clean.sh` でクリーンアップ
   - GitHubリリース作成
   - zipファイルアップロード

### 使用技術バージョン
- GitHub Actions: v1系（古い）
- Node.js: 14（古い）
- PHP: 7.2
- MySQL: 5.7

## 変更計画

### 新しいリリースフロー
1. **feature → master (PR)**
   - test.yml実行（必須、マージ条件）
   - PHP lint + JS/CSS lint + ビルド確認

2. **master更新時**
   - auto-pr-to-release.yml実行
   - master → release のDraft PR自動作成/更新

3. **release PR手動マージ時**
   - deploy-to-production.yml実行
   - テーマビルド → GitHubリリース作成 → rsyncデプロイ

### ビルド・デプロイ対象
- **対象**: `themes/hametuha` のみ
- **除外**: `maintenance.php`, `db-error.php`（更新頻度低、手動管理）

### AWSデプロイ設定
- `AWS_ACCESS_KEY_ID` アクセスキーID
- `AWS_SECRET_ACCESS_KEY` アクセスキー  
- `/var/www/vhosts/hametuha.com/wp-content/themes/hametuha` デプロイ先

### 技術仕様更新
- GitHub Actions: v4系
- Node.js: 18（LTS）
- PHP: 7.4（開発環境に合わせる）
- rsync: burnett01/rsync-deployments@7.0.2

### 予定変更項目
- [ ] test.yml作成（PHP lint + JS/CSS lint + ビルド確認）
- [ ] auto-pr-to-release.yml作成（master → release 自動PR）
- [ ] deploy-to-production.yml作成（リリース + rsyncデプロイ）
- [ ] .rsyncignore作成（デプロイ除外ファイル設定）
- [ ] GitHub branch protection設定（master, release）

## 達成項目

- [ ] 

#genai #claude
