FROM php:5.6-apache

#RUN apt update
#RUN apt install software-properties-common -y
# remove stock versions
#RUN apt purge php.*

#RUN apt update -y
#RUN add-apt-repository ppa:ondrej/php
RUN apt update && apt upgrade -y

RUN docker-php-ext-install pdo mysql

RUN apt install php5-mysql

RUN a2enmod rewrite

# helpers
RUN apt install nano -y

# set server name
RUN echo "ServerName tms2" >> /etc/apache2/apache2.conf

COPY . /var/www/html
WORKDIR /var/www/html