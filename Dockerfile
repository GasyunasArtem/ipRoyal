FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    netcat-openbsd \
    cron \
    supervisor \
    sudo

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

WORKDIR /var/www

COPY --chown=www:www . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && chown -R www:www /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

COPY start-supervisor.sh /usr/local/bin/start-supervisor.sh
RUN chmod +x /usr/local/bin/start-supervisor.sh

COPY docker-init.sh /var/www/docker-init.sh
RUN chmod +x /var/www/docker-init.sh



USER www

EXPOSE 8000

CMD ["/var/www/docker-init.sh"]
