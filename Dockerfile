# Étape 1 : installer les dépendances de production avec Composer
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# Étape 2 : l'image finale, sans Composer ni dépendances de développement
FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql \
    && echo 'FallbackResource /index.php' > /etc/apache2/conf-enabled/fallback.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY src ./src
COPY public ./public
