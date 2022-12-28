FROM php:8.1.11-fpm-alpine3.16

RUN apk add --no-cache $PHPIZE_DEPS openssl-dev
RUN apk add --no-cache icu-dev
RUN apk add --no-cache linux-headers

RUN docker-php-ext-install pdo pdo_mysql intl opcache

RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install xdebug && docker-php-ext-enable xdebug