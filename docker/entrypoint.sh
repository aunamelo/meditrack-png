#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.docker.example ]; then
    cp .env.docker.example .env
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

php artisan storage:link --force --no-interaction 2>/dev/null || true

echo "Waiting for MySQL..."
attempt=0
until php -r "
  require 'vendor/autoload.php';
  \$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
  \$dotenv->safeLoad();
  try {
      new PDO(
          'mysql:host=' . (\$_ENV['DB_HOST'] ?? 'mysql') . ';port=' . (\$_ENV['DB_PORT'] ?? '3306'),
          \$_ENV['DB_USERNAME'] ?? '',
          \$_ENV['DB_PASSWORD'] ?? ''
      );
      exit(0);
  } catch (Throwable \$e) {
      exit(1);
  }
"; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge 90 ]; then
    echo "Database not ready after 3 minutes."
    exit 1
  fi
  sleep 2
done
echo "MySQL is ready."

php artisan migrate --force --no-interaction

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan optimize:clear --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

exec "$@"
