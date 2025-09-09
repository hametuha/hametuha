#!/bin/bash
# ==============================================================================
# 05-cloudflare-ssl.sh - CloudFlare Flexible SSL設定
# ==============================================================================

set -e

echo "=== CloudFlare SSL Setup Started ==="

# CloudFlare Real IP設定ファイル作成
echo "Creating CloudFlare Real IP configuration..."
cat > /etc/nginx/conf.d/cloudflare-real-ip.conf << 'CLOUDFLARE_EOF'
# CloudFlare IP ranges - updated 2024
# IPv4
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;

# IPv6
set_real_ip_from 2400:cb00::/32;
set_real_ip_from 2606:4700::/32;
set_real_ip_from 2803:f800::/32;
set_real_ip_from 2405:b500::/32;
set_real_ip_from 2405:8100::/32;
set_real_ip_from 2a06:98c0::/29;
set_real_ip_from 2c0f:f248::/32;

# CloudFlareから送信される実際のIPヘッダー
real_ip_header CF-Connecting-IP;
real_ip_recursive on;
CLOUDFLARE_EOF

# WordPress用Nginx設定（CloudFlare Flexible SSL対応）
echo "Creating WordPress Nginx configuration..."
cat > /etc/nginx/conf.d/hametuha.conf << 'NGINX_EOF'
server {
    listen 80;
    server_name hametuha.com www.hametuha.com _;
    root /var/www/hametuha.com/wordpress;
    index index.php index.html index.htm;

    # CloudFlare Real IP設定を読み込み
    include /etc/nginx/conf.d/cloudflare-real-ip.conf;

    # CloudFlare Flexible SSL - HTTP→HTTPSリダイレクト
    # CloudFlareがHTTPSで受けたリクエストをHTTPで転送してくる場合の対応
    if ($http_x_forwarded_proto = "http") {
        return 301 https://$host$request_uri;
    }

    # セキュリティヘッダー
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # WordPress wp-admin trailing slash redirect
    rewrite /wp-admin$ $scheme://$host$uri/ permanent;

    # ログインページの厳格な制限
    location = /wp-login.php {
        limit_req zone=limit_req_login burst=3 delay=2;
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 300;
        fastcgi_param HTTPS $http_x_forwarded_proto;
        fastcgi_param SERVER_PORT $http_x_forwarded_port;
    }

    # WordPress管理画面（REST API考慮）
    location ~ ^/wp-admin {
        limit_req zone=limit_req_admin burst=20 delay=10;
        try_files $uri $uri/ /index.php?$args;
    }

    # WordPress REST API（wp-json）
    location ~ ^/wp-json/ {
        limit_req zone=limit_req_admin burst=25 delay=15;
        try_files $uri $uri/ /index.php?$args;
    }

    # Hamepub（小説配信機能）ディレクトリの保護
    location ~ /hamepub/.* {
        deny all;
    }

    # robots.txt動的生成（WordPress処理）
    location = /robots.txt {
        rewrite ^/robots\.txt$ /index.php?robots=1 last;
        allow all;
        log_not_found off;
        access_log off;
    }

    # WordPress pretty permalinks（セキュリティ強化版）
    location / {
        limit_req zone=limit_req_by_ip burst=15 delay=5;
        
        # blexbot対策（悪質クローラー）
        if ($http_user_agent ~* blexbot) {
            return 503;
        }
        
        try_files $uri $uri/ /index.php?$args;
    }

    # PHP処理（php-fpm.confから最適化設定統合）
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_read_timeout 300;
        
        # パフォーマンス最適化（php-fpm.confから統合）
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        
        # X-Accelヘッダー（内部リダイレクト対応）
        fastcgi_pass_header "X-Accel-Redirect";
        fastcgi_pass_header "X-Accel-Buffering";
        fastcgi_pass_header "X-Accel-Charset";
        fastcgi_pass_header "X-Accel-Expires";
        fastcgi_pass_header "X-Accel-Limit-Rate";
        
        # CloudFlare Flexible SSL対応 - HTTPS認識
        fastcgi_param HTTPS $http_x_forwarded_proto;
        fastcgi_param SERVER_PORT $http_x_forwarded_port;
    }

    # favicon.ico専用設定（404エラーログ抑制）
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    # 静的ファイルのキャッシュ（統合版：拡張子追加、ETag無効化）
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|otf|svg|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
        access_log off;
        etag off;  # CloudFlare経由なので重複を避ける
    }

    # 隠しファイルの保護
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # wp-config.phpの保護
    location ~ wp-config\.php {
        deny all;
    }

    # xmlrpc.phpの制限（Jetpackのみ許可）
    location = /xmlrpc.php {
        # Jetpack以外は拒否
        if ($http_user_agent !~* jetpack) {
            return 403;
        }
        
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 300;
        
        # CloudFlare Flexible SSL対応
        fastcgi_param HTTPS $http_x_forwarded_proto;
        fastcgi_param SERVER_PORT $http_x_forwarded_port;
        
        access_log off;
        log_not_found off;
    }
}

# www.hametuha.comリダイレクト設定
server {
    listen 80;
    server_name www.hametuha.com;
    return 301 https://hametuha.com$request_uri;
}
NGINX_EOF

# CloudFlare用PHP設定
echo "Creating CloudFlare PHP configuration..."
cat > /etc/php.d/98-cloudflare-ssl.ini << 'PHP_SSL_EOF'
; CloudFlare Flexible SSL - HTTPS認識のための設定
; WordPressがHTTPSで動作していることを正しく認識
auto_prepend_file = /var/www/cloudflare-flexible-ssl.php
PHP_SSL_EOF

# CloudFlare用PHP prepend ファイル
cat > /var/www/cloudflare-flexible-ssl.php << 'PHP_PREPEND_EOF'
<?php
// CloudFlare Flexible SSL対応
// HTTPSプロトコルの正しい認識
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
}

// CloudFlareの実際のIPアドレスを取得
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = trim($ips[0]);
}
?>
PHP_PREPEND_EOF

# Nginx設定の再読み込み
nginx -t && systemctl reload nginx

# PHP-FPM再起動（新しい設定を反映）
systemctl restart php-fpm

echo "=== CloudFlare SSL Setup Completed ==="