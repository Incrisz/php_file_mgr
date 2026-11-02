FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers
COPY apache-security.conf /etc/apache2/conf-enabled/security-files.conf
WORKDIR /var/www/html
