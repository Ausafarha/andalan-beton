# Dockerfile untuk PHP 8.3 + Apache (Render.com)
FROM php:8.3-apache

# Install ekstensi PHP yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_pgsql pgsql zip

# Enable mod_rewrite Apache
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy semua file project ke container
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies via Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permission
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 8080 (Render pake 8080)
EXPOSE 8080

# Jalankan Apache dengan port 8080
CMD ["apache2-foreground"]