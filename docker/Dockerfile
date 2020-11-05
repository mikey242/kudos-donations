ARG PHP_VERSION
ARG WORDPRESS_VERSION

FROM wordpress:${WORDPRESS_VERSION}-php${PHP_VERSION}

WORKDIR /var/www/html

RUN pecl install xdebug redis \
    && docker-php-ext-enable xdebug redis \
    && apt-get update \
    && apt-get install nano \
    && export TERM=xterm
