FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    unzip \
    git \
    ca-certificates \
    gnupg \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring xml bcmath sockets pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /start.sh
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]