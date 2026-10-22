FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpng-dev \
        libcurl4-openssl-dev \
        libonig-dev \
    && docker-php-ext-install mysqli gd curl zip mbstring \
    && a2enmod rewrite proxy proxy_http headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/phpmyadmin-proxy.conf /etc/apache2/conf-enabled/phpmyadmin-proxy.conf
COPY docker/spapi-proxy.conf /etc/apache2/conf-enabled/spapi-proxy.conf
