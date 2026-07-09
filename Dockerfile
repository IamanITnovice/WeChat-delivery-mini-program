FROM php:8.1-fpm

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    default-mysql-client \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        opcache \
        curl \
        xml \
        fileinfo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 复制 Nginx 配置
COPY nginx.conf /etc/nginx/sites-available/default

# 复制数据库 SQL 文件
COPY db/ /db/

# 复制后端代码
COPY jjj_food_chain/ /var/www/html/

# 安装 PHP 依赖
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 创建运行时目录并设置权限
RUN mkdir -p runtime/cache runtime/log runtime/temp runtime/session \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/runtime

# 复制启动脚本
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
