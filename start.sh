#!/bin/bash

# 从 MYSQL_ADDRESS (host:port) 中解析主机名和端口
MYSQL_HOST=$(echo $MYSQL_ADDRESS | cut -d':' -f1)
MYSQL_PORT=$(echo $MYSQL_ADDRESS | cut -d':' -f2)

# 如果端口为空则默认 3306
if [ -z "$MYSQL_PORT" ]; then
    MYSQL_PORT=3306
fi

# 如果 SECRET_SALT 未设置则使用默认值
if [ -z "$SECRET_SALT" ]; then
    SECRET_SALT="F28zbNMMFyacZpUBpjykFEppOhJFV20mh3M18laLnAWyCLP9"
fi

# 自动生成 .env 文件
cat > /var/www/html/.env << EOF
APP_DEBUG = false
[APP]
DEFAULT_TIMEZONE = Asia/Shanghai
[DATABASE]
TYPE = mysql
HOSTNAME = ${MYSQL_HOST}
DATABASE = ${MYSQL_DATABASE:-jjj_food_chain}
USERNAME = ${MYSQL_USERNAME:-root}
PASSWORD = ${MYSQL_PASSWORD}
HOSTPORT = ${MYSQL_PORT}
CHARSET = utf8mb4
prefix = jjjfood_
[LANG]
default_lang = zh-cn
[SECRET]
SALT = ${SECRET_SALT}
EOF

echo ".env generated:"
cat /var/www/html/.env

# 设置 runtime 目录权限
mkdir -p /var/www/html/runtime/cache \
         /var/www/html/runtime/log \
         /var/www/html/runtime/temp \
         /var/www/html/runtime/session
chown -R www-data:www-data /var/www/html/runtime
chmod -R 777 /var/www/html/runtime

# 启动 PHP-FPM
php-fpm -D

# 启动 Nginx（前台运行保持容器存活）
nginx -g "daemon off;"
