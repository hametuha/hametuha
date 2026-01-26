# Hametuha Docker開発環境

## 概要
wp-content相当をリポジトリルートとして管理する構成。

## ディレクトリ構成
```
hametuha/ (リポジトリルート)
├── docker-compose.yml
├── .env.example
├── wp/                   # WordPressコア（composerで管理、.gitignore）
├── wp-tests/             # WordPressテストスイート（ローカル管理）
│   ├── includes/         # テストフレームワーク
│   ├── data/             # テストデータ
│   └── wp-tests-config.php  # テスト設定
├── themes/
│   └── hametuha/    # テーマファイル
├── plugins/         # プラグイン（composer管理、.gitignore）
│   ├── hamelp/      # このプラグインだけcomposerではなく、単体のGitリポジトリとして一時的に管理
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
- **Hameticonフォントプレビュー**: [https://hametuha.info/wp-content/themes/hametuha/tests/hameticon-preview.php](https://hametuha.info/wp-content/themes/hametuha/tests/hameticon-preview.php)

### サービス説明
- **phpMyAdmin**: データベースの管理・操作
- **Mailpit**: WordPressから送信されるメールの監視・確認
- **MySQL**: SequelProやTablePlusなどでの外部接続用
- **Hameticonフォントプレビュー**: カスタムアイコンフォント（hameticon）の全アイコンをプレビュー・検索できるツール

## 開発コマンド

コマンドにはDockerの中で実行した方がよいもの（例・phpunit）とそうでないもの（例・rootのcomposerインストール）があります。
それらの差分を吸収したものとして、composer.jsonに composer scriptsを定義してあります。
実際の中身はcomposer.jsonを参照してください。

### Dockerコンテナ操作

```bash
# コンテナ起動
composer start
# コンテナ停止
composer stop
# コンテナ再起動
composer restart
# ログ確認
composer logs [サービス名]
# WordPressのdebug.logを見る
composer logs:debug
# コンテナに入る
docker compose exec wordpress bash
```

### PHP関連

PHPの構文チェック、単体テスト用のコマンドです。

```bash
# Lint
composer lint
# Fix  
composer fix
# Unit Test
composer test
```

### JavaScript/CSS関連

JS/CSSのトランスパイルおよび構文チェックは基本的にthemes/hametuhaの中で行います。

```bash
# ビルド
cd themes/hametuha && npm run package
# CSSのビルド
cd themes/hametuha && npm run sass
# JSのビルド（3種類あり、1つに統合予定）
cd themes/hametuha && npm run commonjs # assets/js/src/common の中を連結して assets/js/src/common.js にする（廃止予定）
cd themes/hametuha && npm run js # JSをミニファイする（廃止予定）
cd themes/hametuha && npm run jsx # JSXを含むES Nextの記法をトランスパイルする
# 監視モード
cd themes/hametuha && npm run watch 
```

トランスパイルには @kunoichi/grab-deps を一部使っており、これらのファイルではファイルヘッダーに依存関係が書いてあります。

```js
/*!
 * フォロワーUI
 *
 * @handle hametuha-hb-followers
 * @deps wp-api-fetch, wp-element, wp-i18n, hametuha-loading-indicator, hametuha-pagination, wp-url
 */
```

これらの情報は wp-settings.json にまとめられ、PHPから自動で読み取られます。
ハンドル名（@deps）や依存関係（@deps）が自動的に解決されます。
grab-desp対応のJSファイルは便宜的に拡張子をJSXにしています。

### WP-CLI

Word

```bash
# composer経由のショートカット
composer wp [コマンド]

# 例：プラグイン一覧
composer wp plugin list
```

## ローカル環境専用機能

### 自動ログイン機能

ローカル環境（`wp_get_environment_type() === 'local'`）でのみ動作する、テスト用の自動ログイン機能が実装されています。Chrome DevTools MCPなどクッキーを保持できない環境でのテスト用です。

#### 使い方

`wp-config-local.php` に以下の定数を設定すると、すべてのリクエストで指定されたユーザーとして扱われます：

```php
// 自動ログイン機能
define( 'HAMETUHA_LOGGED_IN_AS', 'user_login' );

// reCAPTCHA検証をスキップ（オプション）
define( 'SKIP_RECAPTCHA_VERIFICATION', true );
```

#### 注意事項

- ローカル環境でのみ動作します（本番環境では無効）
- 指定する `user_login` は存在しているものでなければなりません。 `composer wp user list` などで検索ができます。
- `wp-config-local.php` はdockerの起動時にしか同期されません。変更した場合は再起動 `composer restart` してください。
- フロントエンド・保護ページ・wp-admin管理画面すべてにアクセス可能になります

## 注意事項

1. **node_modules**はGit管理しない（テーマの性質上）
2. **WordPressコア**は`wp/`にcomposerでインストール
3. **プラグイン**は`plugins/`にcomposerでインストール
4. **PhpStorm**では`wp/`をInclude Pathに追加してコードヒントを有効化

## Feature Group タグシステム

### 概要

`@feature-group` タグは、物理的なディレクトリ構造にかかわらず、機能ごとに関連ファイルを論理的にグループ化するためのドキュメントタグです。

### 目的

WordPressテーマの開発では、1つの機能に関連するファイルが複数のディレクトリに散らばることがよくあります：

- テンプレートファイル: `templates/news/`, `single-*.php`, `archive-*.php`
- パーツテンプレート: `parts/loop-*.php`
- スタイル: `assets/sass/parts/_*.scss`
- JavaScript: `assets/js/src/components/*.jsx`
- PHPクラス: `src/Hametuha/Model/`, `src/Hametuha/Widget/`
- フック: `hooks/*.php`
- 関数: `functions/*.php`

このような分散したファイルを機能単位で管理するため、`@feature-group` タグを使用してファイルにメタデータを付与します。

### メリット

1. **検索性の向上**: grepやIDEの検索機能で機能に関連するすべてのファイルを一度に見つけられる
2. **関心の分離**: 物理的な配置を変えずに論理的なグループ化が可能
3. **自己文書化**: コード内でそのファイルがどの機能に属するかが明確になる
4. **多言語対応**: PHP、SCSS、JSX、その他すべてのファイル形式で使用可能

### 使用方法

#### PHP テンプレートファイル

```php
<?php
/**
 * ニュースアーカイブテンプレート
 *
 * @feature-group news
 */
get_header();
// ...
```

#### PHP クラスファイル

```php
<?php
/**
 * News command
 *
 * @feature-group news
 * @package Hametuha\Commands
 */
class News extends Command {
    // ...
}
```

#### SCSS ファイル

```scss
/**
 * ニュースのスタイル
 *
 * @feature-group news
 */
.news {
    // ...
}
```

#### JSX ファイル

```jsx
/*!
 * 安否報告投稿用のスクリプト
 *
 * @feature-group anpi
 * @handle hametuha-components-anpi-submit
 * @deps wp-element, wp-i18n, wp-api-fetch
 */
const AnpiSubmitComponent = () => {
    // ...
};
```

### 検索方法

#### コマンドライン（grep）

```bash
# 特定のfeature-groupに属するすべてのファイルを検索
grep -r "@feature-group news" themes/hametuha/

# ファイル名のみを表示
grep -rl "@feature-group news" themes/hametuha/

# 複数のグループを検索
grep -r "@feature-group \(news\|ideas\|anpi\)" themes/hametuha/
```

#### IDE検索

- PhpStorm/VSCodeなどのIDE内検索で `@feature-group news` を検索
- 正規表現検索: `@feature-group (news|ideas|anpi)`

### 既存のFeature Groups

- **thread** スレッド（掲示板）機能に関連するファイル群
- **news** ニュース機能に関連するファイル群
- **ideas** アイデア投稿機能に関連するファイル群
- **anpi** 安否情報機能に関連するファイル群
- **series** 連載機能に関連するファイル群

### 新しいFeature Groupの追加

新しい機能を開発する際は：

1. **グループ名を決定**: 簡潔で分かりやすい名前（例: `campaign`, `book-review`）
2. **関連ファイルにタグを追加**: 各ファイルのコメントブロックに `@feature-group [グループ名]` を記載
3. **このドキュメントを更新**: 新しいグループを「既存のFeature Groups」セクションに追加

### 注意事項

- グループ名は小文字とハイフンを使用（例: `news`, `book-review`, `user-profile`）
- 1つのファイルが複数のグループに属することも可能（必要に応じて）
- WordPressのテンプレート階層を優先し、汎用的な部品は `get_template_part('parts/loop', get_post_type())` のようにポストタイプで呼び分ける

## WordPressバージョン管理

### 統一されたバージョン管理

WordPressのバージョンは`.env`ファイルの`WORDPRESS_VERSION`で一元管理します。
`.env`ファイルは `.env.example` を元に作成してください。

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
composer test
```

#### ディレクトリ構成
```
hametuha/
├── wp/                   # WordPressコア（Composer管理）
├── wp-tests/             # WordPressテストスイート（ローカル管理）
│   ├── includes/        # テストフレームワーク
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
# Composer経由
composer test

# 直接実行
docker compose exec wordpress bash -c "cd /var/www/html/wp-content/themes/hametuha && vendor/bin/phpunit"
```

#### 新しいテストの追加
1. `themes/hametuha/tests/`にテストファイルを作成
2. ファイル名は`Test_*.php`の形式（クラス名と一致）
3. クラス名は`Test_*`の形式（スネークケース）
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
