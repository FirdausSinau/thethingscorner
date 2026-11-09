# The Things Corner

Website toko, manajemen stok, custom PO, dan rekonsiliasi Shopee untuk The Things Corner.

**Status:** Slice 0 — setup project  
**Stack:** Laravel 13, PHP 8.4, MySQL 8, Blade, Tailwind CSS 4, Vite, Alpine.js  
**Repository:** https://github.com/FirdausSinau/thethingscorner.git

---

## Dokumentasi

Semua dokumen perencanaan ada di `docs/`:

| Dokumen | Isi |
|---|---|
| `docs/README.md` | Index dokumentasi |
| `docs/PRD Fase 1 - The Things Corner.md` | Kebutuhan produk dan acceptance criteria |
| `docs/erd.md` | ERD dan data dictionary |
| `docs/technical-design-fase-1.md` | Arsitektur, route, integrasi, deployment |
| `docs/decisions.md` | Decision log dan asumsi |
| `docs/design-system.md` | Token warna, tipografi, komponen |
| `docs/wireframes.md` | Wireframe pembeli dan admin |
| `docs/implementation-plan-fase-1.md` | Rencana per slice |
| `docs/data/` | Sample dataset dan template CSV |

---

## Kebutuhan environment

- PHP 8.4+
- Composer 2
- MySQL 8 / MariaDB
- Node.js 22+ dan npm
- Git

---

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Buat database:

```sql
CREATE DATABASE db_thethingscorner
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Atur koneksi di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_thethingscorner
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:

```bash
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

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

Akun demo yang direncanakan:

| Email | Role | Password |
|---|---|---|
| `admin@demo.local` | Admin Master | `rahasia123` |
| `adminbiasa@demo.local` | Admin Biasa | `rahasia123` |
| `budi@demo.local` | Pembeli | `rahasia123` |

Seeder belum dibuat pada Slice 0.

---

## Troubleshooting Windows / OneDrive

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
