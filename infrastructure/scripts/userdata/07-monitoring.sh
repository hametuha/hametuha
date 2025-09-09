#!/bin/bash
# ==============================================================================
# 07-monitoring.sh - CloudWatch Agent、ログ設定
# ==============================================================================

set -e

echo "=== Monitoring Setup Started ==="

# CloudWatch Agentインストール
echo "Installing CloudWatch Agent..."
dnf install -y amazon-cloudwatch-agent

# CloudWatch Agent設定
echo "Configuring CloudWatch Agent..."
cat > /opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json << 'CLOUDWATCH_EOF'
{
    "agent": {
        "run_as_user": "cwagent"
    },
    "logs": {
        "logs_collected": {
            "files": {
                "collect_list": [
                    {
                        "file_path": "/var/log/nginx/access.log",
                        "log_group_name": "/aws/ec2/hametuha/nginx/access",
                        "log_stream_name": "{instance_id}",
                        "retention_in_days": 30
                    },
                    {
                        "file_path": "/var/log/nginx/error.log",
                        "log_group_name": "/aws/ec2/hametuha/nginx/error",
                        "log_stream_name": "{instance_id}",
                        "retention_in_days": 30
                    },
                    {
                        "file_path": "/var/log/php-fpm/www-error.log",
                        "log_group_name": "/aws/ec2/hametuha/php-fpm/error",
                        "log_stream_name": "{instance_id}",
                        "retention_in_days": 30
                    },
                    {
                        "file_path": "/var/log/php-fpm/www-slow.log",
                        "log_group_name": "/aws/ec2/hametuha/php-fpm/slow",
                        "log_stream_name": "{instance_id}",
                        "retention_in_days": 30
                    }
                ]
            }
        }
    },
    "metrics": {
        "namespace": "Hametuha/EC2",
        "metrics_collected": {
            "cpu": {
                "measurement": [
                    {
                        "name": "cpu_usage_idle",
                        "rename": "CPU_IDLE",
                        "unit": "Percent"
                    }
                ],
                "totalcpu": false
            },
            "disk": {
                "measurement": [
                    {
                        "name": "disk_used_percent",
                        "rename": "DISK_USED",
                        "unit": "Percent"
                    }
                ],
                "resources": [
                    "/",
                    "/var/www/hametuha.com"
                ]
            },
            "mem": {
                "measurement": [
                    {
                        "name": "mem_used_percent",
                        "rename": "MEM_USED",
                        "unit": "Percent"
                    }
                ]
            },
            "swap": {
                "measurement": [
                    {
                        "name": "swap_used_percent",
                        "rename": "SWAP_USED",
                        "unit": "Percent"
                    }
                ]
            }
        },
        "append_dimensions": {
            "Environment": "${ENVIRONMENT}",
            "Application": "hametuha"
        }
    }
}
CLOUDWATCH_EOF

# CloudWatch Agent起動
/opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl \
    -a fetch-config \
    -m ec2 \
    -s \
    -c file:/opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json

# awslogsも設定（レガシー互換性のため）
dnf install -y awslogs

# awslogs設定
cat > /etc/awslogs/awslogs.conf << 'AWSLOGS_EOF'
[general]
state_file = /var/lib/awslogs/agent-state
use_gzip_http_content_encoding = true

[/var/log/messages]
datetime_format = %b %d %H:%M:%S
file = /var/log/messages
buffer_duration = 5000
log_stream_name = {instance_id}/messages
initial_position = start_of_file
log_group_name = /aws/ec2/hametuha/system/messages

[/var/log/user-data.log]
file = /var/log/user-data.log
buffer_duration = 5000
log_stream_name = {instance_id}/user-data
initial_position = start_of_file
log_group_name = /aws/ec2/hametuha/system/user-data
AWSLOGS_EOF

# リージョン設定
echo "region = ap-northeast-1" >> /etc/awslogs/awscli.conf

# awslogs起動
systemctl enable awslogsd
systemctl start awslogsd

echo "=== Monitoring Setup Completed ==="