FROM php:8.2-apache

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY ./backend-php/ /var/www/html/

EXPOSE 80
