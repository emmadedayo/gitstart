# Use the official PHP 8.2 FPM image as the base
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    postgresql-client \
    libpng-dev \
    libzip-dev \
    nginx

# Debugging step: Check available PHP extensions
RUN docker-php-ext-configure pdo_pgsql --with-pdo-pgsql && \
    docker-php-ext-install pdo pdo_pgsql zip

# Set the working directory inside the container
WORKDIR /var/www

# Copy your application code into the container
COPY . /var/www

# Install Composer (if not already in the base image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies using Composer
# The COMPOSER_ALLOW_SUPERUSER=1 flag is often not recommended in production
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install

# Configure Nginx (copy your custom configuration)
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Generate JWT keypair (if using LexikJWTAuthenticationBundle)
#RUN php bin/console lexik:jwt:generate-keypair

# Expose port 80 (or whichever port Nginx is configured to use)
EXPOSE 80

# Start both PHP-FPM and Nginx in the foreground
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
