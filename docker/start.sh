#!/bin/sh
set -eu

cd /app

mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  database

# Restore packaged library DB if the runtime DB is missing/empty.
if [ ! -s database/database.sqlite ] && [ -f database/demo.sqlite ]; then
  cp database/demo.sqlite database/database.sqlite
  echo "Restored packaged demo.sqlite"
fi
touch database/database.sqlite
chmod -R 777 storage bootstrap/cache database || true

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Must match render.yaml APP_KEY so build-time caches stay valid.
STABLE_KEY="base64:eOmCqltp/cpmpEdwAkynEMoP8OC9/PKrU7d8UPkvkxY="
php -r '
$key = getenv("APP_KEY") ?: "";
$ok = false;
if (str_starts_with($key, "base64:")) {
    $raw = base64_decode(substr($key, 7), true);
    $ok = is_string($raw) && strlen($raw) === 32;
}
exit($ok ? 0 : 1);
' || export APP_KEY="$STABLE_KEY"

# Keep .env aligned for artisan helpers.
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
file_put_contents($envPath, $env);
'

# Ensure demo rows exist (PDO — no Laravel boot).
if [ -f database/demo.sqlite ]; then
  COUNT="$(php -r '
    try {
      $db = new PDO("sqlite:database/database.sqlite");
      $n = (int) $db->query("SELECT COUNT(*) FROM sqlite_master WHERE type=\"table\" AND name=\"prompts\"")->fetchColumn();
      if ($n === 0) { echo 0; exit; }
      echo (int) $db->query("SELECT COUNT(*) FROM prompts")->fetchColumn();
    } catch (Throwable $e) { echo 0; }
  ')"
  if [ "${COUNT}" = "0" ]; then
    cp database/demo.sqlite database/database.sqlite
    echo "Re-copied demo.sqlite"
  fi
fi

# Skip migrate when schema already present (saves boot time on every wake).
NEEDS_MIGRATE="$(php -r '
  try {
    $db = new PDO("sqlite:database/database.sqlite");
    $n = (int) $db->query("SELECT COUNT(*) FROM sqlite_master WHERE type=\"table\" AND name=\"migrations\"")->fetchColumn();
    echo $n === 1 ? "0" : "1";
  } catch (Throwable $e) { echo "1"; }
')"
if [ "$NEEDS_MIGRATE" = "1" ]; then
  php artisan migrate --force --no-interaction --no-ansi >/tmp/migrate.log 2>&1 || cat /tmp/migrate.log
else
  echo "Schema present — skip migrate"
fi

# Refresh caches quickly if missing (image usually already has them).
if [ ! -f bootstrap/cache/config.php ]; then
  php artisan config:cache --no-interaction --no-ansi || true
fi
if [ ! -f bootstrap/cache/routes-v7.php ] && [ ! -f bootstrap/cache/routes.php ]; then
  php artisan route:cache --no-interaction --no-ansi || true
fi

echo "Listening on ${PORT:-8080}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
