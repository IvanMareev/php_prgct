FROM php:8.2-fpm

# Устанавливаем системные зависимости для Laravel и PHP-расширений
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Копируем свой php.ini
COPY php.ini /usr/local/etc/php/php.ini

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем проект
COPY . .

# Устанавливаем зависимости Composer без dev-пакетов
RUN composer install --no-dev --optimize-autoloader

# Даем права на storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache
