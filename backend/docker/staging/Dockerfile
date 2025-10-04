FROM serversideup/php:8.4-fpm-nginx AS base

USER root
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd
COPY ./docker/staging/nginx/default.conf /etc/nginx/conf.d/default.conf
RUN docker-php-serversideup-set-id www-data 1000:1000 && \
    docker-php-serversideup-set-file-permissions --owner 1000:1000 --service nginx

COPY --chown=www-data:www-data . /var/www/html
USER www-data

FROM base AS test
COPY --chown=1000:1000 .env.example /var/www/html/.env

USER www-data
RUN composer install
