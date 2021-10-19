FROM php:7.1.3-apache

WORKDIR /srv/app

RUN set -xe \
    && buildDeps=" \
    $PHP_EXTRA_BUILD_DEPS \
    libcurl4-openssl-dev \
    libedit-dev \
    libsqlite3-dev \
    libxml2-dev \
    libpng-dev \
    " \
    && apt-get update && apt-get install -y gettext-base $buildDeps \
    libmcrypt-dev libpq-dev libssl-dev --no-install-recommends \
    && docker-php-ext-install -j$(nproc) \
    gd mcrypt pgsql pdo_pgsql zip soap bcmath sockets \
    && php -r "copy('http://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && apt-get purge -y $buildDeps --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/*

RUN chown -R www-data:www-data /srv/app \
    && a2enmod rewrite && a2enmod headers
