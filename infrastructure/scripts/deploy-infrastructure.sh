#!/bin/bash
# Hametuha Infrastructure Deployment Script

set -e

# 設定
STACK_NAME="hametuha-production-server"
TEMPLATE_FILE="infrastructure/cloudformation/hametuha-ec2-modular.yaml"
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

# Function to extract parameter value from JSON file
get_parameter_value() {
    local params_file=$1
    local parameter_key=$2
    
    if [[ -f "$params_file" ]]; then
        # Extract parameter value using jq (or python if jq not available)
        if command -v jq &> /dev/null; then
            jq -r --arg key "$parameter_key" '.[] | select(.ParameterKey==$key) | .ParameterValue' "$params_file" 2>/dev/null || echo ""
        else
            # Fallback to python if jq not available
            python3 -c "
import json, sys
try:
    with open('$params_file') as f:
        params = json.load(f)
    for param in params:
        if param.get('ParameterKey') == '$parameter_key':
            print(param.get('ParameterValue', ''))
            break
except:
    pass
" 2>/dev/null || echo ""
        fi
    else
        echo ""
    fi
}

# Function to handle EBS volume detachment for production updates
handle_ebs_detachment() {
    local params_file=$1
    
    # Only process for production environment and when parameters file exists
    if [[ "$ENVIRONMENT" != "production" ]] || [[ ! -f "$params_file" ]]; then
        return 0
    fi
    
    # Extract existing volume ID from parameters
    local volume_id
    volume_id=$(get_parameter_value "$params_file" "ExistingWebContentVolumeId")
    
    if [[ -z "$volume_id" ]] || [[ "$volume_id" == "null" ]] || [[ "$volume_id" == '""' ]] || [[ "$volume_id" == "" ]]; then
        log_info "No existing EBS volume specified, skipping detachment check"
        return 0
    fi
    
    log_info "Checking EBS volume attachment status: $volume_id"
    
    # Check if volume exists and get attachment info
    local attachment_info
    attachment_info=$(aws ec2 describe-volumes --volume-ids "$volume_id" --region "$REGION" --query 'Volumes[0].Attachments[0]' --output json 2>/dev/null) || {
        log_warn "Could not retrieve volume information for $volume_id"
        return 0
    }
    
    # Check if volume is attached
    if [[ "$attachment_info" != "null" ]] && [[ -n "$attachment_info" ]]; then
        local instance_id
        local attachment_state
        
        if command -v jq &> /dev/null; then
            instance_id=$(echo "$attachment_info" | jq -r '.InstanceId // ""')
            attachment_state=$(echo "$attachment_info" | jq -r '.State // ""')
        else
            instance_id=$(echo "$attachment_info" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data.get('InstanceId', ''))" 2>/dev/null || echo "")
            attachment_state=$(echo "$attachment_info" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data.get('State', ''))" 2>/dev/null || echo "")
        fi
        
        if [[ "$attachment_state" == "attached" ]] && [[ -n "$instance_id" ]]; then
            log_info "Volume $volume_id is attached to instance $instance_id. Preparing to detach..."
            
            # Stop the instance first for safety
            log_info "Stopping instance $instance_id to safely detach volume..."
            aws ec2 stop-instances --instance-ids "$instance_id" --region "$REGION" > /dev/null
            
            # Wait for instance to stop
            log_info "Waiting for instance to stop completely..."
            aws ec2 wait instance-stopped --instance-ids "$instance_id" --region "$REGION"
            
            # Detach the volume
            log_info "Detaching volume $volume_id..."
            aws ec2 detach-volume --volume-id "$volume_id" --region "$REGION" > /dev/null
            
            # Wait for volume to become available
            log_info "Waiting for volume to become available..."
            aws ec2 wait volume-available --volume-ids "$volume_id" --region "$REGION"
            
            log_info "✅ Volume $volume_id successfully detached and ready for reuse"
        else
            log_info "Volume $volume_id is not attached or in unexpected state: $attachment_state"
        fi
    else
        log_info "Volume $volume_id is not attached to any instance"
    fi
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
    
    # Handle EBS volume detachment before update (production only)
    handle_ebs_detachment "$PARAMETERS_FILE"
    
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