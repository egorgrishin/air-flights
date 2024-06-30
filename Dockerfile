FROM php:8.3.8-fpm-alpine
WORKDIR /var/www/html
COPY . .

RUN apk add --update linux-headers

RUN docker-php-ext-install mysqli pdo_mysql pdo calendar sockets
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

RUN composer install

RUN apk add autoconf
RUN apk add build-base
RUN pecl install xdebug redis && docker-php-ext-enable xdebug redis
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini