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
RUN composer config --global policy.advisories.block false \
    && composer create-project laravel/laravel . "^11.0" --prefer-dist --no-interaction --no-progress
# --- Autenticación por JWT ---
RUN composer require php-open-source-saver/jwt-auth --no-interaction

# --- Nuestro código de aplicación se copia ENCIMA del esqueleto ---
COPY laravel-app/app/ ./app/
COPY laravel-app/routes/ ./routes/
COPY laravel-app/database/ ./database/
COPY laravel-app/public/ /var/www/public/
COPY laravel-app/config/cors.php ./config/cors.php
COPY laravel-app/config/auth.php ./config/auth.php
COPY laravel-app/bootstrap/app.php ./bootstrap/app.php
COPY laravel-app/.env.example ./.env.example

# --- Configuración base (.env, APP_KEY, config de JWT y su llave) ---
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider" --force \
    && php artisan jwt:secret --force

# --- Script de arranque (ver docker/entrypoint.sh) ---
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]