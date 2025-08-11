#!/bin/bash

# WordPressテストを実行するスクリプト
set -e

# ローカルテストスイートがない場合はセットアップ
if [ ! -d "wp-tests" ]; then
    echo "Setting up WordPress test suite..."
    ./bin/install-wp-tests.sh
fi

echo "Running PHPUnit tests in Docker..."

# Dockerコンテナ内でPHPUnitを実行
docker compose exec -T wordpress bash -c "
    cd /var/www/html/wp-content/themes/hametuha &&
    vendor/bin/phpunit
"