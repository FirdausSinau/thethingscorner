# Context Handoff - thethingscorner

**Tanggal:** 11 November 2026  
**Tujuan:** Ringkasan konteks project untuk compact atau handoff ke AI/developer lain.  
**Repo:** `https://github.com/FirdausSinau/thethingscorner.git`  
**Lokal:** `Bisnis Sepatu/`  
**Branch aktif:** `firdaus`  
**Main terakhir:** `7878140` (`docs: log order status review`)

---

## 1. Ringkasan singkat

Project ini adalah website untuk **thethingscorner**, toko sepatu handmade custom di Bandung. Fase 1 mencakup katalog, cart, checkout, pembayaran, pengiriman, pesanan, custom PO, review, stok, rekonsiliasi Shopee, laporan, dan admin.

Stack:

- Laravel 13
- PHP 8.4
- PostgreSQL 18 / Neon
- Blade
- Tailwind CSS 4
- Vite
- MySQL tidak dipakai lagi

Tim:

| Nama | Domain |
|---|---|
| Firdaus | Backend core, schema, service, integrasi, security, deploy |
| Anand | Buyer storefront, cart, checkout UI, order pembeli |
| Hafiez | Admin operations, produk, stok, pesanan admin, laporan, Shopee |

---

## 2. Aturan kerja dan database

- Database bersama: **Neon**, satu database `neondb`, tanpa branch Neon.
- Hanya **Firdaus** yang boleh menjalankan migrasi ke Neon.
- Jangan `migrate:fresh`, `migrate:refresh`, `migrate:reset`, atau `db:wipe` di Neon.
- Test memakai SQLite in-memory, bukan Neon.
- Sebelum migrasi Neon: backup, `migrate:status`, `migrate --pretend`, lalu `migrate --force`.
- Jangan rollback migration di Neon tanpa backup dan persetujuan.
- `.env` dan `.env.*` tidak masuk Git.
- Credential Neon/S3 hanya di `.env` masing-masing, tidak di chat, screenshot, atau dokumen.

Dokumen aturan lengkap: `project-management/database-rules.md`.

---

## 3. Struktur dokumentasi

### docs/ - teknis dan produk

- `README.md` - index dokumentasi teknis
- `PRD Fase 1 - The Things Corner.md` - kebutuhan produk
- `erd.md` - ERD versi 0.5
- `technical-design-fase-1.md` - arsitektur, route, state machine, deployment
- `backend-overview.md` - alur backend dan jalur belajar
- `decisions.md` - decision log versi 0.6
- `design-system.md` - token warna, tipografi, komponen
- `wireframes.md` - wireframe
- `implementation-plan-fase-1.md` - progres per slice
- `data/` - sample CSV
- `sprint-1-catalog-contract.md` - kontrak katalog versi 2.2
- `sprint-2-cart-contract.md` - kontrak cart
- `sprint-2-checkout-contract.md` - kontrak checkout dan reservasi

### project-management/ - kerja tim

- `README.md`
- `work-plan.md` - domain, slice, urutan batch
- `team-workflow.md` - branch, PR, aturan konflik
- `setup-local.md` - setup laptop dan PostgreSQL/Neon
- `data-request.md` - checklist data client
- `database-rules.md` - aturan Neon/migrasi/backup
- `context-handoff.md` - dokumen ini
- `client-docs/` - file `.docx` client, tidak masuk Git

File `.docx` tidak di-commit. Simpan salinan di OneDrive/penyimpanan tim.

---

## 4. Progres slice

| Slice | Status |
|---|---|
| 0 Setup project | Selesai |
| 1 Auth dan role | Selesai |
| 2 Katalog dan varian | Selesai |
| 3 Cart | Selesai |
| 4 Checkout dan reservasi | Backend + checkout UI selesai; admin order read-only belum |
| 5 Pembayaran | Belum |
| 6 Pengiriman | Belum |
| 7 Pesanan dan tracking | Belum |
| 8 Custom PO | Belum |
| 9 Review dan retur | Belum |
| 10 Stok dan kartu stok | Belum |
| 11 Shopee CSV | Belum |
| 12 Laporan dan export | Belum |
| 13 Settings dan integrasi | Belum |
| 14 Security, QA, UAT, deploy | Belum |

Test terakhir: **63 passed, 179 assertions**.  
Build Vite: lulus.

---

## 5. Schema katalog aktual

Struktur:

```text
products
  -> product_colors
       -> product_color_images
       -> product_variants

products
  -> product_images
```

Field produk:

- `category_id`, `name`, `slug`, `description`
- `base_price`
- `weight_grams`
- `is_active`
- `is_preorder`
- `preorder_close_date`
- `estimated_ready_date`
- `rating_avg`, `rating_count`

Field warna:

- `product_id`, `name`, `hex_code`

Field varian:

- `product_color_id`
- `size`
- `sku` unik
- `stock`
- `stock_reserved`
- `price` nullable; jika null memakai `base_price`

Model:

- `App\Models\Product\Product`
- `App\Models\Product\ProductColor`
- `App\Models\Product\ProductVariant`
- `App\Models\Product\ProductImage`
- `App\Models\Product\ProductColorImage`
- `App\Models\Product\Category`

Catatan: model lama di root `app/Models/Product.php` sudah dihapus. Jangan membuat model katalog baru dengan namespace lama.

---

## 6. Model dan fitur cart/checkout

Model:

- `App\Models\CartItem`
- `App\Models\Address`
- `App\Models\Order`
- `App\Models\OrderItem`
- `App\Models\StockReservation`

Service:

- `App\Services\CartService`
- `App\Services\CheckoutService`

Enum:

- `App\Enums\OrderStatus`
- `App\Enums\OrderChannel`
- `App\Enums\PaymentMethod`
- `App\Enums\ReservationStatus`

Order status sekarang:

```text
MENUNGGU_PEMBAYARAN
DIBAYAR
MENUNGGU_KONFIRMASI
DIKEMAS
DIKIRIM
SELESAI
DIBATALKAN
KEDALUWARSA
```

`BARU` sudah dihapus. `PENDING` bukan order status; nanti masuk `PaymentStatus` di Slice 5.

Arti `MENUNGGU_KONFIRMASI`: pembayaran sudah diterima, pesanan menunggu konfirmasi admin sebelum dikemas.

Status pembayaran yang direncanakan:

```text
PENDING
PAID
EXPIRED
FAILED
REFUNDED
```

---

## 7. Aturan cart dan checkout

Cart:

- Hanya untuk user login.
- Persisten di tabel `cart_items`.
- Cart tidak mengunci stok.
- Qty tidak boleh melebihi `stock - stock_reserved`.
- Harga dihitung ulang dari database.
- Bisa tambah, ubah, hapus, dan kosongkan.

Checkout:

- Alamat default user disimpan dalam transaksi yang sama dengan checkout.
- Metode pembayaran:
  - QRIS, VA, e-wallet: reservasi 30 menit.
  - Transfer manual: reservasi 60 menit.
- Checkout memakai transaction dan `lockForUpdate()`.
- Membuat order, order items snapshot, stock reservations.
- Menambah `stock_reserved`; `stock` fisik tidak berubah.
- Menghapus cart setelah sukses.
- Ongkir masih 0/placeholder sampai Slice 6.
- Harga dan stok selalu dihitung di server.
- Reservation expiry command: `php artisan reservations:release-expired`, dijadwalkan setiap menit.
- Scheduler lokal: `php artisan schedule:work`. Production butuh cron `schedule:run`.

---

## 8. Route penting

Publik:

- `/` home
- `/produk` katalog
- `/produk/{product:slug}` detail produk
- `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`

Buyer (auth):

- `/dashboard`
- `/keranjang` cart
- `POST /keranjang` tambah cart
- `PATCH /keranjang/{cartItem}` ubah qty
- `DELETE /keranjang/{cartItem}` hapus item
- `DELETE /keranjang` kosongkan cart
- `/checkout` checkout
- `POST /checkout` buat order
- `/pesanan/{order:order_code}` detail order pembeli

Admin (`auth` + `role:admin,admin_master`):

- `/admin`
- `/admin/products`
- `/admin/products/create`
- `/admin/products/{product}/edit`
- `/admin/products/{product}/deactivate`
- `/admin/products/{product}/colors`
- `/admin/products/{product}/images`
- `/admin/colors/{color}/images`
- `/admin/colors/{color}/variants`
- `/admin/admins` (khusus Admin Master)

Admin order read-only belum dibuat.

---

## 9. Keputusan produk dan bisnis

- Brand resmi: `thethingscorner` (huruf kecil satu kata).
- Cart tidak reserve stok.
- Reservasi stok dimulai saat checkout.
- QRIS/VA/e-wallet 30 menit; transfer manual 60 menit.
- Model stok sementara: satu pool fisik.
- Diskon ditunda, menunggu aturan bisnis (D-27).
- Produk reject TBD (D-28); biasanya berasal dari kesalahan produksi PO; belum ada schema.
- Retur manual via CS/`wa.me`.
- Review nanti: 30 hari, satu per order item, toko balas sekali, Admin Master bisa hide.
- Payment gateway: Midtrans default, Xendit alternatif, manual transfer fallback.
- Shipping: Biteship, fallback flat/manual.
- Media produk: `PRODUCT_IMAGES_DISK=public` untuk lokal, `s3` untuk Neon Object Storage.
- Produk yang sudah dipakai transaksi harus dinonaktifkan, bukan dihapus.
- Seeder hanya untuk demo Theon Docmart; Cassie Docmart dibuat manual lewat admin; produk berikutnya lewat admin.

---

## 10. Status Neon dan gambar

Database Neon aktif dan dipakai bersama.

Snapshot terakhir:

- `users` 3: Admin Master, Admin Biasa, Budi Santoso.
- `products` 1: Theon Docmart.
- `product_colors` 2.
- `product_variants` 7.
- `product_images` 1.
- `product_color_images` 0.
- `orders` 5, semuanya status `DIBAYAR`.
- `order_items` 7.
- `stock_reservations` 7, semuanya `active`.
- `stock_reserved` masih menahan stok 7 pasang.

Penting:

- Data di atas tampaknya hasil reset/uji tim. DB pernah di-reset; migration tercatat 16 dengan batch 1.
- 5 order `DIBAYAR` dengan reservasi masih `active` adalah data uji yang belum konsisten. Payment slice harus mengubah reservasi menjadi `consumed` dan mengurangi stok. Jangan menganggap ini alur final.
- Jangan jalankan scheduler tanpa memahami data uji ini; scheduler akan melepas reservasi yang kedaluwarsa.

Masalah gambar Neon:

- `.env` memakai `PRODUCT_IMAGES_DISK=s3`.
- Endpoint yang dipakai: `...storage.c-1.ap-southeast-1.aws.neon.tech`.
- Bucket: `product`.
- Path style: `true`.
- Region: `ap-southeast-1`.
- Access key saat dicek menghasilkan `InvalidAccessKeyId`.
- Object `products/theon-dockmart.png` menghasilkan `NoSuchKey` di URL publik.
- Artinya: credential salah/tidak cocok dan file belum ada di bucket.
- Endpoint `c-1` bisa dihubungi; `c1` dari screenshot tidak connect. Jangan ubah `c-1` ke `c1` tanpa bukti.
- Code image storage sudah configurable lewat `app/Support/ProductImageDisk.php`.
- `ProductImage`/`ProductColorImage` accessor untuk `public` mengecek file; untuk `s3` langsung memakai URL tanpa cek exists agar tidak lambat.
- Jika file S3 hilang, browser menampilkan gambar rusak. Perlu upload ulang atau fallback tambahan.

Yang perlu dilakukan untuk gambar:

1. Perbaiki `AWS_ACCESS_KEY_ID` dan `AWS_SECRET_ACCESS_KEY` dari dashboard Neon.
2. Pastikan tidak ada spasi/quote/duplikat baris AWS di `.env`.
3. `php artisan config:clear`.
4. Cek `Storage::disk('s3')->allFiles()`.
5. Upload file ke key `products/theon-dockmart.png`, atau upload ulang lewat admin.
6. Cek `Storage::disk('s3')->exists(...)` dan buka URL di browser.

---

## 11. Login demo

Password semua: `rahasia123`

- `admin@demo.local` - Admin Master
- `adminbiasa@demo.local` - Admin Biasa
- `budi@demo.local` - Pembeli

---

## 12. Environment

`.env` lokal (jangan commit):

```dotenv
DB_CONNECTION=pgsql
DB_URL=<redacted>
PRODUCT_IMAGES_DISK=s3
AWS_ENDPOINT=<neon-object-storage-endpoint>
AWS_DEFAULT_REGION=ap-southeast-1
AWS_ACCESS_KEY_ID=<redacted>
AWS_SECRET_ACCESS_KEY=<redacted>
AWS_BUCKET=product
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Untuk lokal tanpa S3:

```dotenv
PRODUCT_IMAGES_DISK=public
```

`.env.example` sudah memuat placeholder dan default `PRODUCT_IMAGES_DISK=public`.

Local PostgreSQL juga tersedia di port 5432, tetapi `.env` sekarang mengarah ke Neon.

---

## 13. Git dan branch

Branch:

- `main` - stabil
- `firdaus` - branch kerja Firdaus
- `Anand` - branch Anand
- `hafiez` - branch Hafiez
- `catalog-reconcile` dan `batch-2-review` sudah merge ke main

Commit penting:

- `35545cc` setup Laravel + docs
- `990ea92` auth dan role
- `91280d5` admin accounts + password reset
- `7783ba2` switch PostgreSQL
- `f49b615` schema katalog awal
- `51ee81e` rekonsiliasi katalog
- `e656a18` cart, checkout, reservasi backend
- `5f7211b` UI cart/checkout/order dari Anand
- `51d34bf` test UI cart/checkout
- `87e0643` image disk configurable
- `7caaa22` status order, cart clear, transaksi alamat, product deactivate
- `7878140` docs log order status review

HEAD saat handoff: `7878140`.

Server lokal:

- `php artisan serve --host=127.0.0.1 --port=8000`
- Terakhir terlihat listening di PID 11436.
- Matikan dengan `Stop-Process -Id <PID>` jika perlu.

---

## 14. Test

```powershell
php artisan test
npm run build
```

Status:

- 63 test lulus
- 179 assertions

Test penting:

- `AuthTest`
- `DashboardAccessTest`
- `AdminUserManagementTest`
- `CatalogTest`
- `ProductImageStorageTest`
- `CartServiceTest`
- `CartCheckoutUiTest`
- `CheckoutServiceTest`
- `PasswordResetTest`

---

## 15. Yang belum / next step

Prioritas sekarang:

1. **Perbaiki Neon Object Storage**: credential valid dan file gambar benar-benar ada di bucket.
2. **Hafiez**: admin order read-only (`/admin/orders`, detail order). Ini melengkapi Slice 4.
3. **Slice 5 Pembayaran**:
   - `PaymentStatus` enum terpisah.
   - Halaman pembayaran.
   - Midtrans/Xendit adapter.
   - Transfer manual verification.
   - Webhook signature + idempotency.
   - Saat pembayaran sukses: ubah reservasi menjadi `consumed`, kurangi stok fisik, ubah order ke `DIBAYAR`.
4. **Slice 6 Pengiriman**: Biteship, ongkir, resi, tracking.
5. **Slice 7 Pesanan**: state machine, admin actions, buyer tracking, auto-selesai.
6. **Slice 8 Custom PO**.
7. **Slice 9 Review dan retur**.
8. **Slice 10 Stok dan kartu stok**.
9. **Slice 11 Shopee CSV**.
10. **Slice 12 Laporan**.
11. **Slice 13 Settings**.
12. **Slice 14 QA/UAT/deploy**.

Risiko yang perlu diputuskan nanti:

- Hapus warna/varian masih permanen; perlu mekanisme nonaktifkan sebelum ada order history.
- State transition order belum dijaga service/state machine.
- Payment status belum terpisah dari order status.
- Ongkir masih placeholder.
- Product image fallback untuk S3 belum ada.
- Brand formal di beberapa dokumen masih memakai "The Things Corner"; UI sudah `thethingscorner`.
- Migration history Neon pernah berisi duplikat; sekarang tercatat 16 migration batch 1, tetapi jangan rollback sembarangan.

---

## 16. Aturan untuk AI berikutnya

- Baca `docs/implementation-plan-fase-1.md`, `project-management/work-plan.md`, dan `docs/decisions.md` sebelum mengubah fitur.
- Jangan mengubah schema produk tanpa koordinasi dengan domain produk (Hafiez) dan review Firdaus.
- Jangan commit `.env`, credential, file client, vendor, node_modules, atau build.
- Jangan menjalankan migrasi destruktif ke Neon.
- Jika ada kode yang tidak jelas, tanyakan konteksnya sebelum mengubah.
- Cek apakah perubahan sudah mencakup route, controller, service, Blade, test, dan dokumentasi.
- Utamakan perubahan kecil yang bisa dites, bukan refactor besar tanpa alasan.
