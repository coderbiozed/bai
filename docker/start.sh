#!/bin/sh
set -eu

cd /app

mkdir -p \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  database
touch database/database.sqlite
chmod -R 777 storage bootstrap/cache database || true

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
  echo "Generated ephemeral APP_KEY for this boot"
fi

php artisan config:clear --no-interaction || true
php artisan migrate --force --no-interaction

PROMPT_COUNT="$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo Illuminate\Support\Facades\Schema::hasTable("prompts")
    ? (string) Illuminate\Support\Facades\DB::table("prompts")->count()
    : "0";
')"

if [ "${PROMPT_COUNT}" = "0" ]; then
  echo "Empty database — seeding bAI library..."
  php artisan db:seed --force --no-interaction
else
  echo "Database already seeded (${PROMPT_COUNT} prompts) — skipping seed"
fi

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
