#!/bin/bash
# Hametuha Infrastructure Deployment Script

set -e

# 設定
STACK_NAME="hametuha-production-server"
TEMPLATE_FILE="infrastructure/cloudformation/hametuha-ec2.yaml"
PARAMETERS_FILE="infrastructure/parameters/production.json"

# カラー出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ヘルプ表示
show_help() {
    cat << EOF
Hametuha Infrastructure Deployment

USAGE:
    $0 [OPTIONS] COMMAND

COMMANDS:
    validate    テンプレートの検証
    deploy      スタックのデプロイ
    update      スタックの更新
    delete      スタックの削除
    status      スタックの状態確認
    outputs     スタックの出力値表示

OPTIONS:
    -e ENV      環境名 (staging|production) [default: production]
    -r REGION   AWSリージョン [default: ap-northeast-1]
    -h          このヘルプを表示

EXAMPLES:
    # テンプレート検証
    $0 validate

    # 本番環境デプロイ
    $0 deploy

    # ステージング環境デプロイ
    $0 -e staging deploy

    # スタック状態確認
    $0 status
EOF
}

# デフォルト値
ENVIRONMENT="production"
REGION="ap-northeast-1"

# オプション解析
while getopts "e:r:h" opt; do
    case $opt in
        e)
            ENVIRONMENT="$OPTARG"
            ;;
        r)
            REGION="$OPTARG"
            ;;
        h)
            show_help
            exit 0
            ;;
        *)
            echo -e "${RED}Invalid option${NC}" >&2
            show_help
            exit 1
            ;;
    esac
done

shift $((OPTIND-1))

# コマンド取得
COMMAND="${1:-help}"

# スタック名を環境に応じて変更
STACK_NAME="hametuha-${ENVIRONMENT}-server"
PARAMETERS_FILE="infrastructure/parameters/${ENVIRONMENT}.json"

# パラメータファイル存在チェック
if [[ ! -f "$PARAMETERS_FILE" ]]; then
    echo -e "${YELLOW}Warning: Parameters file not found: $PARAMETERS_FILE${NC}"
    echo "Using template default values"
    PARAMETERS_ARG=""
else
    PARAMETERS_ARG="--parameters file://$PARAMETERS_FILE"
fi

# AWS CLI存在チェック
if ! command -v aws &> /dev/null; then
    echo -e "${RED}Error: AWS CLI not found${NC}"
    exit 1
fi

# リージョン設定
export AWS_DEFAULT_REGION="$REGION"

# ログ関数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# テンプレート検証
validate_template() {
    log_info "Validating CloudFormation template..."
    
    if aws cloudformation validate-template \
        --template-body "file://$TEMPLATE_FILE" > /dev/null; then
        log_info "✅ Template validation successful"
    else
        log_error "❌ Template validation failed"
        exit 1
    fi
}

# スタックデプロイ
deploy_stack() {
    log_info "Deploying stack: $STACK_NAME"
    
    # スタックの存在確認
    if aws cloudformation describe-stacks \
        --stack-name "$STACK_NAME" &> /dev/null; then
        log_warn "Stack already exists. Use 'update' command instead."
        exit 1
    fi
    
    # デプロイ実行
    aws cloudformation create-stack \
        --stack-name "$STACK_NAME" \
        --template-body "file://$TEMPLATE_FILE" \
        $PARAMETERS_ARG \
        --capabilities CAPABILITY_NAMED_IAM \
        --tags \
            Key=Environment,Value="$ENVIRONMENT" \
            Key=Application,Value=hametuha \
            Key=ManagedBy,Value=CloudFormation
    
    log_info "Stack creation initiated. Waiting for completion..."
    
    # 作成完了を待機
    if aws cloudformation wait stack-create-complete \
        --stack-name "$STACK_NAME"; then
        log_info "✅ Stack deployed successfully"
        show_outputs
    else
        log_error "❌ Stack deployment failed"
        exit 1
    fi
}

# スタック更新
update_stack() {
    log_info "Updating stack: $STACK_NAME"
    
    # 更新実行
    if aws cloudformation update-stack \
        --stack-name "$STACK_NAME" \
        --template-body "file://$TEMPLATE_FILE" \
        $PARAMETERS_ARG \
        --capabilities CAPABILITY_NAMED_IAM; then
        
        log_info "Stack update initiated. Waiting for completion..."
        
        # 更新完了を待機
        if aws cloudformation wait stack-update-complete \
            --stack-name "$STACK_NAME"; then
            log_info "✅ Stack updated successfully"
            show_outputs
        else
            log_error "❌ Stack update failed"
            exit 1
        fi
    else
        log_warn "No updates to be performed"
    fi
}

# スタック削除
delete_stack() {
    log_warn "This will DELETE the stack: $STACK_NAME"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_info "Operation cancelled"
        exit 0
    fi
    
    log_info "Deleting stack: $STACK_NAME"
    
    aws cloudformation delete-stack \
        --stack-name "$STACK_NAME"
    
    log_info "Stack deletion initiated. Waiting for completion..."
    
    if aws cloudformation wait stack-delete-complete \
        --stack-name "$STACK_NAME"; then
        log_info "✅ Stack deleted successfully"
    else
        log_error "❌ Stack deletion failed"
        exit 1
    fi
}

# スタック状態確認
show_status() {
    log_info "Checking stack status: $STACK_NAME"
    
    if ! aws cloudformation describe-stacks \
        --stack-name "$STACK_NAME" \
        --query 'Stacks[0].[StackName,StackStatus,CreationTime,LastUpdatedTime]' \
        --output table; then
        log_error "Stack not found: $STACK_NAME"
        exit 1
    fi
}

# 出力値表示
show_outputs() {
    log_info "Stack outputs:"
    
    aws cloudformation describe-stacks \
        --stack-name "$STACK_NAME" \
        --query 'Stacks[0].Outputs' \
        --output table || log_warn "No outputs available"
}

# コマンド実行
case $COMMAND in
    validate)
        validate_template
        ;;
    deploy)
        validate_template
        deploy_stack
        ;;
    update)
        validate_template
        update_stack
        ;;
    delete)
        delete_stack
        ;;
    status)
        show_status
        ;;
    outputs)
        show_outputs
        ;;
    help|*)
        show_help
        ;;
esac