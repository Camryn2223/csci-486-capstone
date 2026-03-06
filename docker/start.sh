#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -z "$(grep '^APP_KEY=.\+' /var/www/html/.env)" ]; then
    php artisan key:generate
fi

if [ "$(grep '^APP_ENV=' /var/www/html/.env | cut -d '=' -f2)" = "local" ]; then
    echo "Waiting for MySQL to be ready..."
    until mysqladmin ping -h mysql -u laravel -psecret --silent 2>/dev/null; do
        sleep 2
    done
    echo "MySQL is ready."
    php artisan migrate --force
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

php-fpm -D
nginx -g "daemon off;"