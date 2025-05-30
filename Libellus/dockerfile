FROM php:apache

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN openssl req -nodes -x509 -newkey rsa:2048 \
    -keyout /etc/ssl/private/localhost.key \
    -out /etc/ssl/certs/localhost.crt \
    -days 365 \
    -subj "/CN=localhost"


# Copiar el .conf a apache
COPY ./default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

# Habilitar ssl y el sitio default
RUN a2enmod ssl && \
    a2enmod rewrite && \
    a2ensite default-ssl

# Copiar mis vistas a la ruta de apache
COPY ./src /var/www/html/