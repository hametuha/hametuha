#!/bin/bash

# WordPressバージョンを設定するスクリプト
set -e

# 引数チェック
if [ $# -lt 1 ]; then
    echo "Usage: $0 <wordpress-version>"
    echo "Example: $0 6.1.7"
    echo "Example: $0 latest"
    exit 1
fi

WP_VERSION=$1

# sed -i の書き方は GNU 版と BSD 版（macOS 標準）で違う。OS ではなく sed 自体で判定する
# （macOS でも Homebrew の GNU sed が sed として入っていることがある）
if sed --version >/dev/null 2>&1; then
    sed_inplace() { sed -i "$@"; }
else
    sed_inplace() { sed -i '' "$@"; }
fi

# .envファイルのバージョンを更新
if [ -f .env ]; then
    if grep -q "WORDPRESS_VERSION=" .env; then
        sed_inplace "s/WORDPRESS_VERSION=.*/WORDPRESS_VERSION=$WP_VERSION/" .env
    else
        echo "WORDPRESS_VERSION=$WP_VERSION" >> .env
    fi
    echo "Updated .env with WORDPRESS_VERSION=$WP_VERSION"
else
    echo "Error: .env file not found. Please copy .env.example to .env first."
    exit 1
fi

# composer.jsonのバージョンを更新
if [ -f composer.json ]; then
    # バージョンがlatestの場合は*に変換
    if [ "$WP_VERSION" = "latest" ]; then
        COMPOSER_VERSION="*"
    else
        COMPOSER_VERSION="$WP_VERSION"
    fi
    
    sed_inplace "s/\"johnpbloch\/wordpress\": \"[^\"]*\"/\"johnpbloch\/wordpress\": \"$COMPOSER_VERSION\"/" composer.json
    echo "Updated composer.json with johnpbloch/wordpress:$COMPOSER_VERSION"
fi

echo ""
echo "WordPress version set to: $WP_VERSION"
echo ""
echo "Next steps:"
echo "1. Run: composer update johnpbloch/wordpress"
echo "2. Run: ./bin/install-wp-tests.sh"
echo "3. Restart Docker containers: docker compose restart"