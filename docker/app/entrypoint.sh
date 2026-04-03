#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ -f .env.docker ]; then
  cp .env.docker .env
fi

composer install --no-interaction --prefer-dist

php artisan key:generate --force
php artisan migrate --force

exec "$@"
