FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libsodium-dev \
        libpq-dev \
        rabbitmq-c-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) \
        intl \
        zip \
        bcmath \
        sodium \
        pdo_pgsql \
        pgsql \
    && pecl install amqp \
    && docker-php-ext-enable opcache amqp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/symfony.ini /usr/local/etc/php/conf.d/symfony.ini

WORKDIR /var/www/html
