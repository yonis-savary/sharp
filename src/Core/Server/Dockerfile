# --- Sharp Sample Dockerfile
# This file is an example file that can be customized
# It contains some code that can be extended/edited/deleted
#
# Please consider this file as a boilerplate, do not use it as-is
#
# --- Build & Run
# docker build -t sharp-image-name .
# docker run --rm --name my-instance -d -p 8000:8000 sharp-image-name
# docker exec -ti my-instance sh

FROM php:8.2.25-cli-alpine3.20

# Libraries
RUN apk add --no-cache wget \
 && apk add --no-cache nano \
 && apk add --no-cache git \
 && apk add --no-cache curl \
 && apk add --no-cache libzip-dev \
 && apk add --no-cache zip \
 #
 # ------ PHP extension
 && docker-php-ext-install pdo_mysql \
 && docker-php-ext-install zip \
 && docker-php-ext-install gd
 #
 # ------ Node packages
 # && apk add --no-cache nodejs \
 # && apk add --no-cache npm \
 # && npm i -g less \
 # && npm cache clean --force \
 #
 # ------ Install PHP imagick
 # && apk add --no-cache libpng-dev \
 # && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS imagemagick-dev libtool \
 # && export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
 # && pecl install imagick-3.7.0 \
 # && docker-php-ext-enable imagick \
 # && apk add --no-cache --virtual .imagick-runtime-deps imagemagick \
 # && apk del .phpize-deps


# Setup
WORKDIR /app
COPY . .

RUN curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/bin/composer \
 && composer install

RUN php do build \
 && php do create-configuration \
 && php do cache-enable

EXPOSE 8000

CMD [ "php", "do", "serve", "--hostname='0.0.0.0'", "--port=8000" ]
