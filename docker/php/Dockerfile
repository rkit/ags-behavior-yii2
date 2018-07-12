FROM php:7.1-fpm

# --- EXTENSIONS --- #

RUN docker-php-ext-install pdo_mysql

# --- COMPOSER --- #

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# --- DATA --- #

WORKDIR /var/www/html
