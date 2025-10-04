FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev libssl-dev \
    libpng-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql mbstring zip pcntl

WORKDIR /var/www/html
ENTRYPOINT ["php", "/var/www/html/artisan", "queue:work"]
