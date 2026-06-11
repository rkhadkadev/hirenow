FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.3-apache

COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY public/ /var/www/html/
COPY --from=vendor /app/vendor/ /var/www/vendor/

RUN a2enmod rewrite headers

EXPOSE 80
