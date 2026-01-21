FROM php:8.2-cli-alpine

RUN apk add --no-cache git curl libzip-dev zip netcat-openbsd

RUN docker-php-ext-install zip

# Install PCOV for fast code coverage
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
