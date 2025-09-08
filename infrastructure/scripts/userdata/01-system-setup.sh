#!/bin/bash
# ==============================================================================
# 01-system-setup.sh - システム基本設定
# ==============================================================================

set -e

echo "=== System Setup Started ==="

# タイムゾーン設定
timedatectl set-timezone Asia/Tokyo

# ロケール設定
localectl set-locale LANG=ja_JP.UTF-8

# システムパッケージ更新
echo "Updating system packages..."
dnf update -y

# 基本ツールインストール
echo "Installing basic tools..."
dnf install -y \
    git \
    vim \
    htop \
    curl \
    wget \
    unzip \
    jq \
    tree \
    gcc \
    gcc-c++ \
    make \
    openssl-devel

# スワップ設定（メモリ不足対策）
echo "Setting up swap space..."
if [ ! -f /swapfile ]; then
    dd if=/dev/zero of=/swapfile bs=1G count=4
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    
    # スワップ設定の調整
    echo 'vm.swappiness=10' >> /etc/sysctl.conf
    echo 'vm.vfs_cache_pressure=50' >> /etc/sysctl.conf
    sysctl -p
fi

echo "=== System Setup Completed ==="