FROM adclick/base:0.3-php7.3-fpm

RUN docker-php-ext-install \
            mysqli  \
    &&  docker-php-ext-enable \
            mysqli

COPY docker/php/fpm/conf/*.ini /usr/local/etc/php/conf.d/

COPY project/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html/
WORKDIR /var/www/html/
