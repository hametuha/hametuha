# Hametuha Infrastructure as Code

このディレクトリには、Hametuha WordPress環境のインフラストラクチャをコード化したファイルが含まれています。

## 📁 ディレクトリ構造

```
infrastructure/
├── cloudformation/
│   └── hametuha-ec2.yaml      # EC2インスタンス定義
├── parameters/
│   ├── production.json        # 本番環境パラメータ
│   └── staging.json          # ステージング環境パラメータ
├── scripts/
│   └── deploy-infrastructure.sh  # デプロイスクリプト
└── README.md
```

## 🚀 使用方法

### 1. 事前準備

パラメータファイルを環境に合わせて編集：

```bash
# 本番環境の設定
vi infrastructure/parameters/production.json

# 必要な値を設定：
# - KeyPairName: EC2キーペア名
# - VPCId: VPC ID
# - SubnetId: サブネット ID  
# - ExistingSecurityGroupId: セキュリティグループ ID
# - ExistingElasticIPAllocation: Elastic IP Allocation ID
```

### 2. デプロイ

```bash
# スクリプトに実行権限を付与
chmod +x infrastructure/scripts/deploy-infrastructure.sh

# テンプレート検証
./infrastructure/scripts/deploy-infrastructure.sh validate

# 本番環境デプロイ
./infrastructure/scripts/deploy-infrastructure.sh deploy

# ステージング環境デプロイ  
./infrastructure/scripts/deploy-infrastructure.sh -e staging deploy
```

### 3. 管理コマンド

```bash
# スタック状態確認
./infrastructure/scripts/deploy-infrastructure.sh status

# 出力値表示（IPアドレスなど）
./infrastructure/scripts/deploy-infrastructure.sh outputs

# スタック更新
./infrastructure/scripts/deploy-infrastructure.sh update

# スタック削除（要注意！）
./infrastructure/scripts/deploy-infrastructure.sh delete
```

## 🏗️ 作成されるリソース

### EC2インスタンス
- **AMI**: Amazon Linux 2023
- **PHP**: 8.2 (必要な拡張機能込み)
- **Webサーバー**: Nginx
- **その他**: Composer, WP-CLI自動インストール

### セキュリティグループ（新規作成の場合）
- SSH (22) - 全IP許可
- HTTP (80) - 全IP許可  
- HTTPS (443) - 全IP許可

### IAMロール・ポリシー
- CloudWatch監視権限
- SSM管理権限
- S3バックアップ/アップロード権限

## 📊 自動インストールされるソフトウェア

User Dataスクリプトにより以下が自動セットアップされます：

### システムパッケージ
- Git, Vim, htop
- PHP 8.2 + 必要な拡張機能
- Nginx
- CloudWatch Agent

### PHP設定の最適化
- メモリ制限: 512MB
- OPcache有効化
- WordPress向けの設定

### Nginx設定
- WordPress用リライトルール
- PHP-FPM連携
- 静的ファイルキャッシュ
- セキュリティヘッダー

## 🔄 移行手順での使用

### 1. ステージング環境で検証
```bash
# ステージング環境作成
./infrastructure/scripts/deploy-infrastructure.sh -e staging deploy

# データ同期・テスト後
./infrastructure/scripts/deploy-infrastructure.sh -e staging delete
```

### 2. 本番環境移行
```bash
# 新しい本番インスタンス作成
./infrastructure/scripts/deploy-infrastructure.sh deploy

# 出力値でIPアドレス確認
./infrastructure/scripts/deploy-infrastructure.sh outputs

# データ同期後、Elastic IP付け替え
# （CloudFormationテンプレートで自動化済み）
```

## 🛡️ セキュリティ考慮事項

### 含まれているセキュリティ対策
- IAMロールによる最小権限の原則
- セキュリティヘッダーの設定
- 不要なファイルへのアクセス拒否

### 追加推奨事項
- SSH接続元IPの制限
- SSL証明書の設定（Let's Encrypt推奨）
- CloudWatch監視アラームの設定

## 📈 監視・ログ

### CloudWatch連携
- EC2インスタンス標準メトリクス
- カスタムメトリクス（CloudWatch Agent）
- ログ収集設定

### ログファイル場所
- Nginx: `/var/log/nginx/`
- PHP-FPM: `/var/log/php-fpm/`
- User Data実行ログ: `/var/log/user-data.log`

## 🔧 カスタマイズ

### インスタンスタイプ変更
```bash
# parameters/*.jsonを編集
"ParameterValue": "t3.large"  # より大きなインスタンス
```

### ソフトウェア追加
```bash
# cloudformation/hametuha-ec2.yamlのUserDataセクションを編集
# 例: Redisインストール
dnf install -y redis
systemctl enable redis
```

## ⚠️ 注意事項

### コスト管理
- EC2インスタンス料金
- Elastic IP料金（未使用時は$3.65/月）
- EBS料金

### データ保護
- スタック削除時にEBSボリュームも削除される
- 重要データは事前バックアップ必須
- RDSは別管理（このテンプレートには含まれない）

### 制限事項
- SSL証明書は手動設定が必要
- ドメイン設定は別途必要
- メール送信設定（SESなど）は別途設定

## 🆘 トラブルシューティング

### デプロイ失敗時
```bash
# CloudFormationイベント確認
aws cloudformation describe-stack-events --stack-name hametuha-production-server

# User Dataログ確認（インスタンス内で）
sudo tail -f /var/log/user-data.log
```

### パラメータエラー
```bash
# 必須パラメータの確認
aws ec2 describe-vpcs           # VPC ID確認
aws ec2 describe-subnets        # Subnet ID確認
aws ec2 describe-key-pairs      # KeyPair確認
```