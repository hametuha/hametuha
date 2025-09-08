#!/bin/bash
# ==============================================================================
# 08-static-subdomain.sh - 静的ファイル配信用サブドメイン設定
# ==============================================================================
# s.hametuha.com - クッキーフリーの静的ファイル配信専用サブドメイン
# ==============================================================================

set -e

echo "=== Static Subdomain Setup Started ==="

# s.hametuha.com用のNginx設定作成
echo "Creating static subdomain configuration for s.hametuha.com..."
cat > /etc/nginx/conf.d/s.hametuha.conf <<'STATIC_SUBDOMAIN_EOF'
# ==============================================================================
# Static Files Subdomain - s.hametuha.com
# ==============================================================================
# Purpose: Cookie-free static file delivery with maximum performance and security
# - No PHP execution
# - No rewrite rules
# - Strict file type filtering
# - CORS enabled for cross-origin resource sharing
# ==============================================================================

server {
    listen 80;
    server_name s.hametuha.com;
    
    # ドキュメントルート（メインサイトと同じ）
    root /var/www/hametuha.com/wordpress;
    
    # デフォルトファイル無効化（静的ファイルのみ提供）
    index "";
    
    # CORS設定（クロスオリジンリソース共有）
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, HEAD" always;
    add_header Access-Control-Max-Age "31536000" always;
    
    # セキュリティヘッダー
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # パフォーマンスヘッダー
    add_header X-Served-By "s.hametuha.com" always;
    
    # ===========================================================================
    # アクセス制御（セキュリティ最優先）
    # ===========================================================================
    
    # デフォルトで全て拒否
    location / {
        deny all;
    }
    
    # wp-content/uploads内のPHPファイルを明示的に拒否（最優先）
    location ~* ^/wp-content/uploads/.*\.php$ {
        deny all;
        return 403;
    }
    
    # wp-content/uploads内の画像ファイル
    location ~* ^/wp-content/uploads/.*\.(jpg|jpeg|png|gif|ico|webp|avif)$ {
        allow all;
        expires 1y;
        add_header Cache-Control "public, immutable" always;
        etag off;
        access_log off;
        try_files $uri =404;
    }
    
    # wp-content/uploads内のドキュメントファイル
    location ~* ^/wp-content/uploads/.*\.(pdf|zip|doc|docx|xls|xlsx|ppt|pptx)$ {
        allow all;
        expires 30d;
        add_header Cache-Control "public" always;
        add_header Content-Disposition "attachment" always;
        access_log off;
        try_files $uri =404;
    }
    
    # wp-content/uploads内のその他のファイル（動画など）
    location ~ ^/wp-content/uploads/ {
        allow all;
        expires 7d;
        add_header Cache-Control "public" always;
        access_log off;
        try_files $uri =404;
    }
    
    # 静的ファイルのみ許可（uploads以外の領域）
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|otf|svg|eot|webp|avif)$ {
        # アクセス許可
        allow all;
        
        # キャッシュ設定（アグレッシブ）
        expires 1y;
        add_header Cache-Control "public, immutable" always;
        
        # ETag無効化（帯域節約）
        etag off;
        
        # ログ無効化（パフォーマンス向上）
        access_log off;
        
        # Gzip圧縮は有効（CSS/JS/SVG用）
        gzip on;
        gzip_vary on;
        gzip_types text/css application/javascript image/svg+xml;
        
        # バッファ最適化
        output_buffers 1 32k;
        postpone_output 1460;
        
        # 直接ファイル配信（バイパス処理）
        try_files $uri =404;
    }
    
    # ===========================================================================
    # 明示的な拒否ルール
    # ===========================================================================
    
    # PHPファイルへのアクセス完全拒否（場所を問わず）
    location ~ \.php$ {
        deny all;
        return 403;
    }
    
    # 隠しファイル・ディレクトリ拒否
    location ~ /\. {
        deny all;
        return 403;
    }
    
    # WordPress関連ファイル拒否
    location ~ /(wp-config\.php|wp-settings\.php|wp-blog-header\.php) {
        deny all;
        return 403;
    }
    
    # XMLファイル拒否（サイトマップ等）
    location ~ \.(xml|xsl)$ {
        deny all;
        return 403;
    }
    
    # データベースダンプファイル拒否
    location ~ \.(sql|sql\.gz|sql\.zip|sql\.tar\.gz)$ {
        deny all;
        return 403;
    }
    
    # バックアップファイル拒否
    location ~ \.(bak|backup|old|orig|original|~)$ {
        deny all;
        return 403;
    }
    
    # ログファイル拒否
    location ~ \.(log|error_log|access_log)$ {
        deny all;
        return 403;
    }
    
    # ===========================================================================
    # 特殊ケース処理
    # ===========================================================================
    
    # favicon.ico（エラーログ抑制）
    location = /favicon.ico {
        try_files $uri =204;
        log_not_found off;
        access_log off;
    }
    
    # robots.txt（クローラー制御）
    location = /robots.txt {
        # 静的リソースへのクロールは許可（Core Web Vitals対策）
        # Googlebot等がJS/CSSを取得してレンダリング評価できるようにする
        add_header Content-Type "text/plain" always;
        return 200 "User-agent: *\nAllow: *.js\nAllow: *.css\nAllow: *.jpg\nAllow: *.jpeg\nAllow: *.png\nAllow: *.gif\nAllow: *.svg\nAllow: *.woff\nAllow: *.woff2\nDisallow: *.php\nDisallow: /wp-admin/\nDisallow: /wp-includes/\n";
    }
    
    # ヘルスチェック用エンドポイント
    location = /health {
        access_log off;
        add_header Content-Type "text/plain" always;
        return 200 "OK";
    }
}
STATIC_SUBDOMAIN_EOF

# Nginx設定テスト
echo "Testing Nginx configuration..."
nginx -t

# Nginx設定をリロード
echo "Reloading Nginx..."
systemctl reload nginx

echo "=== Static Subdomain Setup Completed ==="
echo "Static file subdomain configured at: s.hametuha.com"
echo "Features enabled:"
echo "  ✅ Cookie-free static file delivery"
echo "  ✅ CORS headers for cross-origin access"
echo "  ✅ PHP execution blocked (including in uploads)"
echo "  ✅ wp-content/uploads accessible for PDF/ZIP distribution"
echo "  ✅ Strict file type filtering"
echo "  ✅ Aggressive caching (1 year for images, 30 days for documents)"
echo "  ✅ Security headers applied"