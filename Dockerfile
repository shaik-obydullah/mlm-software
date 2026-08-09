# MLM Software - Laravel 13 Application

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        git \
        zip \
        unzip \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        sqlite-dev \
        bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_sqlite \
        intl \
        bcmath \
        opcache \
        zip \
        gd \
        exif

RUN apk add --no-cache nodejs npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-mlm.ini

RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && composer config --no-plugins allow-plugins.pestphp/pest-plugin true \
    && npm install --ignore-scripts \
    && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
