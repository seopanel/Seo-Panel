FROM php:7.4-apache

RUN sed -i \
        -e '/bullseye-security/d' \
        -e 's|deb.debian.org/debian|archive.debian.org/debian|g' \
        /etc/apt/sources.list \
    && apt-get -o Acquire::Check-Valid-Until=false update \
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
