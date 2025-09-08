#!/bin/bash
# ==============================================================================
# 04-nginx-setup.sh - Nginx Webサーバーセットアップ
# ==============================================================================

set -e

echo "=== Nginx Setup Started ==="

# Nginxインストール
echo "Installing Nginx..."
dnf install -y nginx

# Nginx基本設定
echo "Configuring Nginx..."
cat > /etc/nginx/nginx.conf << 'NGINX_CONF_EOF'
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /run/nginx.pid;

events {
    worker_connections 2048;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # ログフォーマット
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for" '
                    'rt=$request_time uct="$upstream_connect_time" '
                    'uht="$upstream_header_time" urt="$upstream_response_time"';

    access_log /var/log/nginx/access.log main;

    # パフォーマンス設定
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    client_max_body_size 128M;
    client_body_buffer_size 128k;

    # Gzip圧縮
    gzip on;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_types text/plain text/css text/xml text/javascript 
               application/javascript application/xml+rss 
               application/json application/x-font-ttf
               application/x-font-opentype application/vnd.ms-fontobject
               image/svg+xml image/x-icon;

    # Rate Limiting（ブルートフォース対策）
    # WordPress管理画面のREST API利用を考慮した設定
    limit_req_zone $binary_remote_addr zone=limit_req_by_ip:10m rate=30r/s;
    limit_req_zone $binary_remote_addr zone=limit_req_admin:10m rate=50r/s;
    limit_req_zone $binary_remote_addr zone=limit_req_login:10m rate=5r/m;
    limit_req_log_level warn;  # errorからwarnに変更（ログ量削減）
    limit_req_status 503;

    # Proxy設定（将来のリバースプロキシ対応）
    proxy_read_timeout 30;
    proxy_connect_timeout 30;
    proxy_send_timeout 300;

    # バーチャルホスト設定を読み込み
    include /etc/nginx/conf.d/*.conf;
}
NGINX_CONF_EOF

# SSL用ディレクトリ作成（将来のSSL対応用）
mkdir -p /etc/nginx/ssl

# ログディレクトリ権限設定
mkdir -p /var/log/nginx
chown -R nginx:nginx /var/log/nginx

# Nginx起動
systemctl enable nginx
systemctl start nginx

echo "=== Nginx Setup Completed ==="