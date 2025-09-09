# Hametuha Infrastructure as Code

このディレクトリには、Hametuha WordPress環境のインフラストラクチャをコード化したファイルが含まれています。

## 📁 ディレクトリ構造

```
infrastructure/
├── cloudformation/
│   ├── hametuha-ec2-modular.yaml     # EC2インスタンス定義（モジュラー構造）
│   └── s3-infrastructure-bucket.yaml # S3バケット定義（初回のみ）
├── parameters/
│   ├── production.json        # 本番環境パラメータ
│   └── staging.json          # ステージング環境パラメータ
├── scripts/
│   ├── userdata/             # UserDataスクリプト（モジュラー構造）
│   │   ├── main.sh           # メインオーケストレータ
│   │   ├── 01-system-setup.sh   # システム基盤設定
│   │   ├── 02-ebs-mount.sh      # EBSボリューム管理
│   │   ├── 03-php-setup.sh      # PHP環境構築
│   │   ├── 04-nginx-setup.sh    # Webサーバー設定
│   │   ├── 05-cloudflare-ssl.sh # CloudFlare SSL設定
│   │   ├── 06-composer-tools.sh # 開発ツール
│   │   ├── 07-monitoring.sh     # 監視設定
│   │   └── 08-static-subdomain.sh # 静的ファイル配信設定
│   ├── deploy-infrastructure.sh      # デプロイスクリプト
│   ├── setup-s3-bucket.sh            # S3バケット初期設定（初回のみ）
│   ├── upload-userdata-scripts.sh    # UserDataスクリプトS3アップロード
│   └── test-userdata-scripts.sh      # テストスイート
└── README.md
```

## 🚀 使用方法

### 1. 初回セットアップ（S3バケット作成）

インフラストラクチャコードを管理するS3バケットを作成（初回のみ実行）：

```bash
# S3バケットの作成
./infrastructure/scripts/setup-s3-bucket.sh

# 以下が作成されます：
# - S3バケット: hametuha-infrastructure-{AWSアカウントID}
# - バージョニング: 有効
# - 暗号化: AES256
# - ライフサイクル: 古いバージョンは90日後に自動削除
```

### 2. UserDataスクリプトのS3アップロード

Git運用から独立してインフラコードをデプロイできます：

```bash
# 開発版としてアップロード（デフォルト）
./infrastructure/scripts/upload-userdata-scripts.sh

# 本番版（latest）としてアップロード
./infrastructure/scripts/upload-userdata-scripts.sh -v latest

# 特定バージョンとしてアップロード
./infrastructure/scripts/upload-userdata-scripts.sh -v v1.0.0

# ドライラン（実際にはアップロードしない）
./infrastructure/scripts/upload-userdata-scripts.sh -d

# ヘルプ表示
./infrastructure/scripts/upload-userdata-scripts.sh -h
```

#### アップロードコマンドの詳細

**バージョン管理戦略：**
- `dev` - 開発・テスト用（デフォルト）
- `latest` - 本番環境用の最新安定版
- `v1.0.0` - 特定バージョンの固定

**ワークフロー例：**
```bash
# 1. 開発版でテスト
./infrastructure/scripts/upload-userdata-scripts.sh -v dev
./infrastructure/scripts/deploy-infrastructure.sh -e staging deploy

# 2. テスト成功後、本番用にプロモート
./infrastructure/scripts/upload-userdata-scripts.sh -v latest
./infrastructure/scripts/deploy-infrastructure.sh deploy

# 3. リリースタグを付ける
./infrastructure/scripts/upload-userdata-scripts.sh -v v1.0.0
```

**メリット：**
- **Gitプッシュ不要**: ローカル変更を即座にテスト可能
- **独立した運用**: インフラとアプリのデプロイサイクルを分離
- **バージョン管理**: 問題時は以前のバージョンにロールバック可能

### 3. 環境パラメータ設定

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
# - ExistingWebContentVolumeId: 既存EBSボリュームID（データ永続化用）
```

### 4. デプロイ

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

### 5. 管理コマンド

```bash
# スタック状態確認
./infrastructure/scripts/deploy-infrastructure.sh status

# 出力値表示（IPアドレスなど）
./infrastructure/scripts/deploy-infrastructure.sh outputs

# スタック更新（EBS自動デタッチ機能付き）
./infrastructure/scripts/deploy-infrastructure.sh update

# スタック削除（要注意！）
./infrastructure/scripts/deploy-infrastructure.sh delete
```

## ✨ 新機能: EBS自動デタッチ

本番環境のスタック更新時、既存のEBSボリューム（WordPress データ）を自動で処理：

1. **`production.json`** から`ExistingWebContentVolumeId`を自動読み取り
2. **旧インスタンスの安全停止** とボリュームデタッチ
3. **新インスタンスへの自動アタッチ** でデータ保持

これにより、**インスタンス更新時もWordPressデータが永続化**され、手動でのEBSデタッチ作業が不要になります。

## 🏗️ 作成されるリソース

### EC2インスタンス
- **AMI**: Amazon Linux 2023
- **PHP**: 8.2 (必要な拡張機能込み)
- **Webサーバー**: Nginx
- **その他**: Composer, WP-CLI, cachetool自動インストール

### EBSボリューム
- **データ永続化**: WordPress wp-content用の専用ボリューム
- **自動スナップショット**: DLM (Data Lifecycle Manager) で日次バックアップ
- **保持期間**: 90日間（3か月）

### セキュリティグループ（新規作成の場合）
- SSH (22) - 全IP許可
- HTTP (80) - 全IP許可  
- HTTPS (443) - 全IP許可

### IAMロール・ポリシー
- CloudWatch監視権限
- SSM管理権限
- S3バックアップ/アップロード権限
- DLM実行権限

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
# scripts/userdata/内の各モジュラースクリプトを編集
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

## 🤖 CI/CD自動デプロイ

### GitHub Actions連携

インフラストラクチャコードは自動的にS3にデプロイされます：

#### 自動トリガー
- **master/mainブランチ**: → `latest`バージョン（本番環境）
- **infra/*ブランチ**: → `dev`バージョン（開発環境）
- **プルリクエスト**: → 検証のみ（デプロイなし）

#### ワークフロー
```yaml
# .github/workflows/infrastructure-deploy.yml
- インフラコード変更検知
- シェルスクリプト検証
- CloudFormationテンプレート検証
- S3へ自動アップロード
- デプロイ完了通知
```

#### 手動実行
```bash
# GitHub CLIでの手動デプロイ
gh workflow run infrastructure-deploy.yml -f version=dev
gh workflow run infrastructure-deploy.yml -f version=latest
```

### 環境設定

詳細は[GitHub Environments設定ガイド](docs/github-environments-setup.md)を参照。

必要な設定：
1. **GitHub Secrets**: AWS認証情報
2. **GitHub Environments**: development/production
3. **Branch Protection**: masterブランチ保護

### デプロイフロー

```
1. ローカル開発
   ↓
2. infraブランチにプッシュ
   ↓
3. 自動的にS3のdevバージョンへアップロード
   ↓
4. ステージング環境でテスト
   ↓
5. masterへマージ
   ↓
6. 自動的にS3のlatestバージョンへアップロード
   ↓
7. 本番環境へ反映
```

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