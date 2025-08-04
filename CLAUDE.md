# Hametuha Docker開発環境

## 概要
@wordpress/envからDocker Composeベースの開発環境へ移行。wp-content相当をリポジトリルートとして管理する構成。

**注意**: プロダクション環境はPHP 7.2.34ですが、開発環境では古いDebianリポジトリの問題によりPHP 7.4を使用しています。

## ディレクトリ構成
```
hametuha/ (リポジトリルート)
├── docker-compose.yml
├── .env.example
├── wp/              # WordPressコア（composer管理、.gitignore）
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

## 主な変更点

### 1. ファイル移動
- テーマファイルを`themes/hametuha/`に移動（git mvで履歴保持）
- `.github/`などの設定ファイルも含めて移動

### 2. Composer設定
- ルートの`composer.json`で全ての依存関係を管理
- wpackagistでWordPressプラグインを管理
- テーマ固有の依存関係（hametuha/wpametu等）も統合
- phpcs/phpunitもルートで管理

### 3. Docker Compose設定
- WordPress（PHP-FPM）、Nginx、MySQL、phpMyAdminのコンテナ構成
- ポート80/443を標準で使用
- volumeマウントでWordPress標準構造を維持
  - `./themes` → `/var/www/html/wp-content/themes`
  - `./plugins` → `/var/www/html/wp-content/plugins`
  - `./uploads` → `/var/www/html/wp-content/uploads`

### 4. 開発環境の特徴
- 標準ポート（80/443）を使用可能
- ホットリロード対応
- Xdebug対応（オプション）
- WP-CLIを含む

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
# 一般的なWP-CLIコマンド
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
# WordPress
WORDPRESS_DB_HOST=mysql:3306
WORDPRESS_DB_NAME=wordpress
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=wordpress
WORDPRESS_TABLE_PREFIX=wp_
WORDPRESS_DEBUG=true

# MySQL
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=wordpress
MYSQL_USER=wordpress
MYSQL_PASSWORD=wordpress

# サイトURL
WP_HOME=https://hametuha.info
WP_SITEURL=https://hametuha.info
```

## Docker Composeの利点
- 標準ポート（80/443）が使用可能
- 設定の柔軟性が高い
- 他のプロジェクトとの独立性
- カスタマイズが容易
- 本番環境に近い構成

## トラブルシューティング

### ポート競合の場合
`.env`ファイルで以下を変更：
```
NGINX_PORT=8080
NGINX_HTTPS_PORT=8443
```

### 権限エラーの場合
```bash
# uploads/pluginsディレクトリの権限を修正
docker compose exec wordpress chown -R www-data:www-data /var/www/html/wp-content/
```

## 今後の課題
- 本番環境へのデプロイフローの確立
- CI/CDパイプラインの最適化
- 開発者向けドキュメントの整備
- SSL証明書の自動化（Let's Encryptなど）

#genai #claude