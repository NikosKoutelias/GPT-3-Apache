FROM php:8.1-apache

COPY /httpd.conf /etc/apache2/sites-enabled/000-default.conf

RUN a2enmod rewrite

RUN apt-get update \
    && apt-get install sudo
    
RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpng-dev libldap2-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && apt-get install -y libxml2-dev \
    && apt install libsodium-dev \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-install gd \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install ldap 

ADD /custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

WORKDIR /var/www/html

RUN apt-get purge -y g++ \
    && apt-get autoremove -y \
    && rm -r /var/lib/apt/lists/* \
    && rm -rf /tmp/* 

COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY /error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini