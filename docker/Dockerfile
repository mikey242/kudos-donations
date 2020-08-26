FROM wordpress:5.3-php7.1

WORKDIR /var/www/html

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get update \
    && apt-get install nano \
    && export TERM=xterm
