# The Things Corner

Website toko, manajemen stok, custom PO, dan rekonsiliasi Shopee untuk The Things Corner.

**Status:** Slice 1 selesai - auth dan role  
**Stack:** Laravel 13, PHP 8.4, PostgreSQL 16+, Blade, Tailwind CSS 4, Vite, Alpine.js  
**Repository:** https://github.com/FirdausSinau/thethingscorner.git

---

## Dokumentasi teknis

Semua dokumen yang berkaitan dengan kode dan produk ada di `docs/`:

| Dokumen | Isi |
|---|---|
| `docs/README.md` | Index dokumentasi teknis |
| `docs/PRD Fase 1 - The Things Corner.md` | Kebutuhan produk dan acceptance criteria |
| `docs/erd.md` | ERD dan data dictionary |
| `docs/technical-design-fase-1.md` | Arsitektur, route, integrasi, deployment |
| `docs/backend-overview.md` | Alur backend, service, transaksi, dan jalur belajar backend |
| `docs/decisions.md` | Decision log dan asumsi |
| `docs/design-system.md` | Token warna, tipografi, komponen |
| `docs/wireframes.md` | Wireframe pembeli dan admin |
| `docs/implementation-plan-fase-1.md` | Rencana per slice |
| `docs/data/` | Sample dataset dan template CSV |

## Dokumentasi kerja tim

Dokumen operasional, setup laptop, data request, dan file client ada di `project-management/`:

| Dokumen | Isi |
|---|---|
| `project-management/README.md` | Index dokumen kerja tim |
| `project-management/work-plan.md` | Domain, pembagian slice, urutan kerja |
| `project-management/team-workflow.md` | Branch, PR, aturan konflik |
| `project-management/setup-local.md` | Setup laptop dan troubleshooting |
| `project-management/data-request.md` | Checklist data untuk client |
| `project-management/client-docs/` | Dokumen client `.docx` (tidak masuk Git) |

---

## Kebutuhan environment

- PHP 8.3+ (disarankan 8.4) dengan `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`, `gd`
- Composer 2
- PostgreSQL 16+
- Node.js 22+ dan npm
- Git

Panduan lengkap per langkah, termasuk troubleshooting error 505, ada di
[`project-management/setup-local.md`](project-management/setup-local.md).

---

## Mulai cepat

Clone di luar OneDrive, lalu dari folder `Bisnis Sepatu`:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Buat database:

```powershell
psql -U postgres -c "CREATE DATABASE db_thethingscorner;"
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` PostgreSQL di `.env`, lalu:

```powershell
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Buka `http://127.0.0.1:8000`, bukan `https://` dan bukan folder `public/`.

Kalau muncul **505**, cek terminal `php artisan serve`, pastikan port yang dibuka sama, dan ikuti bagian 5 di `project-management/setup-local.md`.

---

## Provider

Fase 1 memakai fake adapter:

```dotenv
PAYMENT_DRIVER=fake
SHIPPING_DRIVER=fake
```

Isi key provider hanya di `.env`, jangan di Git:

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
BITESHIP_API_KEY=
BITESHIP_ORIGIN_AREA_ID=
WHATSAPP_ADMIN_NUMBER=
```

---

## Sample data

Data dummy ada di `docs/data/`. Nanti dimasukkan lewat seeder/factory.

Akun demo yang tersedia:

| Email | Role | Password |
|---|---|---|
| `admin@demo.local` | Admin Master | `rahasia123` |
| `adminbiasa@demo.local` | Admin Biasa | `rahasia123` |
| `budi@demo.local` | Pembeli | `rahasia123` |

Seeder sudah tersedia. Jalankan `php artisan migrate --seed` saat setup, atau
`php artisan db:seed --class=DemoUserSeeder` untuk mengembalikan akun demo.

---

## Troubleshooting

### Error 505 HTTP Version Not Supported

505 biasanya bukan error Laravel, tetapi masalah URL atau web server lokal:

1. Buka `http://127.0.0.1:8000`, bukan `https://`.
2. Jangan buka `http://127.0.0.1:8000/public`.
3. Lihat terminal `php artisan serve`. Kalau port pindah, buka port yang tertulis.
4. Matikan XAMPP/IIS/Docker yang memakai port 8000, atau jalankan:

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

5. Coba incognito dan matikan VPN/proxy yang memaksa HTTPS.

Panduan lengkap ada di [`project-management/setup-local.md`](project-management/setup-local.md) bagian 5.

### Windows / OneDrive

Jika muncul:

```text
bootstrap/cache directory must be present and writable
```

Jalankan:

```powershell
attrib -r "bootstrap\cache" /s /d
php artisan package:discover
```

Jika perubahan `.env` tidak terbaca:

```bash
php artisan config:clear
```

---

## Git

```bash
git remote -v
git status
```

Jangan commit:

- `.env`
- `vendor/`
- `node_modules/`
- `public/build/`
- file `.docx`/`.pdf` client
