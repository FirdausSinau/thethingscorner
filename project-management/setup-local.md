# Setup Lokal Tim - The Things Corner

**Versi:** 1.1  
**Tanggal:** 9 November 2026  
**Untuk:** Firdaus, Anggota A, Anggota B

Panduan ini dipakai saat pertama kali menyiapkan project di laptop dan saat mengambil update terbaru dari `main`.

---

## 1. Cek environment

Jalankan di PowerShell:

```powershell
php -v
composer -V
node -v
npm -v
psql --version
git --version
```

Yang dibutuhkan:

| Tool | Versi minimal |
|---|---|
| PHP | 8.3 (disarankan 8.4) |
| Composer | 2 |
| Node.js | 22 |
| PostgreSQL | 16 |
| Git | terbaru |

Ekstensi PHP yang wajib aktif: `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`, `gd`.

Cek ekstensi:

```powershell
php -m | findstr /i "pdo_pgsql mbstring openssl fileinfo gd"
```

Kalau `pdo_pgsql` tidak muncul, aktifkan di `php.ini` lalu restart terminal.

---

## 2. Setup pertama kali

### 2.1 Clone di luar OneDrive

Clone ke folder biasa, misalnya `C:\Projects`. Jangan di dalam OneDrive, karena sync bisa mengunci file `vendor`, `node_modules`, dan `bootstrap/cache`.

```powershell
mkdir C:\Projects -Force
cd C:\Projects
git clone https://github.com/FirdausSinau/thethingscorner.git
cd "thethingscorner\Bisnis Sepatu"
```

### 2.2 Install dependency

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Cek `.env`:

```dotenv
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_thethingscorner
DB_USERNAME=postgres
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan PostgreSQL di laptop masing-masing. `DB_PASSWORD` adalah password yang dibuat saat instalasi PostgreSQL.

### 2.3 Buat database

Dari terminal:

```powershell
psql -U postgres -c "CREATE DATABASE db_thethingscorner;"
```

Kalau `psql` tidak dikenali, buka **SQL Shell (psql)** dari Start Menu, atau tambahkan folder `C:\Program Files\PostgreSQL\<versi>\bin` ke PATH. Bisa juga membuat database lewat pgAdmin.

### 2.4 Migrasi dan data demo

```powershell
php artisan migrate --seed
php artisan storage:link
```

Perintah ini membuat tabel, akun demo, dan folder `public/storage`.

### 2.5 Build asset dan jalankan

```powershell
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Buka URL yang muncul di terminal. Biasanya:

```text
http://127.0.0.1:8000
```

Jangan buka `https://`, jangan buka folder `public/`, dan jangan buka port Vite (`5173`) untuk halaman Laravel.

---

## 3. Akun demo

Semua password: `rahasia123`

| Email | Role |
|---|---|
| `admin@demo.local` | Admin Master |
| `adminbiasa@demo.local` | Admin Biasa |
| `budi@demo.local` | Pembeli |

Kalau akun demo hilang:

```powershell
php artisan db:seed --class=DemoUserSeeder
```

---

## 4. Kerja harian setelah pull

```powershell
git checkout main
git pull origin main
composer install
php artisan migrate
npm install
npm run build
php artisan optimize:clear
php artisan serve
```

`composer install` dan `npm install` boleh dijalankan setiap kali pull. Kalau lock file tidak berubah, prosesnya cepat.

Kalau sedang mengembangkan frontend dan ingin hot reload, pakai dua terminal:

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
npm run dev
```

Jangan jalankan `npm run dev` lalu membuka `http://localhost:5173` sebagai halaman utama. Halaman Laravel tetap di `http://127.0.0.1:8000`.

---

## 5. Khusus error 505

505 biasanya berasal dari web server atau URL, bukan dari kode Laravel. Cek berurutan:

1. Pastikan URL memakai `http://`, bukan `https://`.
2. Pastikan membuka `http://127.0.0.1:8000`, bukan `http://127.0.0.1:8000/public`.
3. Baca terminal `php artisan serve`. Kalau port 8000 sudah dipakai, Laravel kadang pindah ke 8001. Buka port yang benar-benar tertulis.
4. Matikan server lain yang memakai port 8000 (XAMPP Apache, IIS, Docker, aplikasi lain).
5. Coba port lain:

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

6. Coba browser incognito, matikan VPN/proxy/ekstensi yang memaksa HTTPS.
7. Tes server langsung tanpa artisan:

```powershell
php -S 127.0.0.1:8000 -t public
```

8. Cek port yang dipakai:

```powershell
netstat -ano | findstr :8000
```

Kalau ada PID lain, tutup aplikasi tersebut. Jangan asal `taskkill` proses sistem.

Kalau halamannya justru menampilkan 500, bukan 505, lihat bagian berikut.

---

## 6. Error umum lain

| Gejala | Penyebab umum | Perbaikan |
|---|---|---|
| 500 setelah clone | `.env` belum ada atau `APP_KEY` kosong | `Copy-Item .env.example .env` lalu `php artisan key:generate` |
| 500 `Vite manifest not found` | Asset belum dibuild | `npm install` lalu `npm run build` |
| 500 `Unknown database` | Database belum dibuat atau nama di `.env` salah | Buat `db_thethingscorner`, cek `DB_DATABASE` |
| 500 `password authentication failed` | User/password PostgreSQL salah | Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` |
| 500 `could not find driver` | `pdo_pgsql` belum aktif | Aktifkan di `php.ini`, restart terminal |
| 419 Page Expired | Session/database belum siap | `php artisan migrate`, `php artisan optimize:clear` |
| 403 saat buka admin | Login sebagai role yang salah | Pakai akun admin yang sesuai |
| `bootstrap/cache must be writable` | Folder terkunci OneDrive | Pindahkan project keluar OneDrive atau `attrib -r "bootstrap\cache" /s /d` |
| Perubahan `.env` tidak terbaca | Config lama masih di-cache | `php artisan config:clear` |

Cek error asli:

```powershell
Get-Content storage\logs\laravel.log -Tail 50
```

---

## 7. Kalau masih gagal

Kirim ke grup tim:

1. Screenshot halaman error.
2. Output lengkap terminal `php artisan serve`.
3. URL yang dibuka.
4. Output `php -v`, `composer -V`, `node -v`.
5. 50 baris terakhir `storage/logs/laravel.log` jika ada.
6. Output `php -m | findstr /i "pdo_pgsql"`.

Jangan kirim isi `.env` lengkap karena berisi password dan key.

---

## 8. Branch kerja tim

Branch pribadi dibuat sekali:

```powershell
git checkout main
git pull origin main
git checkout -b anggota-a
git push -u origin anggota-a
```

Ganti `anggota-a` dengan nama branch masing-masing. Setelah itu kerja di branch sendiri, lalu buka Pull Request ke `main`.

Sebelum mulai kerja:

```powershell
git checkout main
git pull origin main
git checkout anggota-a
git merge main
```

Aturan lengkap ada di [`team-workflow.md`](team-workflow.md) dan urutan kerja di [`work-plan.md`](work-plan.md).
