# EC2インスタンスの現在の状況

- デフォルトユーザーはec2-user
- php-fpmはec2-userで動作するようにしている（sshやsftpするとき簡単なので）


## /etc/awslog

### /etc/awslogs/awscli.conf

```conf
[plugins]
cwlogs = cwlogs
[default]
region = ap-northeast-1
```

### /etc/awslogs/awslogs.conf

```conf
[/var/log/php-fpm/www-error.log]
datetime_format = [%d-%b-%Y %H:%M:%S UTC]
time_zone = UTC
file = /home/ec2-user/var/log/php-fpm/7.2/www-error.log
log_stream_name = hametuha_{instance_id}
initial_position = start_of_file
log_group_name = /var/log/php-fpm/www-error
multi_line_start_pattern = {datetime_format}
buffer_duration = 5000

[/var/log/php-fpm/www-slow.log]
datetime_format = [%d-%b-%Y %H:%M:%S]
time_zone = UTC
file = /home/ec2-user/var/log/php-fpm/7.2/www-slow.log
log_stream_name = hametuha_{instance_id}
initial_position = start_of_file
log_group_name = /var/log/php-fpm/www-slow
multi_line_start_pattern = {datetime_format}
buffer_duration = 5000

[/var/log/nginx/access.log]
datetime_format = %d/%b/%Y:%H:%M:%S %z
time_zone = UTC
file = /var/log/nginx/access.log
log_stream_name = hametuha_{instance_id}
initial_position = start_of_file
log_group_name = /var/log/nginx
multi_line_start_pattern = {datetime_format}
buffer_duration = 5000
```

## wp-config.phpに書いてある内容

```php
# Memcachedのサーバー（Elasticache）
$memcached_servers = [ 'hametuhaobjectcache.bogjkp.0001.apne1.cache.amazonaws.com:11211' ];
// ePub Checkerのパス(ということは、Javaが必須）
define('EPUB_PATH', '/usr/bin/java -jar /usr/local/bin/epubcheck/epubcheck-3.0.1.jar ');
```


## PHP

`php -i` の結果を `infrastructure/tmp/php/phpinfo.txt`として保存



## Nginx

`/etc/nginx` を `infrastructure/tmp/nginx` に保存。

## Cron tab

```
crontab -l
* * * * * cd /var/www/vhosts/hametuha.com; php /var/www/vhosts/hametuha.com/wp-cron.php 2> /home/ec2-user/var/log/cron/cron.log
		  5 2 * * * cd /var/www/vhosts/hametuha.com; /usr/local/bin/wp haranking save_daily_pv 2> /home/ec2-user/var/log/cron/cron.log
		  5 3 * * * cd /var/www/vhosts/hametuha.com; /usr/local/bin/wp hamenew update_pv 2> /home/ec2-user/var/log/cron/cron.log
		  5 4 25 * * cd /var/www/vhosts/hametuha.com; /usr/local/bin/wp sales kdp  2> /home/ec2-user/var/log/cron/cron.log
		  25 4 25 * * cd /var/www/vhosts/hametuha.com; /usr/local/bin/wp sales news  2> /home/ec2-user/var/log/cron/cron.log
```
