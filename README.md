# SERDADU (Sistem Rekap Data Terpadu)

https://serdadu.nasruladitri.space/

SERDADU adalah aplikasi web berbasis Laravel yang dirancang untuk menyajikan data kependudukan dan wilayah secara terpadu, interaktif, dan aman. Aplikasi ini menyediakan visualisasi data melalui peta interaktif (Leaflet), grafik statistik, dan tabel ringkasan untuk memudahkan analisis demografi Kabupaten Madiun.

## 🚀 Teknologi Utama
- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Blade Templates, Tailwind CSS v4, Alpine.js v3
- **Database:** MySQL / MariaDB
- **Peta & Visualisasi:** Leaflet.js, Chart.js
- **Keamanan:** Role-Based Access Control (RBAC), Custom Middleware

## ✨ Fitur Utama

### 📊 Dashboard Publik
- **Statistik Kependudukan:** Kartu ringkasan untuk total populasi, gender, dan wajib KTP.
- **Peta Interaktif:** Visualisasi persebaran penduduk dengan filter wilayah (Kecamatan/Desa) dan berbagai layer peta (Default, Satellite, dll).
- **Grafik & Tabel:** Penyajian data yang dinamis dan mudah dipahami.
- **Ekspor Data:** Fitur unduh data dalam format PDF/Excel (menggunakan `dompdf` dan `maatwebsite/excel`).

### 🛡️ Panel Admin & Keamanan
- **Manajemen Data:** Impor data kependudukan (Excel) yang aman dan tervalidasi.
- **Otentikasi Aman:** Pendaftaran publik dinonaktifkan untuk mencegah akses tidak sah.
- **RBAC (Role-Based Access Control):** Hanya pengguna dengan status `is_admin` yang dapat mengakses fitur sensitif.
- **Middleware Proteksi:** Seluruh rute admin dilindungi oleh middleware khusus `admin`.
- **Mitigasi DoS:** Perlindungan terhadap serangan Denial of Service pada fitur unggahan file.

## 🛠️ Instalasi

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL

### Langkah-langkah
1. **Clone Repository**
   ```bash
   git clone https://github.com/username/serdadu.git
   cd serdadu
   ```

2. **Install Dependensi**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   Salin file contoh `.env` dan sesuaikan konfigurasi database Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   > **Penting:** Pastikan `APP_DEBUG=false` dan `SESSION_ENCRYPT=true` di production.

4. **Setup Database**
   Jalankan migrasi untuk membuat tabel database.
   ```bash
   php artisan migrate
   ```

5. **Build Assets**
   ```bash
   npm run build
   ```

## 🐳 Deployment dengan Docker

### Quick Start
Untuk deployment menggunakan Docker, ikuti langkah berikut:

1. **Copy Environment File**
   ```bash
   cp .env.docker .env
   ```

2. **Edit Konfigurasi**
   Sesuaikan file `.env` dengan konfigurasi Anda (password database, APP_URL, dll).

3. **Build dan Jalankan Container**
   ```bash
   docker compose build
   docker compose up -d
   ```

4. **Setup Laravel di Container**
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan config:cache
   docker compose exec app php artisan route:cache
   docker compose exec app php artisan view:cache
   ```

5. **Buat Admin User**
   ```bash
   docker compose exec app php artisan user:create-admin
   ```

### Atau Gunakan Script Deployment (Linux/Mac)
```bash
chmod +x deploy.sh
./deploy.sh
```

### Services yang Tersedia
| Service | Port | Deskripsi |
|---------|------|-----------|
| app | 8000 | Aplikasi Laravel |
| db | 3306 | MySQL Database |
| phpmyadmin | 8080 | Admin Database (dev only) |
| redis | 6379 | Cache/Queue (optional) |

### Akses Aplikasi
- **Aplikasi:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8080 (jalankan dengan `docker compose --profile dev up -d`)

### Perintah Docker yang Sering Digunakan
```bash
# Melihat logs
docker compose logs -f app

# Akses shell container
docker compose exec app bash

# Menjalankan artisan command
docker compose exec app php artisan [command]

# Stop containers
docker compose down

# Backup database
docker compose exec db mysqldump -u root -p serdadu > backup.sql
```

> 📖 **Dokumentasi Lengkap:** Lihat [DOCKER_README.md](DOCKER_README.md) untuk panduan Docker yang lebih detail.

## 🐳 Deployment via Portainer.io

SERDADU dapat dengan mudah di-deploy menggunakan Portainer.io untuk manajemen container yang lebih user-friendly.

### Quick Start di Portainer

1. **Buat Stack Baru** di Portainer dengan nama `serdadu`
2. **Pilih metode deployment**:
   - **Git Repository**: Arahkan ke repository ini
   - **Web Editor**: Copy-paste `docker-compose.yml`
3. **Set Environment Variables** (minimal):
   ```
   APP_KEY=                          # Generate setelah deploy
   DB_PASSWORD=YourStrongPassword!
   DB_ROOT_PASSWORD=YourRootPassword!
   APP_URL=http://your-domain.com
   ```
4. **Deploy Stack**
5. **Setup Laravel** via Console:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan user:create-admin
   ```

### Fitur Portainer

- ✅ **GUI Management**: Kelola container via web interface
- ✅ **Easy Updates**: Pull & redeploy dengan satu klik
- ✅ **Log Monitoring**: Lihat logs real-time
- ✅ **Resource Monitoring**: Monitor CPU, memory, network
- ✅ **Environment Variables**: Kelola env vars tanpa edit file

> 📖 **Panduan Lengkap**: Lihat [PORTAINER_DEPLOYMENT.md](PORTAINER_DEPLOYMENT.md) untuk tutorial step-by-step, troubleshooting, dan best practices.

## 👥 Manajemen User Admin
Karena pendaftaran publik dinonaktifkan demi keamanan, gunakan perintah CLI berikut untuk membuat akun administrator:

```bash
php artisan user:create-admin
```
Ikuti instruksi di layar untuk memasukkan Nama, Email, dan Password.

## 🔒 Keamanan
Proyek ini telah melalui audit keamanan dan menerapkan praktik terbaik:
- **Input Validation:** Validasi ketat pada setiap input pengguna.
- **Secure File Upload:** Pengecekan MIME type dan pembatasan ukuran file.
- **XSS & CSRF Protection:** Perlindungan bawaan Laravel diaktifkan sepenuhnya.
- **No Debug Routes:** Rute debug berbahaya telah dihapus dari kode produksi.

## 📄 Lisensi
[MIT License](LICENSE)
