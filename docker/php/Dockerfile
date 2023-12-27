FROM php:8.2-fpm
LABEL maintainer="admin"

# Set working directory
WORKDIR /var/www

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
# add opcache and configuration php.ini for opcache
#RUN echo "opcache.enable=1" >> "$PHP_INI_DIR/php.ini"
#RUN echo "opcache.memory_consumption=512" >> "$PHP_INI_DIR/php.ini"
#RUN echo "opcache.interned_strings_buffer=64" >> "$PHP_INI_DIR/php.ini"
#RUN echo "opcache.max_accelerated_files=32531" >> "$PHP_INI_DIR/php.ini"
#RUN echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/php.ini"
#RUN echo "opcache.fast_shutdown=0" >> "$PHP_INI_DIR/php.ini"

# Install php extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached opcache

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    lua-zlib-dev \
    libmemcached-dev \
    nginx

# Install supervisor
RUN apt-get install -y supervisor

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#install redis
RUN pecl install redis \
    && docker-php-ext-enable redis.so
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy code to /var/www
COPY --chown=www:www-data . /var/www

# add root to www group
RUN chmod -R ug+w /var/www/storage

# Copy nginx/php/supervisor configs
RUN cp docker/php/supervisor.conf /etc/supervisord.conf
RUN cp docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Deployment steps
#RUN composer install --optimize-autoloader --no-dev
#RUN chmod +x /var/www/docker/run.sh
#
#ENTRYPOINT ["/var/www/docker/run.sh"]
