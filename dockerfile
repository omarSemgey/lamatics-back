FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && \
    apt-get install -y \
        git \
        unzip \
        libpng-dev \
        libonig-dev \
        libzip-dev \
        zip \
        libpq-dev \
        curl \
        cron \
        supervisor \
        nano \
        pkg-config \
        libssl-dev \
        libxml2-dev && \
    docker-php-ext-install pdo pdo_pgsql zip && \
    pecl install redis && \
    docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD php artisan migrate --force && \
    php artisan db:seed\
    php artisan serve --host=0.0.0.0 --port=9000