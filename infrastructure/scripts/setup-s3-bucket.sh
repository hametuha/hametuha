#!/bin/bash
# =============================================================================
# S3 Infrastructure Bucket Setup Script (One-time Setup)
# =============================================================================
# このスクリプトは一度だけ実行します
# インフラストラクチャスクリプトを管理するS3バケットを作成します
# =============================================================================

set -e

# カラー出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Hametuha Infrastructure S3 Bucket Setup ===${NC}"

# AWS CLIの確認
if ! command -v aws &> /dev/null; then
    echo -e "${RED}Error: AWS CLI is not installed${NC}"
    exit 1
fi

# AWSアカウントID取得
ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text)
if [ -z "$ACCOUNT_ID" ]; then
    echo -e "${RED}Error: Failed to get AWS Account ID${NC}"
    exit 1
fi

echo -e "${YELLOW}AWS Account ID: ${ACCOUNT_ID}${NC}"

# リージョン設定
REGION=${AWS_DEFAULT_REGION:-ap-northeast-1}
echo -e "${YELLOW}Region: ${REGION}${NC}"

# スタック名
STACK_NAME="hametuha-infrastructure-s3-bucket"
TEMPLATE_PATH="$(dirname "$0")/../cloudformation/s3-infrastructure-bucket.yaml"

# テンプレートファイルの確認
if [ ! -f "$TEMPLATE_PATH" ]; then
    echo -e "${RED}Error: CloudFormation template not found at ${TEMPLATE_PATH}${NC}"
    exit 1
fi

# 既存スタックの確認
echo "Checking if stack already exists..."
if aws cloudformation describe-stacks --stack-name "$STACK_NAME" --region "$REGION" &> /dev/null; then
    echo -e "${YELLOW}Stack ${STACK_NAME} already exists${NC}"
    read -p "Do you want to update the existing stack? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Exiting without changes"
        exit 0
    fi
    OPERATION="update-stack"
    WAIT_OPERATION="stack-update-complete"
else
    echo "Stack does not exist. Creating new stack..."
    OPERATION="create-stack"
    WAIT_OPERATION="stack-create-complete"
fi

# CloudFormationスタックのデプロイ
echo -e "${GREEN}Deploying S3 bucket stack...${NC}"
aws cloudformation $OPERATION \
    --stack-name "$STACK_NAME" \
    --template-body file://"$TEMPLATE_PATH" \
    --capabilities CAPABILITY_IAM \
    --region "$REGION" \
    --parameters \
        ParameterKey=BucketNamePrefix,ParameterValue=hametuha-infrastructure

# スタック作成/更新の完了を待つ
echo "Waiting for stack operation to complete..."
aws cloudformation wait $WAIT_OPERATION \
    --stack-name "$STACK_NAME" \
    --region "$REGION"

# 出力値を取得
echo -e "${GREEN}Getting bucket information...${NC}"
BUCKET_NAME=$(aws cloudformation describe-stacks \
    --stack-name "$STACK_NAME" \
    --query 'Stacks[0].Outputs[?OutputKey==`BucketName`].OutputValue' \
    --output text \
    --region "$REGION")

BUCKET_URL=$(aws cloudformation describe-stacks \
    --stack-name "$STACK_NAME" \
    --query 'Stacks[0].Outputs[?OutputKey==`BucketUrl`].OutputValue' \
    --output text \
    --region "$REGION")

SCRIPTS_BASE_URL=$(aws cloudformation describe-stacks \
    --stack-name "$STACK_NAME" \
    --query 'Stacks[0].Outputs[?OutputKey==`ScriptsBaseUrl`].OutputValue' \
    --output text \
    --region "$REGION")

# ディレクトリ構造を作成
echo -e "${GREEN}Creating initial directory structure in S3...${NC}"

# 初期ディレクトリ構造を示すREADMEファイルを作成
cat > /tmp/README.md << 'EOF'
# Hametuha Infrastructure Scripts

This S3 bucket contains infrastructure scripts for Hametuha WordPress deployment.

## Directory Structure

```
/
├── userdata/           # EC2 UserData scripts
│   ├── latest/        # Latest stable version
│   ├── v1.0.0/       # Version 1.0.0
│   └── dev/          # Development version (for testing)
├── cloudformation/    # CloudFormation templates (future use)
└── docs/             # Documentation
```

## Usage

Scripts are downloaded during EC2 instance initialization from:
- Production: `s3://BUCKET_NAME/userdata/latest/`
- Testing: `s3://BUCKET_NAME/userdata/dev/`
- Specific version: `s3://BUCKET_NAME/userdata/v1.0.0/`
EOF

# READMEをアップロード
aws s3 cp /tmp/README.md s3://${BUCKET_NAME}/README.md --region "$REGION"

# 初期ディレクトリマーカーを作成
echo "Creating directory markers..."
echo "" | aws s3 cp - s3://${BUCKET_NAME}/userdata/.keep --region "$REGION"
echo "" | aws s3 cp - s3://${BUCKET_NAME}/userdata/latest/.keep --region "$REGION"
echo "" | aws s3 cp - s3://${BUCKET_NAME}/userdata/dev/.keep --region "$REGION"
echo "" | aws s3 cp - s3://${BUCKET_NAME}/cloudformation/.keep --region "$REGION"
echo "" | aws s3 cp - s3://${BUCKET_NAME}/docs/.keep --region "$REGION"

# クリーンアップ
rm /tmp/README.md

# 結果表示
echo -e "${GREEN}=== S3 Bucket Setup Complete ===${NC}"
echo
echo -e "${GREEN}Bucket Information:${NC}"
echo "  Bucket Name: ${BUCKET_NAME}"
echo "  Bucket URL: ${BUCKET_URL}"
echo "  Scripts Base URL: ${SCRIPTS_BASE_URL}"
echo
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Upload UserData scripts to S3:"
echo "   ./infrastructure/scripts/upload-userdata-scripts.sh"
echo
echo "2. Update CloudFormation template to use S3:"
echo "   Update SCRIPTS_BASE_URL in hametuha-ec2-modular.yaml"
echo
echo "3. Deploy infrastructure:"
echo "   ./infrastructure/scripts/deploy-infrastructure.sh update production"
echo
echo -e "${GREEN}This bucket will be used for all future infrastructure deployments.${NC}"