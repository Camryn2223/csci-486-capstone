#!/bin/sh

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

    cat > /tmp/wait_mysql.php << 'PHPEOF'
<?php
try {
    new PDO('mysql:host=mysql;port=3306;dbname=laravel', 'laravel', 'secret');
    echo 'ok';
} catch (Exception $e) {
    echo 'fail';
}
PHPEOF

    while true; do
        result=$(php /tmp/wait_mysql.php 2>/dev/null)
        if [ "$result" = "ok" ]; then
            break
        fi
        echo "MySQL not ready, retrying in 2 seconds..."
        sleep 2
    done

    echo "MySQL is ready."
    php artisan migrate --force
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

php-fpm -D
nginx -g "daemon off;"