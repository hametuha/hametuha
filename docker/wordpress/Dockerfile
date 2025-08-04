FROM php:7.4-fpm

# 必要なパッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    vim \
    mariadb-client \
    msmtp \
    subversion \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# WP-CLIのインストール
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Node.jsとnpmのインストール
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# PHP拡張のインストール
RUN docker-php-ext-install opcache mysqli pdo_mysql

# Xdebug (開発環境用、PHP 7.4対応バージョン)
RUN pecl install xdebug-3.1.6 \
    && docker-php-ext-enable xdebug

# msmtp設定ファイルをコピー
COPY msmtprc /etc/msmtprc
RUN chmod 644 /etc/msmtprc

# PHP-FPM設定をコピー
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# 作業ディレクトリ
WORKDIR /var/www/html

# www-dataユーザーでcomposerとnpmを実行できるように
RUN chown -R www-data:www-data /var/www

# ヘルスチェック
HEALTHCHECK --interval=30s --timeout=3s --start-period=30s \
    CMD php-fpm -t || exit 1