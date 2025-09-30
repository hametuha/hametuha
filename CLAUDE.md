# Hametuha Docker開発環境

## 概要
wp-content相当をリポジトリルートとして管理する構成。

## ディレクトリ構成
```
hametuha/ (リポジトリルート)
├── docker-compose.yml
├── .env.example
├── wp/              # WordPressコア（composerで管理、.gitignore）
├── themes/
│   └── hametuha/    # テーマファイル
├── plugins/         # プラグイン（composer管理、.gitignore）
├── uploads/         # アップロード（.gitignore）
├── docker/          # Docker設定ファイル
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── php.ini
├── composer.json
└── .gitignore
```

## 主な機能

### 1. 統合Composer管理
- ルートの`composer.json`で全ての依存関係を管理
- wpackagistでWordPressプラグインを管理
- テーマ固有の依存関係（hametuha/wpametu等）も統合
- phpcs/phpunitもルートで管理

### 2. Docker Compose構成
- **WordPress**: PHP-FPM with Xdebug
- **Nginx**: Alpine版（軽量）
- **MySQL**: データベースサーバー
- **phpMyAdmin**: データベース管理UI
- **Mailpit**: メール監視ツール

### 3. 開発環境の特徴
- 標準ポート（80/443）を使用可能
- ホットリロード対応
- Xdebug対応（有効化済み）
- WP-CLIを含む
- volumeマウントでWordPress標準構造を維持
  - `./themes` → `/var/www/html/wp-content/themes`
  - `./plugins` → `/var/www/html/wp-content/plugins`
  - `./uploads` → `/var/www/html/wp-content/uploads`

## セットアップ手順

```bash
# 1. リポジトリをクローン
git clone [repository-url] hametuha
cd hametuha

# 2. 環境変数ファイルを作成
cp .env.example .env
# .envファイルを編集して必要な設定を行う

# 3. ホストファイルの設定（既に設定済みの場合はスキップ）
# /etc/hostsに以下を追加： 127.0.0.1 hametuha.info

# 4. SSL証明書を生成（mkcertが必要）
# brew install mkcert （未インストールの場合）
# mkcert -install （初回のみ）
cd docker/nginx/certs
mkcert hametuha.info
cd ../../..

# 5. Composer依存関係をローカルでインストール（WordPressコアを取得）
composer install

# 6. Dockerコンテナをビルド・起動
docker compose up -d --build

# 7. WordPressの初期設定（コンテナ内で実行）
docker compose exec wordpress wp core install \
  --url=https://hametuha.info \
  --title="Hametuha" \
  --admin_user=admin \
  --admin_password=password \
  --admin_email=admin@example.com

# 8. テーマ依存関係のインストール
docker compose exec wordpress bash -c "cd themes/hametuha && composer install && npm install"

# 9. テーマを有効化
docker compose exec wordpress wp theme activate hametuha

# 10. 開発開始（テーマのビルド監視）
docker compose exec wordpress bash -c "cd themes/hametuha && npm run watch"
```

## アクセス
- **サイト**: [https://hametuha.info](https://hametuha.info)
- **WordPress管理画面**: [https://hametuha.info/wp-admin](https://hametuha.info/wp-admin)
- **phpMyAdmin**: [http://localhost:8081](http://localhost:8081)
- **Mailpit（メール監視）**: [http://localhost:8026](http://localhost:8026)
- **MySQL（外部接続）**: localhost:3307

### サービス説明
- **phpMyAdmin**: データベースの管理・操作
- **Mailpit**: WordPressから送信されるメールの監視・確認
- **MySQL**: SequelProやTablePlusなどでの外部接続用

## 開発コマンド

### Dockerコンテナ操作
```bash
# コンテナ起動
docker compose up -d

# コンテナ停止
docker compose down

# ログ確認
docker compose logs -f [サービス名]

# コンテナに入る
docker compose exec wordpress bash
```

### PHP関連
```bash
# Lint
docker compose exec wordpress composer lint

# Fix  
docker compose exec wordpress composer fix

# Test
docker compose exec wordpress composer test
```

### JavaScript/CSS関連
```bash
# ビルド
docker compose exec wordpress bash -c "cd themes/hametuha && npm run package"

# 監視モード
docker compose exec wordpress bash -c "cd themes/hametuha && npm run watch"
```

### WP-CLI
```bash
# composer経由のショートカット
composer wp [コマンド]

# Docker内で実行するWP-CLIコマンド
docker compose exec wordpress wp [コマンド]

# 例：プラグイン一覧
docker compose exec wordpress wp plugin list
```

## 注意事項

1. **node_modules**はGit管理しない（テーマの性質上）
2. **WordPressコア**は`wp/`にcomposerでインストール
3. **プラグイン**は`plugins/`にcomposerでインストール
4. **PhpStorm**では`wp/`をInclude Pathに追加してコードヒントを有効化

## 環境変数（.env）
```
# WordPress Database
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=wordpress
MYSQL_USER=wordpress
MYSQL_PASSWORD=wordpress

# WordPress Settings
WORDPRESS_VERSION=6.8  # composer.jsonで管理
WORDPRESS_TABLE_PREFIX=wp_
WORDPRESS_DEBUG=true

# Site URLs
WP_HOME=https://hametuha.info
WP_SITEURL=https://hametuha.info

# Port Settings (変更可能)
NGINX_PORT=80
NGINX_HTTPS_PORT=443
MYSQL_PORT=3307
PHPMYADMIN_PORT=8081
MAILPIT_SMTP_PORT=1026
MAILPIT_UI_PORT=8026

# SSL Settings (オプション)
SSL_ENABLED=true  # mkcertで証明書生成済み
SSL_CERT_PATH=docker/nginx/certs/hametuha.info.pem
SSL_KEY_PATH=docker/nginx/certs/hametuha.info-key.pem
```

## Docker Composeの利点
- 標準ポート（80/443）が使用可能
- 設定の柔軟性が高い
- 他のプロジェクトとの独立性
- カスタマイズが容易
- 本番環境に近い構成

## WordPressバージョン管理

### 統一されたバージョン管理
WordPressのバージョンは`.env`ファイルの`WORDPRESS_VERSION`で一元管理します。

### バージョン設定方法

#### 1. 初回セットアップ時
```bash
# .envファイルを作成
cp .env.example .env

# バージョンを確認・設定（composer.jsonで管理）
grep WORDPRESS_VERSION .env
```

#### 2. バージョン変更時
```bash
# バージョンを変更（例）
./bin/set-wordpress-version.sh 6.8.1

# 最新版に変更
./bin/set-wordpress-version.sh latest

# Composerパッケージを更新
composer update johnpbloch/wordpress

# テストスイートを再インストール
rm -rf wp-tests
./bin/install-wp-tests.sh

# Dockerコンテナを再起動
docker compose restart
```

### WordPressテストスイートのセットアップ

WordPressのPHPUnitテスト実行には、WordPress本体とは別にテストスイートが必要です。

#### セットアップ手順
```bash
# 1. WordPressテストスイートをインストール
#    （.envのWORDPRESS_VERSIONを自動的に使用）
./bin/install-wp-tests.sh

# 2. テストを実行
./bin/test.sh

# または直接実行
docker compose exec wordpress bash -c "cd /var/www/html/wp-content/themes/hametuha && composer test"
```

#### ディレクトリ構成
```
hametuha/
├── wp/                    # WordPressコア（Composer管理）
├── wp-tests/             # WordPressテストスイート（ローカル管理）
│   ├── includes/         # テストフレームワーク
│   ├── data/            # テストデータ
│   └── wp-tests-config.php  # テスト設定
```

#### 利点
- **IDEサポート**: ローカルファイルでコードヒント・自動補完が効く
- **バージョン統一**: WordPressコアとテストスイートのバージョンが一致
- **高速実行**: Docker内tmpではなくマウントされたローカルファイル

### PHPUnitテストの実行

#### テーマのテスト実行
```bash
# 推奨方法
./bin/test.sh

# Composer経由
composer test

# 直接実行
docker compose exec wordpress bash -c "cd /var/www/html/wp-content/themes/hametuha && vendor/bin/phpunit"
```

#### 新しいテストの追加
1. `themes/hametuha/tests/`にテストファイルを作成
2. ファイル名は`test-*.php`の形式
3. クラス名は`Test_*`の形式
4. `WP_UnitTestCase`を継承

例:
```php
<?php
class Test_Example extends WP_UnitTestCase {
    public function test_something() {
        $this->assertTrue( true );
    }
}
```

## トラブルシューティング

### ポート競合の場合
`.env`ファイルでポート設定を変更：
```
NGINX_PORT=8080
NGINX_HTTPS_PORT=8443
MYSQL_PORT=3308
PHPMYADMIN_PORT=8082
MAILPIT_UI_PORT=8027
```

### 権限エラーの場合
```bash
# uploads/pluginsディレクトリの権限を修正
docker compose exec wordpress chown -R www-data:www-data /var/www/html/wp-content/
```

### テストが失敗する場合
```bash
# テストスイートを再インストール
rm -rf wp-tests
./bin/install-wp-tests.sh

# テストデータベースを再作成
docker compose exec mysql mysql -u root -proot -e "DROP DATABASE IF EXISTS wordpress_test; CREATE DATABASE wordpress_test;"
```

## GitHub Actions デプロイ設定

### 必要な設定

#### Environment Variables (Settings → Environments → production → Variables)
- `DEPLOY_PATH`: `/var/www/vhosts/hametuha.com/wp-content/themes/hametuha`
- `DEPLOY_HOST`: EC2インスタンスのホスト名またはIP

#### Secrets (Settings → Secrets and variables → Actions)
- `DEPLOY_USER`: SSHユーザー名（セキュリティのため秘匿）
- `DEPLOY_SSH_KEY`: EC2インスタンスへのSSH秘密鍵

### SSH鍵の設定方法
```bash
# 1. SSH鍵ペアを生成（既存のものがあれば不要）
ssh-keygen -t ed25519 -C "github-actions@hametuha.com" -f deploy_key

# 2. 公開鍵をEC2インスタンスに追加
ssh ec2-user@your-ec2-instance 'echo "YOUR_PUBLIC_KEY" >> ~/.ssh/authorized_keys'

# 3. 秘密鍵をGitHub Secretsに追加
# deploy_keyファイルの内容を DEPLOY_SSH_KEY として登録
```

### デプロイフロー
1. **開発** → `master` (PR) - テスト実行
2. `master` マージ → `release` へのPR自動作成
3. `release` PRマージ → リリースドラフト作成
4. リリース公開 → 本番環境へ自動デプロイ

## 今後の課題
- GCP移行時の設定変更（変数名は汎用的なので最小限の変更で済む）
- CI/CDパイプラインの最適化

#genai #claude
