FROM adclick/base:latest-nginx1.15

COPY ./docker/nginx/conf/*.conf /etc/nginx/conf.d/

COPY project/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html/
WORKDIR /var/www/html/