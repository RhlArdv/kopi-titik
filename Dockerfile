FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    curl \
    git \
    unzip \
    oniguruma-dev \
    libxml2-dev \
    mysql-client \
    bash

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www

EXPOSE 8080

CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan migrate --force && \
    php -S 0.0.0.0:$PORT -t public
