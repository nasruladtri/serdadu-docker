# 🚢 Panduan Deployment SERDADU di Portainer.io

Panduan lengkap untuk men-deploy aplikasi SERDADU menggunakan Portainer.io.

## 📋 Prasyarat

- Server dengan Docker dan Docker Compose terinstall
- Portainer.io sudah terinstall dan berjalan
- Akses ke Portainer UI
- Repository code SERDADU (bisa dari Git atau upload manual)

## 🚀 Langkah-Langkah Deployment

### 1. Persiapan Repository

#### Opsi A: Menggunakan Git Repository
Jika code Anda sudah di Git repository (GitHub, GitLab, dll):
- Pastikan repository bisa diakses (public atau dengan credentials)
- Catat URL repository

#### Opsi B: Upload Manual
Jika menggunakan upload manual:
- Zip seluruh folder project
- Siapkan untuk upload ke server

### 2. Login ke Portainer

1. Buka Portainer UI di browser: `http://your-server-ip:9000`
2. Login dengan credentials Anda
3. Pilih environment yang akan digunakan (local atau remote Docker)

### 3. Membuat Stack Baru

1. Di sidebar, klik **Stacks**
2. Klik tombol **+ Add stack**
3. Berikan nama stack: `serdadu`

### 4. Konfigurasi Stack

#### Opsi A: Menggunakan Git Repository

1. Pilih **Git Repository**
2. Isi konfigurasi:
   - **Repository URL**: URL Git repository Anda
   - **Repository reference**: `refs/heads/main` (atau branch yang diinginkan)
   - **Compose path**: `docker-compose.yml`
   - **Authentication**: Isi jika repository private

#### Opsi B: Menggunakan Web Editor

1. Pilih **Web editor**
2. Copy-paste isi file `docker-compose.yml` ke editor

### 5. Konfigurasi Environment Variables

Scroll ke bawah ke bagian **Environment variables**. Tambahkan variabel berikut (sesuaikan dengan kebutuhan):

#### Variabel Wajib:
```
APP_KEY=                          # Generate nanti setelah deploy
DB_PASSWORD=YourStrongPassword123!
DB_ROOT_PASSWORD=YourRootPassword123!
APP_URL=http://your-domain.com    # Sesuaikan dengan domain/IP Anda
```

#### Variabel Opsional (dengan default values):
```
APP_NAME=SERDADU
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
DB_DATABASE=serdadu
DB_USERNAME=serdadu_user
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
CACHE_STORE=database
LOG_CHANNEL=stack
LOG_LEVEL=error
APP_PORT=8000
DB_PORT=3306
PMA_PORT=8080
REDIS_PORT=6379
```

> 💡 **Tip**: Anda bisa copy dari file `.env.portainer` dan sesuaikan nilainya

### 6. Deploy Stack

1. Scroll ke bawah
2. Klik tombol **Deploy the stack**
3. Tunggu hingga proses deployment selesai
4. Periksa status container di tab **Containers**

### 7. Setup Laravel Application

Setelah stack berhasil di-deploy, jalankan perintah setup Laravel:

#### Generate Application Key:
1. Klik pada container **serdadu_app**
2. Pilih tab **Console**
3. Klik **Connect** dengan shell `/bin/bash`
4. Jalankan:
```bash
php artisan key:generate
```
5. Copy APP_KEY yang dihasilkan
6. Update environment variable `APP_KEY` di Stack

#### Jalankan Migrasi Database:
Di console yang sama, jalankan:
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Buat Admin User:
```bash
php artisan user:create-admin
```
Ikuti instruksi untuk membuat user admin.

### 8. Set Permissions (Jika Diperlukan)

Jika ada masalah permission, jalankan:
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

### 9. Akses Aplikasi

Buka browser dan akses:
- **Aplikasi**: `http://your-server-ip:8000` (atau sesuai APP_PORT)
- **phpMyAdmin** (jika diaktifkan): `http://your-server-ip:8080`

## 🔧 Konfigurasi Tambahan

### Mengaktifkan phpMyAdmin (Development)

Untuk mengaktifkan phpMyAdmin:

1. Edit Stack
2. Di bagian bawah, tambahkan environment variable:
   ```
   COMPOSE_PROFILES=dev
   ```
3. Update stack
4. phpMyAdmin akan tersedia di port 8080

### Mengaktifkan Redis (Caching)

Untuk mengaktifkan Redis:

1. Edit Stack
2. Tambahkan environment variable:
   ```
   COMPOSE_PROFILES=cache
   ```
3. Update Laravel `.env` untuk menggunakan Redis:
   ```
   CACHE_STORE=redis
   REDIS_HOST=redis
   REDIS_PORT=6379
   ```
4. Update stack

### Menggunakan Custom Domain

1. Setup reverse proxy (Nginx/Traefik) di server
2. Arahkan domain ke container serdadu_app
3. Update environment variable `APP_URL` dengan domain Anda
4. Restart stack

## 📊 Monitoring & Maintenance

### Melihat Logs

1. Buka Stack **serdadu**
2. Klik container yang ingin dilihat lognya
3. Pilih tab **Logs**
4. Pilih jumlah baris yang ingin ditampilkan

### Restart Container

1. Buka Stack **serdadu**
2. Klik container yang ingin direstart
3. Klik tombol **Restart**

### Update Application

#### Jika menggunakan Git:
1. Buka Stack **serdadu**
2. Klik **Pull and redeploy**
3. Portainer akan pull code terbaru dan redeploy

#### Jika manual:
1. Upload code baru ke server
2. Rebuild container:
   - Klik Stack **serdadu**
   - Scroll ke bawah
   - Centang **Re-pull image and redeploy**
   - Klik **Update the stack**

### Backup Database

#### Melalui Console:
1. Buka container **serdadu_db**
2. Pilih tab **Console** dan connect
3. Jalankan:
```bash
mysqldump -u root -p serdadu > /var/lib/mysql/backup_$(date +%Y%m%d).sql
```

#### Melalui phpMyAdmin:
1. Akses phpMyAdmin
2. Pilih database **serdadu**
3. Klik tab **Export**
4. Klik **Go**

## 🆘 Troubleshooting

### Container Tidak Bisa Start

**Gejala**: Container status "Exited" atau "Error"

**Solusi**:
1. Periksa logs container
2. Pastikan semua environment variables sudah diisi dengan benar
3. Pastikan port tidak bentrok dengan aplikasi lain
4. Periksa permission folder storage dan bootstrap/cache

### Database Connection Error

**Gejala**: Error "SQLSTATE[HY000] [2002] Connection refused"

**Solusi**:
1. Pastikan container **serdadu_db** sudah running
2. Tunggu beberapa detik untuk database initialization
3. Periksa environment variables database (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
4. Restart container **serdadu_app**

### Permission Denied Error

**Gejala**: Error "Permission denied" saat akses storage

**Solusi**:
1. Masuk ke console container **serdadu_app**
2. Jalankan:
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

### APP_KEY Not Set

**Gejala**: Error "No application encryption key has been specified"

**Solusi**:
1. Generate APP_KEY:
```bash
docker compose exec app php artisan key:generate --show
```
2. Copy key yang dihasilkan
3. Update environment variable `APP_KEY` di Stack
4. Update stack

### Port Already in Use

**Gejala**: Error "port is already allocated"

**Solusi**:
1. Edit Stack
2. Ubah environment variables untuk port yang bentrok:
   - `APP_PORT` (default: 8000)
   - `DB_PORT` (default: 3306)
   - `PMA_PORT` (default: 8080)
3. Update stack

## 🔒 Keamanan Production

### Checklist Keamanan:

- [ ] `APP_DEBUG=false`
- [ ] `SESSION_ENCRYPT=true`
- [ ] Password database yang kuat (min. 16 karakter)
- [ ] APP_KEY sudah di-generate
- [ ] Gunakan HTTPS dengan SSL certificate
- [ ] Batasi akses phpMyAdmin (gunakan hanya saat development)
- [ ] Backup database secara berkala
- [ ] Update Docker images secara berkala
- [ ] Monitor logs untuk aktivitas mencurigakan
- [ ] Gunakan firewall untuk membatasi akses port

## 📚 Referensi

- [Dokumentasi Docker](DOCKER_README.md)
- [Dokumentasi Portainer](https://docs.portainer.io/)
- [Dokumentasi Laravel](https://laravel.com/docs)

## 💬 Bantuan

Jika mengalami masalah yang tidak tercantum di sini, silakan:
1. Periksa logs container untuk detail error
2. Periksa dokumentasi Laravel untuk error spesifik
3. Hubungi tim development
