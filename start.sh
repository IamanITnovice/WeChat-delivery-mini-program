#!/bin/bash

# 从 MYSQL_ADDRESS (host:port) 中解析主机名和端口
MYSQL_HOST=$(echo $MYSQL_ADDRESS | cut -d':' -f1)
MYSQL_PORT=$(echo $MYSQL_ADDRESS | cut -d':' -f2)

# 如果端口为空则默认 3306
if [ -z "$MYSQL_PORT" ]; then
    MYSQL_PORT=3306
fi

# 数据库名固定为 jjj_food_chain
DB_NAME="jjj_food_chain"

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
DATABASE = ${DB_NAME}
USERNAME = ${MYSQL_USERNAME}
PASSWORD = ${MYSQL_PASSWORD}
HOSTPORT = ${MYSQL_PORT}
CHARSET = utf8mb4
prefix = jjjfood_
[LANG]
default_lang = zh-cn
[SECRET]
SALT = ${SECRET_SALT}
EOF

echo "=== .env generated ==="
cat /var/www/html/.env

# 初始化数据库（如果表不存在则导入）
echo "=== Checking database ==="
TABLE_COUNT=$(mysql -h${MYSQL_HOST} -P${MYSQL_PORT} -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';" \
    --skip-column-names 2>/dev/null || echo "0")

echo "Table count: $TABLE_COUNT"

if [ "$TABLE_COUNT" = "0" ] || [ -z "$TABLE_COUNT" ]; then
    echo "=== Creating database and importing SQL ==="
    mysql -h${MYSQL_HOST} -P${MYSQL_PORT} -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} \
        -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

    # 导入初始化SQL
    if [ -f "/db/init.sql" ]; then
        mysql -h${MYSQL_HOST} -P${MYSQL_PORT} -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} ${DB_NAME} \
            < /db/init.sql 2>/dev/null
        echo "=== init.sql imported ==="
    fi

    # 导入更新SQL
    if [ -f "/db/update20240430.sql" ]; then
        mysql -h${MYSQL_HOST} -P${MYSQL_PORT} -u${MYSQL_USERNAME} -p${MYSQL_PASSWORD} ${DB_NAME} \
            < /db/update20240430.sql 2>/dev/null
        echo "=== update20240430.sql imported ==="
    fi
else
    echo "=== Database already initialized, skipping ==="
fi

# 设置 runtime 目录权限
mkdir -p /var/www/html/runtime/cache \
         /var/www/html/runtime/log \
         /var/www/html/runtime/temp \
         /var/www/html/runtime/session
chown -R www-data:www-data /var/www/html/runtime
chmod -R 777 /var/www/html/runtime

echo "=== Starting PHP-FPM and Nginx ==="

# 启动 PHP-FPM
php-fpm -D

# 启动 Nginx（前台运行）
nginx -g "daemon off;"
