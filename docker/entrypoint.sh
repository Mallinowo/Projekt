#!/bin/sh
set -eu

APP_DIR="/var/www"
STORAGE_DIR="$APP_DIR/storage"
CACHE_DIR="$APP_DIR/bootstrap/cache"

mkdir -p \
  "$STORAGE_DIR/app/public" \
  "$STORAGE_DIR/framework/cache" \
  "$STORAGE_DIR/framework/sessions" \
  "$STORAGE_DIR/framework/views" \
  "$STORAGE_DIR/logs" \
  "$CACHE_DIR"

chown -R www-data:www-data "$STORAGE_DIR" "$CACHE_DIR"

find "$STORAGE_DIR" -type d -exec chmod 775 {} \;
find "$STORAGE_DIR" -type f -exec chmod 664 {} \;
find "$CACHE_DIR" -type d -exec chmod 775 {} \;
find "$CACHE_DIR" -type f -exec chmod 664 {} \;

exec "$@"
