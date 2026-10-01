# syntax=docker/dockerfile:1.7

# =========================================================
# Plantilla Docker multi-stage para Laravel 13
# Taller Integrador de Especialización
# =========================================================


ARG PHP_VERSION=8.4
ARG NODE_VERSION=20

# -----------------------------------------------------
# Etapa 1: dependencias de Composer
# -----------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock* ./


RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# -----------------------------------------------------
# Etapa 2: build de assets front-end (Vite)
# -----------------------------------------------------
FROM node:${NODE_VERSION}-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build

# -----------------------------------------------------
# Etapa 3: imagen base PHP-FPM (común a dev y prod)
# -----------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS base

# Dependencias del sistema necesarias para las extensiones PHP
RUN apk add --no-cache \
        bash \
        git \
        curl \
        libpng-dev \
        libzip-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        icu-dev \
        postgresql-dev \
        openssl-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb \
    && apk del $PHPIZE_DEPS

# Usuario no root para ejecutar la aplicación
RUN addgroup -g 1000 laravel \
    && adduser -G laravel -u 1000 -D laravel

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini


RUN mkdir -p /var/www/html/vendor \
    && chown -R laravel:laravel /var/www/html

# -----------------------------------------------------
# Etapa 4: imagen de DESARROLLO
# -----------------------------------------------------
FROM base AS development

ENV APP_ENV=local \
    APP_DEBUG=true

# xdebug solo en desarrollo
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

COPY --from=vendor /usr/bin/composer /usr/bin/composer

USER laravel

EXPOSE 9000
CMD ["php-fpm"]

# -----------------------------------------------------
# Etapa 5: imagen de PRODUCCIÓN (mínima y segura)
# -----------------------------------------------------
FROM base AS production

ENV APP_ENV=production \
    APP_DEBUG=false

COPY --chown=laravel:laravel . .
COPY --from=vendor --chown=laravel:laravel /app/vendor ./vendor
COPY --from=assets --chown=laravel:laravel /app/public/build ./public/build

RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

USER laravel

EXPOSE 9000
CMD ["php-fpm"]
