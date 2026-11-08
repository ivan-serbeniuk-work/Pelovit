#!/bin/sh
set -e

# Wait until the database accepts connections
echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 2
done
echo "Database is up."

# Make sure storage is writable and linked (volumes may reset ownership).
# public/videos — свіжий том, який монтується від root; тека мала, тож
# recursive chown тут дешевий (на відміну від public/images).
chown -R www-data:www-data storage bootstrap/cache public/videos || true
php artisan storage:link || true

# Run migrations, then cache config/routes/views for production
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
