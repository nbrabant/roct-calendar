FROM php:8.4-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    opcache \
    zip

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation de Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Configuration du répertoire de travail
WORKDIR /var/www/symfony

# Configuration de l'utilisateur
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Exposition du port
EXPOSE 9000

CMD ["php-fpm"]
