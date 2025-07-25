# Hametuha DDEV移行作業まとめ

## 概要
@wordpress/envからDDEVへの移行作業を実施。wp-content相当をリポジトリルートとして管理する構成に変更。

## 新しいディレクトリ構成
```
hametuha/ (リポジトリルート)
├── .ddev/
│   └── config.yaml
├── wp/              # WordPressコア（composer管理、.gitignore）
├── themes/
│   └── hametuha/    # テーマファイル
├── plugins/         # プラグイン（composer管理、.gitignore）
├── uploads/         # アップロード（.gitignore）
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

### 3. DDEV設定
- シンボリックリンクでWordPress標準構造を維持
- `themes/hametuha` → `wp-content/themes/hametuha`
- `plugins/` → `wp-content/plugins`

### 4. GitHub Actions対応
- DDEVコンテナ外でも同じコマンドが実行可能
- `composer install`（ルート）
- `cd themes/hametuha && npm install`

## 新環境でのセットアップ手順

```bash
# リポジトリをクローン
git clone [repository-url] hametuha
cd hametuha

# DDEV起動
ddev start

# 依存関係インストール
ddev composer install
cd themes/hametuha && npm install

# 開発開始
ddev exec -d /var/www/html/themes/hametuha npm run watch
```

## 開発コマンド

### PHP関連
```bash
# Lint
ddev composer lint

# Fix
ddev composer fix

# Test
ddev composer test
```

### JavaScript/CSS関連
```bash
# ビルド
cd themes/hametuha && npm run package

# 監視モード
cd themes/hametuha && npm run watch
```

## 注意事項

1. **node_modules**はGit管理しない（テーマの性質上）
2. **WordPressコア**は`wp/`にcomposerでインストール
3. **プラグイン**は`plugins/`にcomposerでインストール
4. **PhpStorm**では`wp/`をInclude Pathに追加してコードヒントを有効化

## 環境変数
- `WP_HOME`: https://hametuha.ddev.site
- `WP_SITEURL`: https://hametuha.ddev.site

## 今後の課題
- 本番環境へのデプロイフローの確立
- CI/CDパイプラインの最適化
- 開発者向けドキュメントの整備

#genai #claude