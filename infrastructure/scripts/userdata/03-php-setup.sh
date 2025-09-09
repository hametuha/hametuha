#!/bin/bash
# ==============================================================================
# 03-php-setup.sh - PHP環境セットアップ
# ==============================================================================

set -e

echo "=== PHP Setup Started ==="

# PHPバージョン（環境変数から取得、デフォルト8.2）
PHP_VERSION="${PHP_VERSION:-8.2}"

# PHP本体と拡張モジュールインストール
echo "Installing PHP ${PHP_VERSION}..."
dnf install -y \
    php${PHP_VERSION} \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysqlnd \
    php${PHP_VERSION}-pdo \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-devel \
    php-pear

# ImageMagick関連
echo "Installing ImageMagick..."
dnf install -y \
    ImageMagick \
    ImageMagick-devel \
    ghostscript \
    ghostscript-devel

# PECL拡張モジュールのインストール
echo "Installing PECL extensions..."
pecl channel-update pecl.php.net

# imagickインストール
printf "\n" | pecl install imagick || true
echo "extension=imagick.so" > /etc/php.d/40-imagick.ini

# igbinaryインストール（シリアライズ高速化）
printf "\n" | pecl install igbinary || true
echo "extension=igbinary.so" > /etc/php.d/40-igbinary.ini

# memcachedインストール（セッション管理用）
dnf install -y libmemcached-devel
printf "yes\nno\n" | pecl install memcached || true
cat > /etc/php.d/50-memcached.ini << 'EOF'
extension=memcached.so
session.save_handler=memcached
EOF

# timezonedbインストール（タイムゾーン最新データ）
printf "\n" | pecl install timezonedb || true
echo "extension=timezonedb.so" > /etc/php.d/40-timezonedb.ini

# PHP基本設定
echo "Configuring PHP..."
cat > /etc/php.d/99-hametuha.ini << 'EOF'
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 128M
upload_max_filesize = 128M
max_file_uploads = 20
date.timezone = Asia/Tokyo

; OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
opcache.save_comments=1

; セキュリティ
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
EOF

# PHP-FPM設定
echo "Configuring PHP-FPM..."
cat > /etc/php-fpm.d/www.conf << 'EOF'
[www]
user = ec2-user
group = apache
listen = /var/run/php-fpm/www.sock
listen.acl_users = apache,nginx
listen.allowed_clients = 127.0.0.1

pm = dynamic
pm.max_children = 12
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

slowlog = /var/log/php-fpm/www-slow.log
request_slowlog_timeout = 5s
catch_workers_output = yes

php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
EOF

# ElastiCache設定（環境変数から）
if [ -n "$ELASTICACHE_ENDPOINT" ]; then
    echo "php_value[session.save_path] = ${ELASTICACHE_ENDPOINT}" >> /etc/php-fpm.d/www.conf
fi

# ログディレクトリ作成
mkdir -p /var/log/php-fpm
chown -R ec2-user:apache /var/log/php-fpm

# PHP-FPM起動
systemctl enable php-fpm
systemctl start php-fpm

echo "=== PHP Setup Completed ==="