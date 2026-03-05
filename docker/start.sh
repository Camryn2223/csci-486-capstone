#!/bin/sh
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    php artisan key:generate
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

php-fpm -D
nginx -g "daemon off;"