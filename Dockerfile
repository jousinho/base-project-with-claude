FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libsodium-dev \
    && docker-php-ext-install -j$(nproc) \
        intl \
        zip \
        bcmath \
        sodium \
        pdo_mysql \
    && docker-php-ext-enable opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/symfony.ini /usr/local/etc/php/conf.d/symfony.ini

WORKDIR /var/www/html
