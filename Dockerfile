FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libsodium-dev \
        libpq-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) \
        intl \
        zip \
        bcmath \
        sodium \
        pdo_pgsql \
        pgsql \
    && pecl install redis \
    && docker-php-ext-enable opcache redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/symfony.ini /usr/local/etc/php/conf.d/symfony.ini

WORKDIR /var/www/html
