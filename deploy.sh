#!/usr/bin/env bash
# Situmbuh deploy script — dijalankan di VPS via GitHub Actions (SSH pull).
# Idempoten: aman dijalankan ulang. Gagal di langkah mana pun = stop (set -e).
set -euo pipefail

APP_DIR="/var/www/situmbuh"
BRANCH="${DEPLOY_BRANCH:-main}"
SEED_CLASS="${SEED_CLASS:-ProductionSeeder}"
RUN_SEED="${RUN_SEED:-false}"

cd "$APP_DIR"

echo "==> [1/8] Git pull ($BRANCH)"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

echo "==> [2/8] Composer install (prod)"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "==> [3/8] NPM build"
if [ -f package-lock.json ]; then
  npm ci --no-audit --no-fund
else
  npm install --no-audit --no-fund
fi
npm run build

echo "==> [4/8] .env check"
if [ ! -f .env ]; then
  echo "!! .env tidak ada — salin dari .env.production.example lalu isi APP_KEY & DB_PASSWORD"
  cp .env.production.example .env
  php artisan key:generate --force
  echo "!! .env baru dibuat. Lengkapi DB_PASSWORD/ADMIN_* lalu jalankan ulang deploy."
  exit 1
fi

echo "==> [5/8] Laravel optimize"
php artisan storage:link || true
php artisan migrate --force
if [ "$RUN_SEED" = "true" ]; then
  echo "==> seed $SEED_CLASS"
  php artisan db:seed --class="$SEED_CLASS" --force
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [6/8] Permission"
chown -R www-data:www-data storage bootstrap/cache public/build || true
chmod -R 775 storage bootstrap/cache || true

echo "==> [7/8] Restart PHP-FPM"
systemctl reload php8.4-fpm || service php8.4-fpm reload || true

echo "==> [8/8] Health check"
php artisan about --only=environment || true
curl -s -o /dev/null -w "local HTTP %{http_code}\n" -H "Host: situmbuh.ridhoazkiaa.dev" http://127.0.0.1/ || true

echo "DEPLOY OK"
