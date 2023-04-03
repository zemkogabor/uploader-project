FROM composer:latest as composer_stage

WORKDIR /app

COPY backend/composer.lock /app
COPY backend/composer.json /app

RUN composer install --ignore-platform-reqs --prefer-dist --no-scripts --no-progress --no-interaction --no-dev

FROM php:8.2-fpm-bullseye

WORKDIR /app

COPY --from=composer_stage /app /app

# libpq-dev - Required for pdo_pgsql extenstion
# zip - Required for composer
# libzip-dev - Required for zip extenstion
RUN apt-get update
RUN apt-get install -y -q --no-install-recommends \
    libpq-dev \
    zip \
    libzip-dev

# pdo pdo_pgsql - PostgreSQL driver
# zip - Required for composer
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip

# Copy php config
COPY _docker/backend/prod/base.ini /usr/local/etc/php/conf.d/base.ini

# Copy folders
COPY backend/bin /app/bin
COPY backend/config /app/config
COPY backend/migrations /app/migrations
COPY backend/public /app/public
COPY backend/src /app/src

# Create keys folder
RUN mkdir -p /app/var/keys
VOLUME /app/var/keys

ARG wwwdatauid=1000
RUN usermod -u $wwwdatauid www-data
RUN chown -R www-data:www-data /app

# https://www.doctrine-project.org/projects/doctrine-orm/en/2.14/reference/advanced-configuration.html#generating-proxy-classes
ENTRYPOINT sh -c "php bin/cli.php orm:generate-proxies && php-fpm"

