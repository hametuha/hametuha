# Hametuha Docker開発環境

## 作業ルール（Claude 向け）

- **セッション開始時に `tasks/*.md`（`tasks/todo.md`・`tasks/lessons.md` など）を読む。** これらは `.gitignore` 済みのローカル作業メモで、過去の教訓や進行中タスクが記録されている。同じミスを繰り返さないため、着手前に必ず目を通す。
- **コミット直前に `git branch --show-current` で現在ブランチを確認する。** 作業中に IDE 等でブランチが切り替わっていることがあるため、デフォルトブランチ回避だけでなく「いまのタスクに適したブランチか」まで毎回チェックする。想定と違えば `git cherry-pick` で正しいブランチへ移し、誤った側は元に戻す。

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

JS/CSSのビルドは themes/hametuha の中で行います。破滅派のプラグイン群と同じく、
**@kunoichi/grab-deps（中で @wordpress/scripts）＋ sass ＋ postcss（autoprefixer）** を使います（#361 で gulp から移行）。

| 種類 | ソース | 出力 |
|---|---|---|
| JS | `assets-src/js/**/*.js` | `assets/js/` |
| SCSS | `assets-src/scss/` | `assets/css/` |
| エディタ用ブロック（block.json） | `assets-src/blocks/<名前>/` | `assets/blocks/<名前>/`（wp-scripts） |
| そのままコピーするライブラリ（Bootstrap＋bootbox、select2） | `node_modules` | `assets/vendor/`（`bin/copy-libs.js`） |
| 画像・フォント | `assets/img/`・`assets/fonts/` | （ソースのまま。Git 管理） |

**`assets-src/` がソース、`assets/` が配布物。** `assets/` のうち `js`・`css`・`blocks`・`vendor` と `wp-dependencies.json` はビルドの生成物（`.gitignore` 済み）。
デプロイ時は CI でビルドし、`assets-src/` は配布物から除外される（`.distignore`）。

```bash
# ビルド（clean → copy-libs → build:css → build:blocks → build:js → dump）
cd themes/hametuha && npm run package
# CSSだけ / JSだけ（JSは最後に wp-dependencies.json も作り直す）
cd themes/hametuha && npm run build:css
cd themes/hametuha && npm run build:js
# 監視モード
cd themes/hametuha && npm run watch
```

#### リント（ESLint・Stylelint）

**WordPress 公式の設定（wp-scripts の既定）をそのまま使い、テーマ固有の差分だけを足しています。** 設定は `themes/hametuha/eslint.config.cjs` と `.stylelintrc.json`。

```bash
cd themes/hametuha && npm run lint      # JS と SCSS
cd themes/hametuha && npm run fix       # 自動修正できるものを直す
```

- **コミット時に自動で走ります**（husky ＋ lint-staged。ステージしたファイルだけ）。フックは `npm install` の `prepare` で有効になる。CI（`Lint JS/CSS`）でも全体を検査する
- **既存の違反（未使用変数・JSDoc・alert など）は `eslint-suppressions.json` に記録して見逃しています。** 新しく書いたコードには効く。
  記録済みの違反を直すと「使われていない抑制」としてエラーになるので、`npx wp-scripts lint-js --prune-suppressions` で記録を減らしてコミットする。記録を増やす（`--suppress-all`）のは原則しない
- 対象外: 同梱ライブラリ（`common/headroom.js`）、アイコンフォントの定義（`_icon.scss`）
- Stylelint はフォント名と `page-break-*` を書き換えないようにしてある（ePub リーダー向け。大文字小文字や `break-after` を解釈しないリーダーがある）
- 整形だけのコミットは `.git-blame-ignore-revs` に載せている。ローカルの `git blame` では `git config blame.ignoreRevsFile .git-blame-ignore-revs` を一度実行する

#### 画像（assets/img）

画像はビルドで圧縮しません。**追加・差し替えたときに一度だけ圧縮し、圧縮後のファイルをコミットします。**

```bash
# Git 上で追加・変更された assets/img の画像を、その場で圧縮する
cd themes/hametuha && npm run compress-images
# ファイルやディレクトリを指定する
cd themes/hametuha && npm run compress-images -- assets/img/foo.png
```

- JPEG・PNG のみ対象（`sharp` を使用）。小さくならなかったファイルは書き換えない
- 2回実行すると再圧縮で画質が落ちるので、**1枚につき1回だけ**
- かつては `assets/img/src` の原本を gulp-imagemin でデプロイのたびに圧縮していたが、
  ネイティブバイナリ依存で Node 22 以降・Apple Silicon で動かなくなったため廃止した（#434）。原本は Git 履歴にある

Node のバージョンはリポジトリルートの `.node-version` で一元管理しています（CI もこれを読む）。

#### JS の書き方と依存関係（grab-deps）

`assets-src/js` 以下の **.js ファイルは1本ずつ独立したスクリプト（ハンドル）としてビルド**され、
`wp-dependencies.json` に登録されます。PHP（`hooks/assets.php`）がこれを読んで `wp_register_script` します。

- **JSX も .js に書く**（以前は grab-deps 対応ファイルだけ便宜的に .jsx にしていたが、wp-scripts が .js の JSX を扱うので統一した）
- **ハンドル名**は、ファイル先頭の `@handle` があればそれ。無ければ `hametuha-<ディレクトリ>-<ファイル名>` が自動で付く（`package.json` の `grabDeps`）
- **依存関係**は、`import` した `@wordpress/*`（`wp-element` 等）や `jquery`、JSX の `react-jsx-runtime` が自動で入る。
  グローバル変数で使うもの（`wp.apiFetch` を import せずに使う等）や、テーマ内の別ハンドルは `@deps` に書く

```js
/*!
 * フォロワーUI
 *
 * @handle hametuha-hb-followers
 * @deps wp-api-fetch, wp-element, wp-i18n, hametuha-loading-indicator, hametuha-pagination, wp-url
 */
```

- ファイル同士は普通に `import` できる（読み込まれた側は呼び出し元にバンドルされる）。
  `common.js` はかつて `common/*.js` を連結していたが、いまは import でまとめている
- **`assets-src/js` の中に `blocks` という名前のディレクトリを作らない。** grab-deps がビルド後に出力先の `blocks` ディレクトリを丸ごと削除する
- **エディタ用ブロックは `assets-src/blocks/<名前>/`**（`block.json` ＋ `index.js`）。wp-scripts でビルドし、`Hooks/Editor.php` が `assets/blocks/*/block.json` をすべて `register_block_type()` する。
  `save()` の出力を変えると既存の記事が「無効なブロック」になるので注意
- **テーマのアセット（`assets/js`・`assets/css`）は PHP で `wp_register_*` しない。** `hooks/assets.php` が `wp-dependencies.json` をまとめて登録する（#478）。
  使う側は `wp_enqueue_script( 'ハンドル名' )` だけを書く。バージョンはファイルの hash なので、中身が変われば URL も変わる
  （`hametuha_version()` は `style.css` の `Version:` を返し、ローカルでは `nightly` 固定になってブラウザが古い版をキャッシュするので使わない）
- SCSS にも同じヘッダーが書ける（`/*!` で始めると圧縮後も残る）。`@charset` がある場合はその直後に置く
- **`@handle` にファイル名と同じ名前（`series-helper.js` に `@handle series-helper` など）を書くと無視され、自動の名前になる。** grab-deps が「未指定」と区別できないため
- PHP で手動登録するのは、外部 CDN（unpkg・FontAwesome 等）と `assets/vendor/`（Bootstrap・select2）だけ

### WP-CLI

Word

```bash
# composer経由のショートカット
composer wp [コマンド]

# 例：プラグイン一覧
composer wp plugin list
```

## ローカル環境専用機能

### 管理画面をブラウザ自動操作で検証する

**Playwright MCP で実ログインしてください。** Playwright はクッキーを保持できるので、GET も POST（保存）も検証できます。

一時的な管理者を作って使い、終わったら削除するのが安全です（実ユーザーのパスワードを触る必要がありません）。

```bash
# 1. 一時管理者を作成（パスワードは控えておく）
openssl rand -base64 18
composer wp -- user create claude_test claude-test@example.com --role=administrator --user_pass="<上で出たパスワード>"

# 2. Playwright で https://hametuha.info/wp-login.php からログインし、検証する

# 3. 後始末
composer wp -- user delete <ID> --yes
```

検証で汚したデータ（メタ・投稿・アップロードファイル等）は必ず元に戻してください。`post_modified` は `wp post update` だと再度更新されてしまうので、戻すときは `wp db query` で直接 UPDATE します。

#### Cloudflare Turnstile（ログイン・登録・CF7 のボット対策）

ボット対策は Cloudflare Turnstile です（#458）。**担当が2つに分かれている**ので注意。

| 対象 | 担当 | スクリプトのハンドル |
|---|---|---|
| ログイン・登録・パスワードリセット | プラグイン `simple-cloudflare-turnstile` | `cfturnstile` |
| Contact Form 7 | **CF7 本体の Turnstile 連携**（「インテグレーション」画面） | `cloudflare-turnstile` |

かつてはテーマ独自の reCAPTCHA v3（`Hametuha\Service\RecaptchaV3`）でしたが、削除しました。
CF7 本体の連携はフォームに `[turnstile]` タグが無くても自動でウィジェットを差し込み、スクリプトは全ページで読み込むため、
CF7 の無いページでは `hooks/assets.php` で dequeue しています。

ローカルでは Cloudflare 公式の**常に通るテスト用キー**を使います。ウィジェットに「テスト専用です」と出ますが、
ブラウザ操作（Playwright）でもそのままログイン・フォーム送信できるので、スキップ用の定数はありません。

```bash
# ログインまわり（simple-cloudflare-turnstile）
composer wp -- option update cfturnstile_key 1x00000000000000000000AA
composer wp -- option update cfturnstile_secret 1x0000000000000000000000000000000AA
for o in cfturnstile_login cfturnstile_register cfturnstile_reset; do composer wp -- option update $o 1; done
# キー保存後の「テスト済み」フラグ。これが yes でないと連携が読み込まれない
composer wp -- option update cfturnstile_tested yes
# CF7（本体の連携）。プラグイン側の CF7 連携（cfturnstile_cf7_all）は本番で使っていないので有効にしない
composer wp -- eval 'WPCF7::update_option( "turnstile", [ "1x00000000000000000000AA" => "1x0000000000000000000000000000000AA" ] );'
```

- ログイン・登録画面の並び順はテーマの `assets-src/js/components/login-helper.js` が「または」の区切りの上へ移しています。
  ウィジェット（`.cf-turnstile`）もこのリストに入れているので、フォームに要素を足すときはここも見ること
- 古い `wp-config-local.php` に `SKIP_RECAPTCHA_VERIFICATION` が残っていても、いまは何の効果もありません

#### かつて存在した自動ログイン機能について

`HAMETUHA_LOGGED_IN_AS` 定数で全リクエストを特定ユーザー扱いにする仕組みが `mu-plugins/hametuha-config.php` にありましたが、削除しました。Chrome DevTools MCP がクッキーを保持できなかった時期の回避策で、以下の問題があったためです。

- 毎リクエストで `wp_set_auth_cookie()` を呼んで**新しいセッショントークンを発行**していた。nonce は `wp_get_session_token()` に紐づくため、GET で描画したフォームを POST すると必ず nonce 検証に失敗し、**保存は「リンクの期限が切れています」403 になった**（＝GET 専用だった）
- `determine_current_user` に割り込むため、未ログイン状態の表示を検証できなかった
- セッショントークンが無限に増えた

古い `wp-config-local.php` にこの定数が残っていても、いまは何の効果もありません。削除して構いません。

## ログイン中ユーザーへの緊急告知（#452）

php-fpm の再起動など、一瞬サイトが止まる作業の前に、**いまサイトを見ているログイン中のユーザー**へトーストで知らせる仕組み。

- **操作は「設定 → 緊急告知」**（`src/Hametuha/Hooks/EmergencyNotice.php`）。タイトル・本文・期限を入れて保存すると、`wp-content/uploads/notification.json` に書き出す。「告知を取り下げる」か本文を空にして保存すると、ファイルを消す
- **正は option（`hametuha_emergency_notice`）、ファイルは書き出しにすぎない。** ファイルが消えても、画面で「保存」し直せば戻る（値が同じでも書き出す）。`register_setting` は `init` で登録しているので、`wp option update` から保存しても同じく整形・書き出しされる
- `assets-src/js/components/emergency-notice.js` がこのファイルを120秒ごとに取得し、あれば表示する。出し方は画面で変える
  - フロント・hashboard: トースト（`hametuha-toast`）
  - 管理画面のブロックエディター: エディター上部の通知（`core/notices`）
  - それ以外の管理画面（**原稿（`post`）はクラシックエディター**）: 見出しの下の `.notice-warning`
  - 管理画面にはトーストの CSS（`app.css`）が無いので、トーストは使わない
- タイトルか本文を変えると id が変わり、一度閉じた人にももう一度出る。期限だけの変更では出し直さない。ユーザーが×で閉じると `localStorage` に記録される（閉じずにページを移動したら次のページでもう一度出る）
- 期限を過ぎたら出ない（消し忘れ対策）。本文・タイトルはテキストとして表示する（HTML は使えない）
- 反映は最大120秒遅れる。URL に `?t=<120秒ごとの番号>` を付けるので、同じ窓の中ではキャッシュが効く
- JSON は公開 URL なので、機微な内容は書かない
- **平常時はファイルが無く 404 になる。nginx でこのパスを PHP に渡さないこと。** 渡すと、ログイン中の全員が120秒ごとに WordPress の404ページを生成させることになる。
  ローカルは `docker/nginx/default.conf`、本番は hametuha/hametuha-infra の `05-cloudflare-ssl.sh` に `location = /wp-content/uploads/notification.json { try_files $uri =404; }` を入れてある（hametuha/hametuha-infra#51）
- uploads を S3 などに移したら、`EmergencyNotice::publish()` の書き出し先を変える必要がある（いまはローカルファイルに `file_put_contents` している）

## 管理者の2要素認証（#430）

Two Factor プラグイン（wp.org `two-factor`）＋ `themes/hametuha/src/Hametuha/Hooks/TwoFactor.php`。

- **管理者だけが対象。** 使えるのは TOTP とバックアップコードのみ（メールは管理者のメール乗っ取りでパスワードリセットと同時に破られるので不可）。管理者以外には一切提供しない（hashboard のためプロフィール画面に入れない）
- **未設定の管理者には管理画面で通知するだけ。** 権限は止めない（管理者は1人で、本番有効化時に自分で設定する前提）
- **ソーシャルログイン（Gianism / hameslack）は2FAを通らない。** `wp_login` を発火せず `wp_set_auth_cookie()` を直接呼ぶため。連携先（Google・Facebook・Slack 等）の乗っ取り対策を信頼して当面は許可している。前提は**管理者が連携しているアカウントすべてに2FAが設定されていること**
  - 制御は Gianism 本体で行う予定（hametuha/gianism#179）。hameslack は Gianism の `set_auth_cookie()` を経由しておらずフックが効かない（hametuha/hameslack#58）
  - 一度「2FAを通っていないセッションでは管理者権限を落とす」実装を試したが、仕組みが分かりにくく、ソーシャルログインを許す方針とも両立しないため採用しなかった（PR #431 の履歴参照）
- 端末の記憶機能は無い（WordPress/two-factor#230）。「ログイン状態を保存」で14日に1回入力する程度

### ロックアウトしたときの復旧

認証アプリもバックアップコードも失った場合は、WP-CLI で2要素認証の設定を消す。

```bash
wp @production user meta delete <ユーザーID> _two_factor_enabled_providers
```

パスワードだけでログインできるようになるので、プロフィール画面で設定し直す。

## 注意事項

1. **node_modules**はGit管理しない（テーマの性質上）
2. **WordPressコア**は`wp/`にcomposerでインストール
3. **プラグイン**は`plugins/`にcomposerでインストール
4. **PhpStorm**では`wp/`をInclude Pathに追加してコードヒントを有効化

## プラグイン管理方針（composerに載せるもの・載せないもの）

`composer.json` は「ローカルで本番を再現するための構成定義」です。本番で動いているプラグインは
原則すべて composer 管理下に置きますが、**ローカルで動かないもの・再現する意味がないものは
意図的に除外します。** 載せても動かないなら、依存の記述として嘘になるためです。

### 除外するもの

| 分類 | 該当プラグイン | 理由 |
|---|---|---|
| キャッシュ系 | `hamecache` / `memcached` / `mo-cache` | `hamecache` は Cloudflare 連携、`memcached` は object-cache ドロップイン。いずれもローカルに対応するインフラが無く動作しない |
| 外部要因対応 | `ads-txt` / `robots-txt-editor` | ads.txt / robots.txt は外部（広告配信・クローラ）向けの出力。ローカルで再現する意味がない |
| 外部API依存 | `akismet` | スパム判定は Akismet のサーバ側。APIキーの無いローカルでは実質動作しない |
| private リポジトリ | `gianism-mixi` / `selected-post-for-contact-form-7` | **下記のとおり性質が異なる。CI を壊さないための妥協** |

**このため、キャッシュ絡みの不具合はローカルでは原理的に再現できません。**
キャッシュが疑わしい調査は本番側のログ・`wp @production` での確認に頼ること。

#### private リポジトリの2本について（他の除外とは意味が違う）

上3分類は「ローカルで動かない／再現する意味がない」ものですが、**この2本は本番で現役稼働しており、
ローカルでも動くはずのもの**です。composer に載せていないのは技術的な妥協であり、
**ローカルが本番を再現できていない既知の穴**として扱うこと。

- `gianism-mixi` — 破滅派の有料アドオン（mixi ログイン）。`hametuha/gianism-mixi` (private)
- `selected-post-for-contact-form-7` — 「この投稿の作者に問い合わせる」CF7 拡張。`tarosky/selected-post-for-contact-form-7` (private)

**載せられない理由:** ルート `composer.json` に private リポジトリを足すと、CI の
`lint-php` ジョブ（`tarosky/workflows/.github/workflows/phpcs.yml@main`）が落ちる。
このワークフローはルートで `composer install` を実行するが、トークン入力を持たず、
Actions の `secrets.GITHUB_TOKEN` は呼び出し元リポジトリにしかスコープが無いため
他リポジトリの private を読めない。解決するには共有ワークフロー側の改修が必要で、
影響が破滅派の外（他の Tarosky プロジェクト）に及ぶ。

**この2本が絡む挙動を検証するときは、手動で `plugins/` に配置すること。**

```bash
gh repo clone hametuha/gianism-mixi plugins/gianism-mixi
gh repo clone tarosky/selected-post-for-contact-form-7 plugins/selected-post-for-contact-form-7
```

`plugins/` は `.gitignore` 済みなので、置いても Git には入らない。

**再検討の条件:** private プラグインが増えて手動配置が現実的でなくなったら、
共有ワークフローにトークン入力を足して `vcs` リポジトリ方式へ移行する。

### 除外の判断基準

新しいプラグインを本番に入れたとき、composer に載せるかは次で判断する。

1. **ローカルで実際に動くか。** 外部SaaS・特定ホスティング機能に依存していて
   ローカルでは起動すらしないなら除外。
2. **ローカルで再現する価値があるか。** 出力先が外部にしか無いもの（ads.txt、robots.txt、
   外部監視タグ等）は除外。
3. 上記に当てはまらなければ **載せる。** 「たぶん使わない」は理由にならない。
   載っていないプラグインはローカルに存在せず、その分ローカルは本番と別物になる。

### 除外したものの記録

除外は `composer.json` に痕跡が残らないため、**この表が唯一の記録**です。
本番から消した・増やした場合はここを更新すること。

### 入手経路が無いプラグイン

wp.org にも自社 GitHub にも無く、composer で取得できないものは管理下に置けません。
**現時点では該当なし。**

このようなプラグインを見つけたら、管理下に置く方法を探すのではなく
**本番から消せないかをまず検討すること。** 取得経路が無いということは
更新経路も無いということで、脆弱性が出ても直せません。

- 2026-09-12: `maintenance-mode` (5.4) を本番から削除。wp.org で **2017年に公開停止**され、
  以降9年間更新されていなかった。破滅派は独自の `maintenance.php` ドロップイン
  （コアの `wp_maintenance()` が拾い、`themes/hametuha/503.php` を描画）を持っており、
  テーマ・mu-plugins からこのプラグインへの参照は1件も無かったため、機能的な影響なし。

## Feature Group タグシステム

### 概要

`@feature-group` タグは、物理的なディレクトリ構造にかかわらず、機能ごとに関連ファイルを論理的にグループ化するためのドキュメントタグです。

### 目的

WordPressテーマの開発では、1つの機能に関連するファイルが複数のディレクトリに散らばることがよくあります：

- テンプレートファイル: `templates/news/`, `single-*.php`, `archive-*.php`
- パーツテンプレート: `parts/loop-*.php`
- スタイル: `assets-src/scss/parts/_*.scss`
- JavaScript: `assets-src/js/components/*.js`
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
- **two-factor** 管理者の2要素認証必須化に関連するファイル群
- **carousel** カルーセル（Swiper）に関連するファイル群
- **emergency-notice** ログイン中ユーザーへの緊急告知（`uploads/notification.json`）に関連するファイル群

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

## 本番ログ（CloudWatch Logs）

本番のログは CloudWatch Logs に集約されています。AWS の認証は direnv でこのリポジトリに固定します。

```bash
cp .envrc.example .envrc && direnv allow   # 初回のみ。.envrc は gitignore 済み
aws iam list-account-aliases               # "hametuha" であることを確認（他のアカウントと取り違えない）
```

| ロググループ | 中身 |
|---|---|
| `/aws/ec2/hametuha/php-fpm/error` | PHP のエラー（Fatal・メモリ枯渇など） |
| `/aws/ec2/hametuha/php-fpm/slow` | PHP-FPM のスローログ |
| `/aws/ec2/hametuha/nginx/access` / `error` | nginx のアクセス・エラー |
| `/aws/rds/instance/hametuha/slowquery` | RDS のスロークエリ |
| `/aws/lambda/production-hametuha-health-check` | 死活監視 |

**保持期間は30日**（RDS の error は90日、health-check は180日）。それより古い障害は追えないので、
イシューに書くときはログの該当行を本文に貼っておくこと。

```bash
# 例: 直近7日の PHP メモリ枯渇
aws logs filter-log-events --log-group-name /aws/ec2/hametuha/php-fpm/error \
  --start-time $(( ($(date +%s) - 7*86400) * 1000 )) \
  --filter-pattern '"Allowed memory size"' --query 'events[].message' --output text
```

## MCPサーバー（分析用）

サイトのアクセス解析・検索パフォーマンス分析のため、以下のMCPサーバーを利用できます。
**設定が有効な場合のみ**利用可能です（後述）。

### 利用できるサーバー

| サーバー名 | 用途 | 主なツール |
|---|---|---|
| `google-analytics-mcp` | Google Analytics 4（アクセス解析） | `get_account_summaries`, `run_report`, `run_realtime_report`, `run_funnel_report` など |
| `google-search-console` | Google Search Console（検索パフォーマンス） | `list_sites`, `search_analytics`, `enhanced_search_analytics`, `detect_quick_wins` など |

### 対象サイト

- **hametuha.com**
  - GA4プロパティ: `properties/338754670`（オンライン文芸誌 破滅派 - GA4 / アカウント「破滅派　運営サイト」）
  - Search Consoleサイト: `sc-domain:hametuha.com`

### 前提・注意事項

- **`.mcp.json` はGit管理していません**（`.gitignore` 済み）。認証用のサービスアカウント鍵へのパスを含むため、共有しません。
  - したがって、これらのMCPサーバーは **`.mcp.json` が各自のローカルに設定されていれば使える** という位置づけです。設定がなければツール自体が現れません。
  - 設定例（鍵のパスは各自の環境に合わせる）: `google-analytics-mcp` は `pipx run analytics-mcp`、`google-search-console` は `npx -y mcp-server-gsc` を使い、`GOOGLE_APPLICATION_CREDENTIALS` にサービスアカウント鍵のパスを指定。
- 権限は **分析（読み取り）用途のみ**を想定。Search Consoleは `siteRestrictedUser` 権限のため、サイトマップ送信やインデックス登録などの書き込み系操作は使えません。

## 今後の課題
- GCP移行時の設定変更（変数名は汎用的なので最小限の変更で済む）
- CI/CDパイプラインの最適化

#genai #claude
