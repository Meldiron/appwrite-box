FROM php:8.3.10-cli-alpine3.20

WORKDIR /root

RUN apk update && apk add docker-cli docker-cli-compose nodejs npm

RUN docker-php-ext-configure pcntl --enable-pcntl && docker-php-ext-install pcntl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN npm i -g appwrite-cli@6.0.0-rc.9

COPY composer.json composer.json

RUN composer install

COPY appwrite appwrite

COPY src src


CMD ["php", "src/app.php"]
