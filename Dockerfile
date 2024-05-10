FROM php:8.1-fpm

LABEL builder="I love you so muchh"

RUN apt-get update && apt-get install -y \
    autoconf \
    g++ \
    make \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev \
    libzip-dev \
    zlib1g-dev \
    ffmpeg \
    curl

RUN docker-php-ext-configure opcache --enable-opcache  && docker-php-ext-configure gd --with-jpeg


RUN docker-php-ext-install pdo pdo_mysql sockets pdo_pgsql zip intl gd pgsql opcache
RUN curl -sS https://getcomposer.org/installer | php -- \
     --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install redis
RUN pecl install redis \
    && docker-php-ext-enable redis.so

WORKDIR /app
COPY . .
RUN composer install
