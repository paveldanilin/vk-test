FROM php:7.4-fpm

RUN apt-get update
RUN apt-get install -y --force-yes \
    && apt-get install -y zip unzip libmemcached-dev zlib1g-dev librabbitmq-dev supervisor \
    && pecl install memcached-3.1.5 \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable memcached \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli \
    && pecl install amqp \
    && docker-php-ext-enable amqp

ADD docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
ADD docker/php/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"
ADD docker/php/supervisor.worker.conf /etc/supervisor/conf.d/supervisor.worker.conf
ADD docker/php/supervisord.conf /etc/supervisor/supervisord.conf
ADD docker/php/docker_entrypoint.sh /docker_entrypoint.sh

# app => container
COPY src/ /var/www

RUN chown -R www-data:www-data /var/www/app
RUN chown -R www-data:www-data /var/www/lib
RUN chmod +x /docker_entrypoint.sh

ENTRYPOINT ["/docker_entrypoint.sh"]
# CMD ["php-fpm", "-F"]
