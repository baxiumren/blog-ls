#!/bin/bash
set -e

# ============ EDIT INI ============
APP_DIR="/var/www/livescore"
PHP_VER="8.3"
PORT="80"          # 80 = akses via http://IP + Cloudflare domain jalan
# ==================================

echo "==> [1/6] Install paket sistem..."
apt update -y
apt install -y php${PHP_VER} php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-sqlite3 \
  php${PHP_VER}-mbstring php${PHP_VER}-curl php${PHP_VER}-xml php${PHP_VER}-zip \
  unzip nginx git curl

if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi
if [ ! -d "${APP_DIR}/public/build" ] && ! command -v node &>/dev/null; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt install -y nodejs
fi

cd "$APP_DIR"

echo "==> [2/6] Dependency aplikasi..."
[ -d vendor ] || composer install --no-dev --optimize-autoloader
[ -d public/build ] || { npm install && npm run build; }

echo "==> [3/6] Siapin file..."
[ -f .env ] || cp .env.example .env
if [ ! -f database/database.sqlite ]; then
  [ -f database/starter.sqlite ] && cp database/starter.sqlite database/database.sqlite || touch database/database.sqlite
fi

echo "==> [4/6] Permission..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 storage bootstrap/cache database
chmod 664 .env

echo "==> [5/6] Nginx (catch-all, listen port ${PORT})..."
cat > /etc/nginx/sites-available/livescore <<NGINX
server {
    listen ${PORT} default_server;
    server_name _;
    root ${APP_DIR}/public;
    index index.php;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/livescore /etc/nginx/sites-enabled/livescore
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo "==> [6/6] Cron (live score auto-update)..."
( crontab -l 2>/dev/null | grep -v "schedule:run"; \
  echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" ) | crontab -

# Deteksi IP publik
IP=$(curl -s --max-time 5 ifconfig.me || hostname -I | awk '{print $1}')
URL="http://${IP}"
[ "$PORT" != "80" ] && URL="http://${IP}:${PORT}"

echo ""
echo "=================================================="
echo "  SELESAI! Buka di browser:"
echo "  ${URL}/install"
echo "=================================================="
echo "  Abis install + login → Admin → Domains → add domain"
echo "=================================================="