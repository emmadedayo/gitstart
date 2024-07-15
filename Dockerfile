FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev \
    libzip-dev default-mysql-client nginx

RUN docker-php-ext-install pdo pdo_mysql zip gd

WORKDIR /var/www

COPY . /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN php bin/console lexik:jwt:generate-keypair

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]