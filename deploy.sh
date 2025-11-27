#!/bin/bash

# SERDADU Docker Deployment Script
# Quick deployment script untuk production

set -e

echo "🐳 SERDADU Docker Deployment"
echo "=============================="

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  File .env tidak ditemukan!"
    echo "📋 Copying .env.docker to .env..."
    cp .env.docker .env
    echo "✅ Silakan edit file .env dan sesuaikan konfigurasi!"
    echo "   Terutama: APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD, APP_URL"
    exit 1
fi

# Check if APP_KEY is set
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=\"\"" .env; then
    echo "⚠️  APP_KEY belum di-set!"
    echo "🔑 Generating APP_KEY..."
    docker compose run --rm app php artisan key:generate
fi

echo "🏗️  Building Docker images..."
docker compose build

echo "🚀 Starting containers..."
docker compose up -d

echo "⏳ Waiting for database to be ready..."
sleep 10

echo "📦 Running migrations..."
docker compose exec app php artisan migrate --force

echo "⚡ Optimizing application..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

echo "🔐 Setting permissions..."
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache

echo ""
echo "✅ Deployment selesai!"
echo ""
echo "📊 Status containers:"
docker compose ps
echo ""
echo "🌐 Aplikasi dapat diakses di:"
echo "   http://localhost:8000"
echo ""
echo "👤 Jangan lupa membuat admin user:"
echo "   docker compose exec app php artisan user:create-admin"
echo ""
