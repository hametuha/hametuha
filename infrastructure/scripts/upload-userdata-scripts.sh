#!/bin/bash
# =============================================================================
# Upload UserData Scripts to S3
# =============================================================================
# このスクリプトはUserDataスクリプトをS3バケットにアップロードします
# Git運用から独立してインフラコードをデプロイできます
# =============================================================================

set -e

# カラー出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# デフォルト値
VERSION="dev"
DRY_RUN=false

# 使用方法
usage() {
    echo "Usage: $0 [OPTIONS]"
    echo
    echo "Options:"
    echo "  -v, --version VERSION   Version to upload (default: dev)"
    echo "                         Examples: dev, latest, v1.0.0"
    echo "  -d, --dry-run          Dry run mode (show what would be uploaded)"
    echo "  -h, --help             Show this help message"
    echo
    echo "Examples:"
    echo "  $0                     # Upload to dev/"
    echo "  $0 -v latest           # Upload to latest/"
    echo "  $0 -v v1.0.0           # Upload to v1.0.0/"
    echo "  $0 -d                  # Dry run"
}

# 引数解析
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--version)
            VERSION="$2"
            shift 2
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            usage
            exit 1
            ;;
    esac
done

echo -e "${GREEN}=== Upload UserData Scripts to S3 ===${NC}"

# S3バケット名を取得
STACK_NAME="hametuha-infrastructure-s3-bucket"
REGION=${AWS_DEFAULT_REGION:-ap-northeast-1}

BUCKET_NAME=$(aws cloudformation describe-stacks \
    --stack-name "$STACK_NAME" \
    --query 'Stacks[0].Outputs[?OutputKey==`BucketName`].OutputValue' \
    --output text \
    --region "$REGION" 2>/dev/null)

if [ -z "$BUCKET_NAME" ]; then
    echo -e "${RED}Error: S3 bucket not found. Please run setup-s3-bucket.sh first.${NC}"
    exit 1
fi

# スクリプトディレクトリ
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
USERDATA_DIR="${SCRIPT_DIR}/userdata"

# UserDataディレクトリの確認
if [ ! -d "$USERDATA_DIR" ]; then
    echo -e "${RED}Error: UserData directory not found at ${USERDATA_DIR}${NC}"
    exit 1
fi

# アップロード先
S3_PATH="s3://${BUCKET_NAME}/userdata/${VERSION}"

echo -e "${BLUE}Configuration:${NC}"
echo "  Source: ${USERDATA_DIR}"
echo "  Destination: ${S3_PATH}"
echo "  Version: ${VERSION}"
echo "  Dry Run: ${DRY_RUN}"
echo

# ファイル一覧表示
echo -e "${YELLOW}Files to upload:${NC}"
for file in "$USERDATA_DIR"/*.sh; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        size=$(du -h "$file" | cut -f1)
        echo "  - ${filename} (${size})"
    fi
done
echo

# 確認（Dry Runでない場合）
if [ "$DRY_RUN" = false ]; then
    if [ "$VERSION" = "latest" ]; then
        echo -e "${YELLOW}WARNING: You are about to upload to 'latest' version.${NC}"
        echo -e "${YELLOW}This will affect production deployments!${NC}"
    fi
    
    read -p "Do you want to proceed? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Upload cancelled"
        exit 0
    fi
fi

# アップロード実行
if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}DRY RUN MODE - No files will be uploaded${NC}"
    echo
    echo "Would execute:"
    echo "  aws s3 sync ${USERDATA_DIR}/ ${S3_PATH}/ \\"
    echo "    --exclude '*' --include '*.sh' \\"
    echo "    --metadata-directive REPLACE \\"
    echo "    --cache-control 'max-age=300' \\"
    echo "    --content-type 'text/plain'"
else
    echo -e "${GREEN}Uploading scripts...${NC}"
    
    # S3にアップロード
    aws s3 sync "${USERDATA_DIR}/" "${S3_PATH}/" \
        --exclude '*' \
        --include '*.sh' \
        --metadata-directive REPLACE \
        --cache-control 'max-age=300' \
        --content-type 'text/plain' \
        --region "$REGION"
    
    # パブリックアクセス用のタグを設定（UserDataスクリプト用）
    echo -e "${GREEN}Setting public access tags...${NC}"
    for file in "$USERDATA_DIR"/*.sh; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            aws s3api put-object-tagging \
                --bucket "$BUCKET_NAME" \
                --key "userdata/${VERSION}/${filename}" \
                --tagging 'TagSet=[{Key=Public,Value=true}]' \
                --region "$REGION"
        fi
    done
    
    echo
    echo -e "${GREEN}=== Upload Complete ===${NC}"
    echo
    echo -e "${GREEN}Scripts uploaded to:${NC}"
    echo "  ${S3_PATH}"
    echo
    echo -e "${GREEN}Base URL for UserData:${NC}"
    echo "  https://${BUCKET_NAME}.s3.${REGION}.amazonaws.com/userdata/${VERSION}"
    
    # latestへのコピー提案
    if [ "$VERSION" != "latest" ] && [ "$VERSION" != "dev" ]; then
        echo
        echo -e "${YELLOW}To promote this version to latest, run:${NC}"
        echo "  aws s3 sync ${S3_PATH}/ s3://${BUCKET_NAME}/userdata/latest/ --delete"
    fi
fi

# バージョン一覧表示
echo
echo -e "${BLUE}Current versions in S3:${NC}"
aws s3 ls s3://${BUCKET_NAME}/userdata/ --region "$REGION" | grep PRE | awk '{print "  - " $2}'