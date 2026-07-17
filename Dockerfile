FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx wget postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql

RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

COPY . /var/www/html
WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && nginx && php-fpm"