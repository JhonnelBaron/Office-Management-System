#!/bin/bash
set -e

echo "🚀 Deployment started..."

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize

echo "✅ Deployment finished successfully!"