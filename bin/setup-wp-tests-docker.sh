#!/bin/bash

# Docker環境内でWordPressテストスイートをセットアップするスクリプト
set -e

WP_VERSION=${1-latest}
WP_TESTS_DIR=/tmp/wordpress-tests-lib
WP_CORE_DIR=/var/www/html

echo "Setting up WordPress test suite in Docker..."

# WordPress test suite のタグを決定
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
    # Get latest version
    curl -s http://api.wordpress.org/core/version-check/1.7/ > /tmp/wp-latest.json
    LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
    if [[ -z "$LATEST_VERSION" ]]; then
        echo "Latest WordPress version could not be found"
        exit 1
    fi
    WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

echo "Using WordPress test tag: $WP_TESTS_TAG"

# Docker内でテストスイートをインストール
docker compose exec wordpress bash -c "
    # テストスイートディレクトリを作成
    if [ ! -d $WP_TESTS_DIR ]; then
        mkdir -p $WP_TESTS_DIR
        
        # SVNからテストファイルをダウンロード
        echo 'Downloading test includes...'
        svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes
        
        echo 'Downloading test data...'
        svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data
    fi
    
    # wp-tests-config.php を作成
    if [ ! -f $WP_TESTS_DIR/wp-tests-config.php ]; then
        echo 'Creating wp-tests-config.php...'
        curl -s https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php > $WP_TESTS_DIR/wp-tests-config.php
        
        # 設定を置換
        sed -i \"s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':\" $WP_TESTS_DIR/wp-tests-config.php
        sed -i 's/youremptytestdbnamehere/wordpress_test/' $WP_TESTS_DIR/wp-tests-config.php
        sed -i 's/yourusernamehere/wordpress/' $WP_TESTS_DIR/wp-tests-config.php
        sed -i 's/yourpasswordhere/wordpress/' $WP_TESTS_DIR/wp-tests-config.php
        sed -i 's|localhost|mysql:3306|' $WP_TESTS_DIR/wp-tests-config.php
    fi
    
    echo 'WordPress test suite setup completed!'
"

echo "Creating test database..."
# テスト用データベースを作成
docker compose exec mysql mysql -u root -proot -e "
    DROP DATABASE IF EXISTS wordpress_test;
    CREATE DATABASE wordpress_test;
    GRANT ALL PRIVILEGES ON wordpress_test.* TO 'wordpress'@'%';
    FLUSH PRIVILEGES;
"

echo "Setup completed! You can now run tests with: composer test:docker"