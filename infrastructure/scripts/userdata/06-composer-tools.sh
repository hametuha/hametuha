#!/bin/bash
# ==============================================================================
# 06-composer-tools.sh - Composer、WP-CLI、開発ツールのセットアップ
# ==============================================================================

set -e

echo "=== Composer & Tools Setup Started ==="

# ec2-userとして実行する処理をまとめる
sudo -u ec2-user bash << 'EC2_USER_SETUP'
# ホームディレクトリ設定
mkdir -p /home/ec2-user/bin
export HOME=/home/ec2-user
export COMPOSER_HOME=/home/ec2-user/.composer

# Composerインストール
echo "Installing Composer..."
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm /tmp/composer-setup.php
    exit 1
fi

php /tmp/composer-setup.php --install-dir=/home/ec2-user/bin --filename=composer
rm -f /tmp/composer-setup.php
chmod +x /home/ec2-user/bin/composer

# WP-CLIインストール
echo "Installing WP-CLI..."
curl -sS https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /home/ec2-user/bin/wp
chmod +x /home/ec2-user/bin/wp

# PATH設定
echo 'export PATH="/home/ec2-user/bin:$PATH"' >> /home/ec2-user/.bashrc
echo 'export COMPOSER_HOME="/home/ec2-user/.composer"' >> /home/ec2-user/.bashrc

# Composer最適化
/home/ec2-user/bin/composer config -g optimize-autoloader true
/home/ec2-user/bin/composer config -g classmap-authoritative true
EC2_USER_SETUP

# cachetoolインストール（OPcache管理ツール）
echo "Installing cachetool..."
php -r "copy('https://gordalina.github.io/cachetool/downloads/cachetool.phar','/usr/local/bin/cachetool');"
chmod +x /usr/local/bin/cachetool

# cachetool設定ファイル作成
cat > /etc/cachetool.yml << 'CACHETOOL_EOF'
adapter: fastcgi
fastcgi:
    host: /var/run/php-fpm/www.sock
    chroot: false
CACHETOOL_EOF

# ePubチェッカー用ディレクトリ作成（必要に応じて）
mkdir -p /usr/local/bin/epubcheck

# 権限設定
chown -R ec2-user:nginx /var/www/hametuha.com
chmod -R 755 /var/www/hametuha.com

# WordPress用ディレクトリ権限
if [ -d "/var/www/hametuha.com/wordpress" ]; then
    find /var/www/hametuha.com/wordpress -type d -exec chmod 755 {} \;
    find /var/www/hametuha.com/wordpress -type f -exec chmod 644 {} \;
    # wp-contentは書き込み可能に
    chmod -R 775 /var/www/hametuha.com/wordpress/wp-content
fi

echo "=== Composer & Tools Setup Completed ==="