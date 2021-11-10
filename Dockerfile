FROM php:8.0.7-cli-buster

ENV TIMEZONE=Etc/GMT+0

RUN pecl install swoole \
    && docker-php-ext-enable swoole

WORKDIR /usr/src/app

CMD php main.php