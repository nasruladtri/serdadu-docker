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
