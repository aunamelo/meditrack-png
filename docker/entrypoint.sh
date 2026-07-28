#!/bin/sh
set -e

cd /var/www/html

echo "==> MediTrack entrypoint starting"

if [ -d .env ]; then
    echo "ERROR: .env is a directory. On the VPS run: rm -rf .env && cp .env.docker.example .env"
    exit 1
fi

if [ ! -f .env ]; then
    if [ -f .env.docker.example ]; then
        cp .env.docker.example .env
        echo "==> Created .env from .env.docker.example"
    else
        echo "ERROR: No .env file found."
        exit 1
    fi
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "==> Generating APP_KEY"
    php artisan key:generate --force --no-interaction
fi

php artisan storage:link --force --no-interaction 2>/dev/null || true

echo "==> Waiting for MySQL"
attempt=0
until php -r "
  try {
      new PDO(
          'mysql:host=' . (getenv('DB_HOST') ?: 'mysql') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_DATABASE') ?: 'meditrack_png'),
          getenv('DB_USERNAME') ?: '',
          getenv('DB_PASSWORD') ?: ''
      );
      exit(0);
  } catch (Throwable \$e) {
      fwrite(STDERR, \$e->getMessage() . PHP_EOL);
      exit(1);
  }
"; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge 90 ]; then
    echo "ERROR: Database not ready after 3 minutes."
    exit 1
  fi
  sleep 2
done
echo "==> MySQL is ready"

echo "==> Running migrations"
php artisan migrate --force --no-interaction

if [ "${RUN_SEED:-false}" = "true" ]; then
    echo "==> Seeding database"
    php artisan db:seed --force --no-interaction || echo "==> Seed skipped or already applied"
fi

echo "==> Caching configuration"
php artisan optimize:clear --no-interaction || true
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Starting web server"
exec "$@"
