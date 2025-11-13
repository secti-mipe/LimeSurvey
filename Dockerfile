FROM php:8.3-fpm-alpine

# Install PHP extensions
RUN apk update && apk add \
    mysql-client \
    gd \
    icu-dev \
    openldap-dev \
    php-ldap \
    php-imap \
    libzip-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    freetype-dev

RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_mysql gd intl zip ldap

WORKDIR /var/www/html

RUN chmod 775 /var/www/html/
