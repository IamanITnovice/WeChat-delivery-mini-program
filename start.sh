#!/bin/bash

# 启动 PHP-FPM
php-fpm -D

# 启动 Nginx（前台运行）
nginx -g "daemon off;"
