FROM php:8.2-fpm

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
    curl \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
# Set the working directory
COPY . /var/www/app
WORKDIR /var/www/app
RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 775 /var/www/app/storage

RUN docker-php-ext-configure opcache --enable-opcache  && docker-php-ext-configure gd --with-jpeg

RUN docker-php-ext-install pdo pdo_mysql sockets pdo_pgsql zip intl gd pgsql opcache
RUN curl -sS https://getcomposer.org/installer | php -- \
     --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Install redis
RUN apt-get update && apt-get install -y redis-server && pecl install redis && docker-php-ext-enable redis

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

#install git
RUN apt-get update && apt-get install -y git


RUN composer install

CMD php artisan serve --host=0.0.0.0 && php artisan migrate

