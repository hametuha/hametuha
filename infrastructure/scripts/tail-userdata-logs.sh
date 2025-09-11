#!/bin/bash
# ==============================================================================
# tail-userdata-logs.sh - S3のUserDataログをリアルタイムでtail
# ==============================================================================

set -e

# デフォルト値
ENVIRONMENT="staging"
INTERVAL=10
LINES=50

# 使用方法を表示
show_usage() {
    cat << EOF
Usage: $0 [OPTIONS]

OPTIONS:
    -e, --environment ENV    Environment (staging|production) [default: staging]
    -i, --interval SEC       Polling interval in seconds [default: 10]
    -n, --lines NUM          Number of lines to show [default: 50]
    -h, --help              Show this help message

EXAMPLES:
    $0                      # tail staging logs
    $0 -e production        # tail production logs
    $0 -i 5 -n 100         # poll every 5 seconds, show 100 lines
EOF
}

# 引数解析
while [[ $# -gt 0 ]]; do
    case $1 in
        -e|--environment)
            ENVIRONMENT="$2"
            shift 2
            ;;
        -i|--interval)
            INTERVAL="$2"
            shift 2
            ;;
        -n|--lines)
            LINES="$2"
            shift 2
            ;;
        -h|--help)
            show_usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            show_usage
            exit 1
            ;;
    esac
done

# AWS Account IDを取得
ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text)
S3_BUCKET="hametuha-infrastructure-$ACCOUNT_ID"
LOG_PREFIX="debug-logs/$ENVIRONMENT/"

echo "🔍 Searching for UserData logs in $ENVIRONMENT environment..."
echo "S3 Bucket: $S3_BUCKET"
echo "Polling interval: ${INTERVAL}s"
echo ""

# 最新のログファイルを見つける関数
find_latest_log() {
    aws s3 ls "s3://$S3_BUCKET/$LOG_PREFIX" --recursive | \
    grep "userdata.log" | \
    sort -k1,2 -r | \
    head -1 | \
    awk '{print $4}'
}

# 前回読んだ行数を記録
LAST_LINE_COUNT=0
TEMP_FILE="/tmp/userdata-tail-$$"

# クリーンアップ関数
cleanup() {
    rm -f "$TEMP_FILE"
    exit 0
}
trap cleanup INT TERM

echo "⏳ Looking for log files..."

while true; do
    LATEST_LOG=$(find_latest_log)
    
    if [[ -z "$LATEST_LOG" ]]; then
        echo "📝 No logs found yet. Waiting..."
        sleep $INTERVAL
        continue
    fi
    
    # ログファイルからインスタンスIDを抽出
    INSTANCE_ID=$(echo "$LATEST_LOG" | sed 's|.*debug-logs/[^/]*/\([^/]*\)/.*|\1|')
    
    if [[ "$LAST_LOG" != "$LATEST_LOG" ]]; then
        echo ""
        echo "📋 Found log: s3://$S3_BUCKET/$LATEST_LOG"
        echo "🖥️  Instance ID: $INSTANCE_ID"
        echo "📅 $(date): Starting to tail logs..."
        echo "----------------------------------------"
        LAST_LINE_COUNT=0
        LAST_LOG="$LATEST_LOG"
    fi
    
    # S3からログをダウンロード
    if aws s3 cp "s3://$S3_BUCKET/$LATEST_LOG" "$TEMP_FILE" --quiet; then
        CURRENT_LINE_COUNT=$(wc -l < "$TEMP_FILE")
        
        # 新しい行がある場合のみ表示
        if [[ $CURRENT_LINE_COUNT -gt $LAST_LINE_COUNT ]]; then
            if [[ $LAST_LINE_COUNT -gt 0 ]]; then
                # 新しい行のみ表示
                tail -n +$((LAST_LINE_COUNT + 1)) "$TEMP_FILE"
            else
                # 初回は指定した行数を表示
                tail -n $LINES "$TEMP_FILE"
            fi
            LAST_LINE_COUNT=$CURRENT_LINE_COUNT
        fi
    else
        echo "⚠️  Failed to download log file"
    fi
    
    sleep $INTERVAL
done