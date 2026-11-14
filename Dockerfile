# PerroniPhone — Contenedor de la aplicación Laravel (PHP-FPM)
FROM php:8.3-fpm

# --- Dependencias del sistema y extensiones de PHP que Laravel necesita ---
RUN apt-get update && apt-get install -y \
        git unzip zip curl \
        libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# --- Composer (el "NuGet"/"npm" de PHP) ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# --- Limpieza del directorio antes de la instalación ---
# Elimina /var/www/html y cualquier contenido por defecto para evitar el error de directorio no vacío
RUN rm -rf /var/www/*

# --- Esqueleto oficial de Laravel ---
# policy.advisories.block=false: Laravel 11 ya no recibe parches y Composer
# se niega a instalarlo si hay avisos de seguridad publicados. Se acepta
# solo porque es un proyecto académico que corre en local.
# El .env que deja create-project se borra: el nuestro lo crea entrypoint.sh
# en cada contenedor, con llaves propias (no quedan horneadas en la imagen).
RUN composer config --global policy.advisories.block false \
    && composer create-project laravel/laravel . "^11.0" --prefer-dist --no-interaction --no-progress \
    && rm -f .env

# --- Autenticación por JWT ---
RUN composer require php-open-source-saver/jwt-auth:^2.0 --no-interaction

# --- Nuestro código de aplicación se copia ENCIMA del esqueleto ---
COPY laravel-app/app/ ./app/
COPY laravel-app/routes/ ./routes/
COPY laravel-app/database/ ./database/
COPY laravel-app/public/ /var/www/public/
COPY laravel-app/config/cors.php ./config/cors.php
COPY laravel-app/config/auth.php ./config/auth.php
COPY laravel-app/bootstrap/app.php ./bootstrap/app.php
COPY laravel-app/.env.example ./.env.example

# --- config/jwt.php del paquete JWT (la llave se genera al arrancar) ---
RUN php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider" --force

# --- Script de arranque (ver docker/entrypoint.sh) ---
# El sed quita los saltos de línea de Windows (CRLF) por si el repo se
# clonó en Windows sin .gitattributes: con CRLF bash no puede ejecutarlo.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]