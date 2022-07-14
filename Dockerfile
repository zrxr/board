FROM php:8.1-apache-bullseye
EXPOSE 80
COPY 'src/webroot/index.php' '/var/www/html/index.php'
RUN curl 'https://code.jquery.com/jquery-3.6.0.min.js' > '/var/www/html/jquery.js'
RUN mkdir -p /var/board/requests && chown -R www-data:www-data /var/board && chmod -R 775 /var/board
