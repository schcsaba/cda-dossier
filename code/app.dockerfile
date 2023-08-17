FROM php:8.1-fpm-alpine

WORKDIR /var/www/apps/pipelinedoc-source/
RUN mkdir /var/log/supervisor
RUN docker-php-ext-install pdo pdo_mysql
RUN apk add --update supervisor && rm  -rf /tmp/* /var/cache/apk/*

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug-3.2.0 \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps

RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug,develop,coverage" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log=/var/www/html/xdebug/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.max_nesting_level=512" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:2.4.4 /usr/bin/composer /usr/local/bin/composer

USER root
COPY ./apps/pipelinedoc-source .

ADD supervisord.conf /etc/

RUN composer install
RUN chown www-data:www-data -R storage/
RUN php artisan config:cache

EXPOSE 9000
EXPOSE 9001

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
RUN php artisan config:cache