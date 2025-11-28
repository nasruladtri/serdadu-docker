# 🌐 Panduan Setup Domain untuk SERDADU Docker

Panduan lengkap untuk mengkonfigurasi domain `nasruladitri.space` dengan aplikasi SERDADU yang berjalan di Docker.

## 📋 Informasi Setup

- **Domain**: nasruladitri.space
- **Subdomain Aplikasi**: serdadu.nasruladitri.space
- **Subdomain phpMyAdmin**: db.nasruladitri.space
- **IP Server**: 45.130.164.251
- **Reverse Proxy**: Nginx Proxy Manager

---

## 🚀 Langkah 1: Konfigurasi DNS di Cloudflare

### 1.1 Login ke Cloudflare

1. Buka [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Login dengan akun Anda
3. Pilih domain **nasruladitri.space**

### 1.2 Tambahkan DNS Records

Klik menu **DNS** di sidebar, lalu tambahkan 2 A records berikut:

#### Record 1: Aplikasi SERDADU
- **Type**: A
- **Name**: `serdadu`
- **IPv4 address**: `45.130.164.251`
- **Proxy status**: 🔴 **DNS only** (grey cloud) - PENTING!
- **TTL**: Auto

#### Record 2: phpMyAdmin
- **Type**: A
- **Name**: `db`
- **IPv4 address**: `45.130.164.251`
- **Proxy status**: 🔴 **DNS only** (grey cloud) - PENTING!
- **TTL**: Auto

> [!IMPORTANT]
> **Mengapa DNS only (grey cloud)?**
> - Nginx Proxy Manager akan menghandle SSL certificate sendiri
> - Jika menggunakan Cloudflare proxy (orange cloud), akan terjadi konflik SSL
> - Setelah SSL berhasil setup, Anda bisa mengaktifkan Cloudflare proxy jika diperlukan

### 1.3 Verifikasi DNS Propagation

Tunggu beberapa menit (biasanya 1-5 menit), lalu cek dengan command:

```bash
nslookup serdadu.nasruladitri.space
nslookup db.nasruladitri.space
```

Atau gunakan online tool: [DNS Checker](https://dnschecker.org/)

**Expected result**: Kedua subdomain harus menunjuk ke IP `45.130.164.251`

---

## 🐳 Langkah 2: Deploy Docker Stack

### 2.1 Pastikan File Sudah Diupdate

File-file berikut sudah diupdate dengan konfigurasi domain:
- ✅ `docker-compose.yml` - Nginx Proxy Manager sudah ditambahkan
- ✅ `.env.docker` - APP_URL dan SESSION_DOMAIN sudah disesuaikan

### 2.2 Deploy via Portainer

#### Opsi A: Update Stack yang Sudah Ada

Jika stack `serdadu` sudah ada di Portainer:

1. Login ke Portainer: `http://45.130.164.251:9000`
2. Pilih **Stacks** di sidebar
3. Klik stack **serdadu**
4. Klik tombol **Editor**
5. Copy-paste isi file `docker-compose.yml` yang baru
6. Scroll ke bawah
7. Centang **Re-pull image and redeploy**
8. Klik **Update the stack**

#### Opsi B: Deploy Stack Baru

Jika belum ada stack:

1. Login ke Portainer
2. Klik **Stacks** → **+ Add stack**
3. Nama stack: `serdadu`
4. Pilih **Web editor**
5. Copy-paste isi file `docker-compose.yml`
6. Scroll ke **Environment variables**
7. Copy-paste environment variables dari `.env.docker`
8. Klik **Deploy the stack**

### 2.3 Verifikasi Container Running

Setelah deploy, pastikan semua container berjalan:

1. Buka **Containers** di Portainer
2. Pastikan status semua container **running** (hijau):
   - ✅ `serdadu_nginx_proxy`
   - ✅ `serdadu_app`
   - ✅ `serdadu_db`
   - ✅ `serdadu_phpmyadmin`

> [!TIP]
> Jika ada container yang error, klik container tersebut → **Logs** untuk melihat error message

---

## 🔧 Langkah 3: Setup Nginx Proxy Manager

### 3.1 Akses Nginx Proxy Manager UI

1. Buka browser
2. Akses: `http://45.130.164.251:81`
3. Login dengan credentials default:
   - **Email**: `admin@example.com`
   - **Password**: `changeme`

### 3.2 Change Default Password

> [!CAUTION]
> **WAJIB** ganti password default untuk keamanan!

1. Setelah login, akan muncul popup untuk change password
2. Isi:
   - **Email**: Ganti dengan email Anda
   - **Password**: Buat password yang kuat
3. Klik **Save**

### 3.3 Tambahkan Proxy Host untuk Aplikasi SERDADU

1. Klik menu **Hosts** → **Proxy Hosts**
2. Klik tombol **Add Proxy Host**
3. Tab **Details**:
   - **Domain Names**: `serdadu.nasruladitri.space`
   - **Scheme**: `http`
   - **Forward Hostname / IP**: `serdadu_app` (nama container)
   - **Forward Port**: `80`
   - ✅ Centang **Block Common Exploits**
   - ✅ Centang **Websockets Support** (untuk fitur realtime jika ada)

4. Tab **SSL**:
   - ✅ Centang **Request a new SSL Certificate**
   - ✅ Centang **Force SSL**
   - ✅ Centang **HTTP/2 Support**
   - ✅ Centang **HSTS Enabled**
   - **Email Address for Let's Encrypt**: Masukkan email Anda
   - ✅ Centang **I Agree to the Let's Encrypt Terms of Service**

5. Klik **Save**

> [!NOTE]
> Proses request SSL certificate memakan waktu 10-30 detik. Tunggu hingga selesai.

### 3.4 Tambahkan Proxy Host untuk phpMyAdmin

1. Klik **Add Proxy Host** lagi
2. Tab **Details**:
   - **Domain Names**: `db.nasruladitri.space`
   - **Scheme**: `http`
   - **Forward Hostname / IP**: `serdadu_phpmyadmin`
   - **Forward Port**: `80`
   - ✅ Centang **Block Common Exploits**

3. Tab **SSL**:
   - ✅ Centang **Request a new SSL Certificate**
   - ✅ Centang **Force SSL**
   - ✅ Centang **HTTP/2 Support**
   - **Email Address for Let's Encrypt**: Email Anda
   - ✅ Centang **I Agree to the Let's Encrypt Terms of Service**

4. Klik **Save**

### 3.5 Verifikasi Proxy Hosts

Setelah selesai, Anda akan melihat 2 proxy hosts dengan status:
- ✅ `serdadu.nasruladitri.space` - SSL: Active
- ✅ `db.nasruladitri.space` - SSL: Active

---

## ✅ Langkah 4: Testing dan Verifikasi

### 4.1 Test Akses Aplikasi SERDADU

1. Buka browser baru (atau incognito mode)
2. Akses: `https://serdadu.nasruladitri.space`
3. **Expected result**:
   - ✅ Aplikasi SERDADU muncul
   - ✅ Browser menunjukkan 🔒 (SSL valid)
   - ✅ Certificate dari Let's Encrypt

### 4.2 Test Akses phpMyAdmin

1. Akses: `https://db.nasruladitri.space`
2. **Expected result**:
   - ✅ phpMyAdmin login page muncul
   - ✅ SSL valid
3. Login dengan:
   - **Username**: `root`
   - **Password**: Sesuai `DB_ROOT_PASSWORD` di environment variables

### 4.3 Test HTTP to HTTPS Redirect

1. Akses: `http://serdadu.nasruladitri.space` (tanpa 's')
2. **Expected result**:
   - ✅ Otomatis redirect ke `https://serdadu.nasruladitri.space`

### 4.4 Test SSL Certificate

Gunakan online tool untuk verify SSL:
- [SSL Labs](https://www.ssllabs.com/ssltest/)
- Masukkan: `serdadu.nasruladitri.space`
- **Expected grade**: A atau A+

---

## 🔐 Langkah 5: Keamanan Tambahan (Opsional)

### 5.1 Batasi Akses Nginx Proxy Manager UI

Nginx Proxy Manager UI (port 81) sebaiknya tidak diakses dari internet.

**Opsi 1: Firewall** (Recommended)
```bash
# Hanya izinkan akses port 81 dari IP tertentu
sudo ufw allow from YOUR_IP_ADDRESS to any port 81
```

**Opsi 2: Change Port**
Edit `docker-compose.yml`:
```yaml
ports:
  - "8181:81"  # Ganti port 81 ke port lain
```

### 5.2 Batasi Akses phpMyAdmin

Untuk production, sebaiknya phpMyAdmin tidak diakses public.

**Opsi 1**: Hapus proxy host `db.nasruladitri.space` di Nginx Proxy Manager

**Opsi 2**: Tambahkan Access List di Nginx Proxy Manager:
1. Buka **Access Lists** → **Add Access List**
2. Nama: `phpMyAdmin Whitelist`
3. Tab **Authorization**:
   - ✅ Centang **Satisfy Any**
4. Tab **Access**:
   - Tambahkan IP address yang diizinkan
5. Klik **Save**
6. Edit proxy host `db.nasruladitri.space`
7. Tab **Details** → **Access List**: Pilih `phpMyAdmin Whitelist`

---

## 🆘 Troubleshooting

### Error: "502 Bad Gateway"

**Penyebab**: Container aplikasi tidak bisa diakses oleh Nginx Proxy Manager

**Solusi**:
1. Pastikan container `serdadu_app` running:
   ```bash
   docker ps | grep serdadu_app
   ```
2. Periksa logs:
   ```bash
   docker logs serdadu_app
   ```
3. Pastikan Forward Hostname di Nginx Proxy Manager adalah `serdadu_app` (nama container), bukan `localhost`

### Error: "SSL Certificate Failed"

**Penyebab**: Let's Encrypt tidak bisa verify domain

**Solusi**:
1. Pastikan DNS sudah propagate (cek dengan `nslookup`)
2. Pastikan Cloudflare proxy **DISABLED** (grey cloud)
3. Pastikan port 80 dan 443 terbuka di firewall:
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   ```
4. Coba request SSL certificate lagi di Nginx Proxy Manager

### Error: "Connection Timeout"

**Penyebab**: Firewall memblokir port 80/443

**Solusi**:
```bash
# Buka port 80 dan 443
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw reload
```

### Error: "Database Connection Failed"

**Penyebab**: Container database belum siap atau environment variables salah

**Solusi**:
1. Pastikan container `serdadu_db` running
2. Tunggu 10-20 detik untuk database initialization
3. Restart container `serdadu_app`:
   ```bash
   docker restart serdadu_app
   ```

### Aplikasi Muncul tapi Styling Rusak

**Penyebab**: APP_URL tidak sesuai dengan domain yang diakses

**Solusi**:
1. Pastikan environment variable `APP_URL=https://serdadu.nasruladitri.space`
2. Restart container:
   ```bash
   docker restart serdadu_app
   ```
3. Clear cache Laravel:
   ```bash
   docker exec serdadu_app php artisan config:clear
   docker exec serdadu_app php artisan cache:clear
   ```

---

## 📊 Monitoring

### Melihat Logs Nginx Proxy Manager

```bash
docker logs serdadu_nginx_proxy
```

### Melihat Logs Aplikasi

```bash
docker logs serdadu_app
```

### Melihat SSL Certificate Info

Di Nginx Proxy Manager UI:
1. Klik menu **SSL Certificates**
2. Lihat expiry date (biasanya 90 hari)
3. Certificate akan auto-renew 30 hari sebelum expired

---

## 🎉 Selesai!

Aplikasi SERDADU Anda sekarang sudah bisa diakses via:
- 🌐 **Aplikasi**: https://serdadu.nasruladitri.space
- 🗄️ **phpMyAdmin**: https://db.nasruladitri.space
- ⚙️ **Nginx Proxy Manager**: http://45.130.164.251:81

> [!TIP]
> Simpan credentials Nginx Proxy Manager di tempat yang aman!

## 📚 Referensi

- [Nginx Proxy Manager Documentation](https://nginxproxymanager.com/guide/)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Cloudflare DNS Documentation](https://developers.cloudflare.com/dns/)
