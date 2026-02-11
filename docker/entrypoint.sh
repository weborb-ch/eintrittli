#!/bin/sh
set -e

php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan migrate --force

exec "$@"
