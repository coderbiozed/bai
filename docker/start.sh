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

# Render "generateValue" APP_KEY is NOT a valid Laravel aes-256 key.
# Require "base64:" + exactly 32 raw bytes. Never boot Artisan to generate.
php -r '
$key = getenv("APP_KEY") ?: "";
$ok = false;
if (str_starts_with($key, "base64:")) {
    $raw = base64_decode(substr($key, 7), true);
    $ok = is_string($raw) && strlen($raw) === 32;
}
exit($ok ? 0 : 1);
' || {
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  echo "Installed valid Laravel APP_KEY (replaced invalid/missing key)"
}

# Keep .env in sync so config:cache and the app see the good key.
KEY="$APP_KEY" URL="${APP_URL:-}" php -r '
$envPath = ".env";
$env = file_exists($envPath) ? file_get_contents($envPath) : "";
$key = getenv("KEY") ?: "";
$url = getenv("URL") ?: "";
if ($key !== "") {
    if (preg_match("/^APP_KEY=.*/m", $env)) {
        $env = preg_replace("/^APP_KEY=.*/m", "APP_KEY=".$key, $env, 1);
    } else {
        $env .= "\nAPP_KEY=".$key."\n";
    }
}
if ($url !== "") {
    if (preg_match("/^APP_URL=.*/m", $env)) {
        $env = preg_replace("/^APP_URL=.*/m", "APP_URL=".$url, $env, 1);
    } else {
        $env .= "\nAPP_URL=".$url."\n";
    }
}
file_put_contents($envPath, $env);
'

# Drop stale cached config that may have baked a bad key.
rm -f bootstrap/cache/config.php \
      bootstrap/cache/routes-v7.php \
      bootstrap/cache/routes.php \
      bootstrap/cache/events.php

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
