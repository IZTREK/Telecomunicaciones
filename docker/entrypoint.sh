#!/usr/bin/env bash
# PerroniPhone — Se ejecuta cada vez que arranca el contenedor "app".
# Deja Laravel listo (clave, JWT, migraciones, datos ficticios) sin que
# nadie tenga que entrar a mano a correr comandos de artisan.
set -e

cd /var/www

# --- 1) Archivo .env: si no existe, se crea a partir del ejemplo ---
if [ ! -f .env ]; then
    echo "[entrypoint] Creando .env a partir de .env.example"
    cp .env.example .env
fi

# --- 2) Variables de conexión a MySQL (vienen del docker-compose.yml) ---
# PHP-FPM no pasa las variables del contenedor a PHP, por eso se escriben
# en el .env. Se usa "|" como separador para tolerar "/" en los valores.
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

# --- 3) Clave de la aplicación (cifra sesiones, cookies, etc.) ---
if ! grep -q "^APP_KEY=base64" .env; then
    echo "[entrypoint] Generando APP_KEY"
    php artisan key:generate --force
fi

# --- 4) Llave para firmar los JWT ---
if ! grep -q "^JWT_SECRET=.\+" .env; then
    echo "[entrypoint] Generando JWT_SECRET"
    php artisan jwt:secret --force
fi

# --- 5) Migraciones + datos ficticios ---
# MySQL ya está "healthy" gracias al healthcheck del docker-compose.
# El seeder es idempotente: sincroniza la matriz de permisos siempre y solo
# carga datos de ejemplo en tablas vacías.
echo "[entrypoint] Corriendo migraciones y seeders"
php artisan migrate --force
php artisan db:seed --force

# --- 6) Permisos para que Laravel pueda escribir logs/caché ---
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "[entrypoint] Listo. Arrancando PHP-FPM..."
exec "$@"
