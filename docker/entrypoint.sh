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

# Docker env_file often injects APP_KEY= (empty). An empty env var blocks
# `php artisan key:generate` ("already present in the environment") and also
# overrides a real key written only inside the container .env file.
is_valid_app_key() {
    case "${1:-}" in
        base64:?*) return 0 ;;
        *) return 1 ;;
    esac
}

FILE_KEY="$(grep -E '^APP_KEY=' .env 2>/dev/null | head -n1 | cut -d= -f2- || true)"
# Persist across restarts when host .env still has APP_KEY= empty
KEY_FILE="storage/app/.app_key"
STORED_KEY=""
if [ -f "$KEY_FILE" ]; then
    STORED_KEY="$(tr -d '\r\n' < "$KEY_FILE")"
fi

if is_valid_app_key "${APP_KEY:-}"; then
    echo "==> Using APP_KEY from environment"
elif is_valid_app_key "$FILE_KEY"; then
    echo "==> Using APP_KEY from .env file"
    APP_KEY="$FILE_KEY"
elif is_valid_app_key "$STORED_KEY"; then
    echo "==> Using APP_KEY from persistent storage"
    APP_KEY="$STORED_KEY"
else
    echo "==> Generating APP_KEY"
    unset APP_KEY
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi

export APP_KEY

if ! is_valid_app_key "$APP_KEY"; then
    echo "ERROR: APP_KEY is still empty after key resolution."
    exit 1
fi

# Keep container .env + volume copy in sync (host .env may still be empty)
if grep -qE '^APP_KEY=' .env 2>/dev/null; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
else
    echo "APP_KEY=${APP_KEY}" >> .env
fi

mkdir -p storage/app
printf '%s' "$APP_KEY" > "$KEY_FILE"
chmod 600 "$KEY_FILE" 2>/dev/null || true

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
