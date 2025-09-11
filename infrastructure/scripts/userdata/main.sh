#!/bin/bash
# ==============================================================================
# main.sh - Hametuha WordPress環境 UserDataメインスクリプト
# ==============================================================================
# 
# このスクリプトは各機能別スクリプトを順次実行し、完全なWordPress環境を構築します。
# 各スクリプトは独立してテスト・実行でき、メンテナンス性を向上させています。
#
# 実行順序：
# 1. システム基盤のセットアップ
# 2. EBSボリュームのマウント
# 3. PHP環境のセットアップ  
# 4. Nginx Webサーバーのセットアップ
# 5. CloudFlare SSL設定
# 6. Composer & 開発ツール
# 7. 監視・ログ設定
# ==============================================================================

set -Eeuo pipefail  # Enhanced error handling
set -x  # デバッグ出力有効

# エラートラップ設定
on_error() {
    local line_no=$1
    local exit_code=$2
    echo "[ERROR] main.sh failed at line $line_no with exit code $exit_code" >&2
    exit $exit_code
}
trap 'on_error $LINENO $?' ERR

# スクリプトのベースディレクトリを取得
SCRIPT_DIR="$(dirname "${BASH_SOURCE[0]}")"

# ログファイルの設定
LOGFILE="/var/log/userdata.log"
exec > >(tee -a $LOGFILE)
exec 2>&1

echo "========================================================================"
echo "[STEP] Hametuha WordPress環境構築開始: $(date)"
echo "========================================================================"

# 環境変数の表示（デバッグ用）
echo "=== Environment Variables ==="
printenv | grep -E "^(ENVIRONMENT|PHP_VERSION|DB_|WORDPRESS_|ELASTICACHE_)" || true
echo "============================"

# 各機能別スクリプトを順次実行
SCRIPTS=(
    "01-system-setup.sh"
    "02-ebs-mount.sh" 
    "03-php-setup.sh"
    "04-nginx-setup.sh"
    "05-cloudflare-ssl.sh"
    "06-composer-tools.sh"
    "07-monitoring.sh"
    "08-static-subdomain.sh"
)

for script in "${SCRIPTS[@]}"; do
    script_path="${SCRIPT_DIR}/${script}"
    
    echo ""
    echo "========================================================================"
    echo "[STEP] 実行開始: ${script} ($(date))"
    echo "========================================================================"
    
    if [[ -f "$script_path" ]]; then
        # スクリプトを実行可能にする
        chmod +x "$script_path"
        
        # スクリプト実行
        if bash "$script_path"; then
            echo "[OK] ✅ 成功: ${script}"
        else
            echo "[ERROR] ❌ 失敗: ${script}"
            echo "[ERROR] エラーコード: $?"
            echo "[ERROR] 詳細はログを確認してください: ${LOGFILE}"
            exit 1
        fi
    else
        echo "[ERROR] ❌ スクリプトが見つかりません: ${script_path}"
        exit 1
    fi
    
    echo "[OK] 完了: ${script} ($(date))"
done

echo ""
echo "========================================================================"
echo "[OK] 🎉 Hametuha WordPress環境構築完了: $(date)"
echo "========================================================================"
echo "[INFO] - WordPress サイト: https://hametuha.com"
echo "[INFO] - 管理画面: https://hametuha.com/wp-admin"
echo "[INFO] - ログファイル: ${LOGFILE}"
echo "[INFO] - PHP バージョン: $(php -v | head -n1)"
echo "[INFO] - Nginx ステータス: $(systemctl is-active nginx)"
echo "[INFO] - PHP-FPM ステータス: $(systemctl is-active php-fpm)"
echo "========================================================================"

# インスタンス準備完了をCloudFormationに通知
# （cfn-signal コマンドは CloudFormation テンプレート内で設定）
echo "[STEP] インスタンス準備完了 - CloudFormationに通知します"