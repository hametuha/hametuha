#!/usr/bin/env bash

# WordPressテストスイートをローカルにインストールするスクリプト
set -e

# .envファイルからバージョンを読み取る
if [ -f .env ] && grep -q "WORDPRESS_VERSION=" .env; then
    WP_VERSION=$(grep "WORDPRESS_VERSION=" .env | cut -d '=' -f2)
    echo "Using WordPress version from .env: $WP_VERSION"
else
    # 引数またはデフォルト値を使用
    if [ $# -lt 1 ]; then
        echo "usage: $0 [wp-version] [skip-database-creation]"
        echo "example: $0 6.1"
        echo "example: $0 latest"
        echo ""
        echo "Note: WordPress version can also be set in .env file with WORDPRESS_VERSION="
        exit 1
    fi
    WP_VERSION=${1-latest}
fi
SKIP_DB_CREATE=${2-false}
WP_TESTS_DIR="$(pwd)/wp-tests"

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

# WordPressのバージョンタグを決定
if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
    WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
    if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
        WP_TESTS_TAG="tags/${WP_VERSION%??}"
    else
        WP_TESTS_TAG="tags/$WP_VERSION"
    fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
    WP_TESTS_TAG="trunk"
else
    download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
    LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
    if [[ -z "$LATEST_VERSION" ]]; then
        echo "Latest WordPress version could not be found"
        exit 1
    fi
    WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

echo "Installing WordPress test suite (version: $WP_VERSION, tag: $WP_TESTS_TAG)..."

# テストスイートのインストール
if [ ! -d "$WP_TESTS_DIR" ]; then
    mkdir -p "$WP_TESTS_DIR"
    echo "Downloading test includes..."
    svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ "$WP_TESTS_DIR/includes"
    echo "Downloading test data..."
    svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ "$WP_TESTS_DIR/data"
fi

# wp-tests-config.phpの作成
if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
    echo "Creating wp-tests-config.php..."
    download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
    
    # 設定の置換
    WP_CORE_DIR="$(pwd)/wp"
    sed -i.bak "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i.bak "s/youremptytestdbnamehere/wordpress_test/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i.bak "s/yourusernamehere/wordpress/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i.bak "s/yourpasswordhere/wordpress/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i.bak "s|localhost|mysql:3306|" "$WP_TESTS_DIR/wp-tests-config.php"
    
    # バックアップファイルを削除
    rm -f "$WP_TESTS_DIR/wp-tests-config.php.bak"
fi

# テスト用データベースの作成
if [ ${SKIP_DB_CREATE} = "false" ]; then
    echo "Creating test database..."
    if command -v docker &> /dev/null && docker compose ps mysql | grep -q "Up"; then
        echo "Using Docker MySQL..."
        docker compose exec mysql mysql -u root -proot -e "
            DROP DATABASE IF EXISTS wordpress_test;
            CREATE DATABASE wordpress_test;
            GRANT ALL PRIVILEGES ON wordpress_test.* TO 'wordpress'@'%';
            FLUSH PRIVILEGES;
        "
    else
        echo "Warning: Could not create database automatically."
        echo "Please create 'wordpress_test' database manually."
    fi
fi

echo "WordPress test suite installed successfully!"
echo "Test directory: $WP_TESTS_DIR"