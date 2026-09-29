#!/bin/sh

# Dynamically set Nginx listen port from Render's $PORT environment variable
sed -i "s/listen 10000;/listen ${PORT:-10000};/g" /etc/nginx/http.d/default.conf

# Cache configuration, routes, and views for production speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Execute database migrations
php artisan migrate --force

# Start Supervisor (launches both Nginx and PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf