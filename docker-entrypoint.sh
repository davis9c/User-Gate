#!/bin/bash
set -e

echo "[entrypoint] Running migrations..."
php spark migrate --force

echo "[entrypoint] Seeding database..."
php spark db:seed ApiPermissionSeeder

echo "[entrypoint] Starting Apache..."
exec apache2-foreground
