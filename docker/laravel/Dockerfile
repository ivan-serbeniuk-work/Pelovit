# Laravel API + MoonShine admin — production image (php-fpm + nginx + supervisor)
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor unzip git \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip gd bcmath intl opcache exif \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# App source (frontend/ excluded via .dockerignore)
COPY . .
# public/videos створюємо тут навмисно: тека порожня й ґітігнорена, а якщо
# її немає в образі, Docker створює точку монтування тому від root — і
# php-fpm (www-data) не може туди записати жодного ролика.
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p public/videos \
    && chown -R www-data:www-data storage bootstrap/cache public/videos

COPY docker/laravel/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/laravel/nginx.conf /etc/nginx/sites-available/default
COPY docker/laravel/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/laravel/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
