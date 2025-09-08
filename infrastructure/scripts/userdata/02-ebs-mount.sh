#!/bin/bash
# ==============================================================================
# 02-ebs-mount.sh - EBSボリュームマウント設定
# ==============================================================================

set -e

echo "=== EBS Volume Mount Setup Started ==="

# 環境変数
WEB_MOUNT_POINT="/var/www/hametuha.com"
WEB_ROOT="/var/www/hametuha.com/wordpress"

# EBSボリュームのアタッチ待機
echo "Waiting for EBS volume to attach..."
DEVICE=/dev/xvdf
MAX_ATTEMPTS=60
ATTEMPT=0

while [ ! -b $DEVICE ]; do
    if [ $ATTEMPT -ge $MAX_ATTEMPTS ]; then
        echo "ERROR: EBS volume not attached after $MAX_ATTEMPTS attempts"
        exit 1
    fi
    echo "Waiting for EBS volume to attach... (attempt $((ATTEMPT+1))/$MAX_ATTEMPTS)"
    sleep 5
    ATTEMPT=$((ATTEMPT+1))
done

echo "EBS volume detected at $DEVICE"

# ファイルシステムの作成（初回のみ）
if ! blkid $DEVICE; then
    echo "Creating filesystem on $DEVICE..."
    mkfs.ext4 $DEVICE
fi

# マウントポイント作成
mkdir -p $WEB_MOUNT_POINT

# マウント
echo "Mounting $DEVICE to $WEB_MOUNT_POINT..."
mount $DEVICE $WEB_MOUNT_POINT

# /etc/fstabに追加（自動マウント設定）
UUID=$(blkid -s UUID -o value $DEVICE)
if ! grep -q "$UUID" /etc/fstab; then
    echo "UUID=$UUID $WEB_MOUNT_POINT ext4 defaults,nofail 0 2" >> /etc/fstab
    echo "Added to /etc/fstab for auto-mount"
fi

# WordPressディレクトリ作成
mkdir -p $WEB_ROOT
mkdir -p $WEB_MOUNT_POINT/uploads
mkdir -p $WEB_MOUNT_POINT/plugins
mkdir -p $WEB_MOUNT_POINT/themes

echo "=== EBS Volume Mount Setup Completed ==="