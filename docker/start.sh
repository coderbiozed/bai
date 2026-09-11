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

# Restore packaged library DB if the runtime DB is missing/empty (ephemeral disk).
if [ ! -s database/database.sqlite ] && [ -f database/demo.sqlite ]; then
  cp database/demo.sqlite database/database.sqlite
  echo "Restored packaged demo.sqlite"
fi
touch database/database.sqlite
chmod -R 777 storage bootstrap/cache database || true

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Render generateValue keys break Laravel aes-256. Force a valid key when needed.
# Prefer a stable key from env if it is already valid.
php -r '
$key = getenv("APP_KEY") ?: "";
$ok = false;
if (str_starts_with($key, "base64:")) {
    $raw = base64_decode(substr($key, 7), true);
    $ok = is_string($raw) && strlen($raw) === 32;
}
exit($ok ? 0 : 1);
' || {
  # Stable fallback (also set in render.yaml) — avoids 500 on boot.
  export APP_KEY="base64:eOmCqltp/cpmpEdwAkynEMoP8OC9/PKrU7d8UPkvkxY="
  echo "Applied stable valid Laravel APP_KEY"
}

KEY="$APP_KEY" URL="${APP_URL:-}" php -r '
$envPath = ".env";
$env = file_exists($envPath) ? file_get_contents($envPath) : file_get_contents(".env.example");
$key = getenv("KEY") ?: "";
$url = getenv("URL") ?: "";
$env = preg_replace("/^APP_KEY=.*/m", "APP_KEY=".$key, $env, 1, $c1);
if ($c1 === 0) { $env .= "\nAPP_KEY=".$key."\n"; }
if ($url !== "") {
  $env = preg_replace("/^APP_URL=.*/m", "APP_URL=".$url, $env, 1, $c2);
  if ($c2 === 0) { $env .= "\nAPP_URL=".$url."\n"; }
}
$env = preg_replace("/^APP_ENV=.*/m", "APP_ENV=production", $env, 1);
$env = preg_replace("/^APP_DEBUG=.*/m", "APP_DEBUG=false", $env, 1);
$env = preg_replace("/^SESSION_DRIVER=.*/m", "SESSION_DRIVER=file", $env, 1);
$env = preg_replace("/^CACHE_STORE=.*/m", "CACHE_STORE=file", $env, 1);
$env = preg_replace("/^QUEUE_CONNECTION=.*/m", "QUEUE_CONNECTION=sync", $env, 1);
file_put_contents($envPath, $env);
'

rm -f bootstrap/cache/config.php \
      bootstrap/cache/routes-v7.php \
      bootstrap/cache/routes.php \
      bootstrap/cache/events.php

# Schema only — library data comes from packaged demo.sqlite (fast cold start).
php artisan migrate --force --no-interaction

PROMPT_COUNT="$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo Illuminate\Support\Facades\Schema::hasTable("prompts")
    ? (string) Illuminate\Support\Facades\DB::table("prompts")->count()
    : "0";
')"

if [ "${PROMPT_COUNT}" = "0" ] && [ -f database/demo.sqlite ]; then
  cp database/demo.sqlite database/database.sqlite
  echo "Re-copied demo.sqlite (prompt count was 0)"
elif [ "${PROMPT_COUNT}" = "0" ]; then
  echo "WARNING: empty DB and no demo.sqlite — seeding (slow)..."
  php artisan db:seed --force --no-interaction
else
  echo "Library ready (${PROMPT_COUNT} prompts)"
fi

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
