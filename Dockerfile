FROM php:7.2.6-cli-alpine3.7

RUN apk add --update autoconf alpine-sdk libstdc++ && \
    printf "\n" | pecl install swoole && \
    echo "extension=swoole.so" > /usr/local/etc/php/conf.d/swoole.ini && \
    apk del --purge autoconf alpine-sdk
