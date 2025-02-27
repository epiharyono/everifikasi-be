ARG PHP_VERSION=8.2-apache
ARG NODE_VERSION=14.15-alpine3.13

FROM php:${PHP_VERSION} as php_laravel
# install dependencies for laravel 8
RUN apt-get update && apt-get install -y \
  curl \
  git \
  libicu-dev \
  libpq-dev \
  libmcrypt-dev \
  mariadb-client \
  openssl \
  unzip \
  vim \
  zip \
  zlib1g-dev \
  libpng-dev \
  libzip-dev && \
rm -r /var/lib/apt/lists/*
# install extension for laravel 8
#RUN pecl install mcrypt-1.0.4
RUN docker-php-ext-install fileinfo exif pcntl bcmath gd mysqli pdo_mysql
#RUN docker-php-ext-enable mcrypt && \
RUN a2enmod rewrite

# install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV APP_SOURCE /var/www/php

# Set working directory
WORKDIR $APP_SOURCE

COPY .docker/000-default.apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY .docker/apache2-foreground .docker/apache2-foreground
COPY .docker/php-artisan-migrate-foreground .docker/php-artisan-migrate

CMD [".docker/apache2-foreground"]

FROM node:$NODE_VERSION as laramix_build

WORKDIR /var/www/php
COPY . .
#RUN npm install -q && \
#    npm run-script prod

FROM php_laravel as executeable
ENV APP_SOURCE /var/www/php

# copy source laravel
COPY . .

RUN rm -rf composer.lock
RUN pwd
RUN ls
RUN ls public/
# give full access
RUN rm -rf public/storage
RUN mkdir -p public/storage
RUN chmod -R 777 storage/*
RUN chmod -R 777 public/storage
RUN chmod -R 777 .docker/*


RUN rm -rf storage/app/public/*

# install dependency laravel

RUN pwd
RUN ls

RUN php -r "file_exists('.env') || copy('.env.example', '.env');"
RUN composer install --no-interaction --optimize-autoloader --no-dev

#RUN php artisan package:discover --ansi && \
#    php artisan key:generate --ansi --force && \
#    php artisan optimize

RUN php artisan key:generate --ansi --force && \
    php artisan optimize
    
RUN echo "upload_max_filesize=50M" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size=50M" >> /usr/local/etc/php/php.ini

VOLUME ${APP_SOURCE}/storage
# expose port default 80
EXPOSE 80/tcp
