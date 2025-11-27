# SERDADU Docker Deployment - Quick Reference

## 🚀 Quick Start Commands

### First Time Setup
```bash
# 1. Copy environment file
cp .env.docker .env

# 2. Edit .env (set passwords and APP_URL)
nano .env

# 3. Build and start
docker compose build
docker compose up -d

# 4. Setup Laravel
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 5. Create admin user
docker compose exec app php artisan user:create-admin
```

### Or Use Deployment Script (Linux/Mac)
```bash
chmod +x deploy.sh
./deploy.sh
```

## 📦 Services

| Service | Port | Description |
|---------|------|-------------|
| app | 8000 | Laravel Application |
| db | 3306 | MySQL Database |
| phpmyadmin | 8080 | Database Admin (dev only) |
| redis | 6379 | Cache/Queue (optional) |

## 🔧 Common Commands

```bash
# Start/Stop
docker compose up -d
docker compose down

# Logs
docker compose logs -f app

# Shell access
docker compose exec app bash

# Artisan commands
docker compose exec app php artisan [command]

# Database backup
docker compose exec db mysqldump -u root -p serdadu > backup.sql
```

## 🌐 Access URLs

- **App:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8080 (with `--profile dev`)

## 📝 Important Files

- `Dockerfile` - Application image
- `docker-compose.yml` - Services orchestration
- `.env` - Environment configuration
- `docker/nginx/default.conf` - Nginx config
- `docker/php/php.ini` - PHP settings

## 🆘 Troubleshooting

```bash
# Permission issues
docker compose exec app chown -R www-data:www-data storage bootstrap/cache

# Clear cache
docker compose exec app php artisan optimize:clear

# Rebuild
docker compose down
docker compose build --no-cache
docker compose up -d
```

For detailed documentation, see [DOCKER_DEPLOYMENT.md](DOCKER_DEPLOYMENT.md)
