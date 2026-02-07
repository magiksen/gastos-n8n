#!/bin/sh
set -e

cd /var/www/html

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link 2>/dev/null || true

# Cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure correct permissions
chown -R www-data:www-data storage bootstrap/cache database

# Create supervisor log dir
mkdir -p /var/log/supervisor

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
