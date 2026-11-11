# Implementation Plan Fase 1 — The Things Corner

**Versi:** 0.1  
**Tanggal:** 6 November 2026  
**Status:** Dikerjakan bertahap  
**Referensi:** PRD v0.2, ERD v0.1, Technical Design v0.1, Decision Log v0.1

---

## 1. Aturan kerja

- Satu slice = satu tujuan yang bisa didemokan.
- Jangan mulai slice berikutnya sebelum slice sekarang lulus test dan demo.
- Setiap slice berakhir dengan commit yang jelas.
- Setiap slice memiliki migration, model, service, request, policy, controller, Blade, test, dan seeder bila perlu.
- Jangan hardcode keputusan `Assumption`/`TBD`.
- Gunakan data dummy dari `docs/data/`.
- Provider asli hanya setelah sandbox siap.
- Selalu ada fallback manual.

### 1.1 Definition of Done per slice

- Kode berjalan tanpa error.
- Feature test/unit test relevan lulus.
- Tidak ada `dd()` atau debug tertinggal.
- UI responsif di 360px, 768px, 1280px.
- Authorization diuji.
- Commit jelas.
- Demo bisa dijelaskan: apa yang dibuat, kenapa, dan batasannya.

### 1.2 Commit convention

```text
feat: add ...
fix: ...
refactor: ...
test: ...
docs: ...
chore: ...
```

Contoh:

```text
feat: add persistent cart
feat: add checkout with stock reservation
feat: add manual transfer fallback
feat: add Biteship rate adapter
feat: add review reply and moderation
```

---

## 2. Progress

| Slice | Nama | Status |
|---|---|---|
| 0 | Setup project | Selesai (commit `35545cc`) |
| 1 | Auth dan role | Selesai: register/login/logout, reset password, role middleware, dashboard per role, kelola Admin Biasa (commit `990ea92`, `91280d5`) |
| 2 | Katalog dan varian | Selesai: katalog publik, detail, admin produk/warna/varian/foto, storage lokal; schema warna/varian dari tim produk; 37 test lulus (commit `51ee81e`) |
| 3 | Keranjang persisten | Backend selesai: `cart_items`, `CartItem`, `CartService`, 9 test; UI cart belum |
| 4 | Checkout dan reservasi | Backend selesai: `addresses`, `orders`, `order_items`, `stock_reservations`, `CheckoutService`, expiry command, 6 test; UI checkout dan admin order belum |
| 5 | Pembayaran | Belum |
| 6 | Pengiriman | Belum |
| 7 | Pesanan dan tracking | Belum |
| 8 | Custom PO | Belum |
| 9 | Review dan retur | Belum |
| 10 | Stok dan kartu stok | Belum |
| 11 | Shopee CSV | Belum |
| 12 | Laporan dan export | Belum |
| 13 | Settings dan integrasi | Belum |
| 14 | Security, QA, UAT, deploy | Belum |

### 2.1 Log progres

| Tanggal | Slice | Hasil | Commit |
|---|---|---|---|
| 2026-11-09 | 0 | Laravel 13.40, MySQL `db_thethingscorner`, migrasi default, storage link, build Vite, docs, GitHub push. Base layout selesai. | `35545cc` |
| 2026-11-09 | 1 | Register/login/logout, enum role + `is_active`, middleware `role`, dashboard pembeli/admin, seeder demo. 15 test lulus. Responsif dicek di 484px dan 1280px. | `990ea92` |
| 2026-11-09 | 1 | Reset password lewat email (notifikasi Indonesia), kelola Admin Biasa oleh Admin Master (buat, nonaktifkan, aktifkan), proteksi akun Admin Master dan eskalasi role. 31 test lulus. UI dicek di mobile dan desktop. | `91280d5` |
| 2026-11-09 | 0 | Database default pindah ke PostgreSQL 16+. Migrasi dan 31 test diuji di PostgreSQL 18.6. Setup tim dan decision log diperbarui. | `7783ba2` |
| 2026-11-09 | 2 | Schema categories/products/product_variants/product_images, model, enum ProductStatus, seeder 10 produk/30 varian, kontrak Slice 2. 35 test lulus. Migration dan seeder dijalankan ke Neon. | `f49b615` |
| 2026-11-10 | 2 | Schema freeze. Migration tim produk mengubah tabel katalog di Neon dan menambah product_colors/product_color_images. Backup Neon dibuat, kontrak ditandai tertahan sampai migration dan kode di-push lalu direkonsiliasi. | `0461cfc` |
| 2026-11-11 | 2 | Rekonsiliasi selesai: adopsi schema produk -> warna -> varian, hapus migration/model duplikat, storage gambar ke disk public, route katalog publik dan detail, admin produk/warna/varian/foto, 37 test lulus. | `51ee81e` |
| 2026-11-11 | 2 | Brand resmi `thethingscorner`; tambah `weight_grams`, `rating_avg`, `rating_count`, `stock_reserved`; migration ke Neon; 38 test lulus. | `352ccaf` |
| 2026-11-11 | 3 | `cart_items`, `CartItem`, `CartService`, validasi stok tanpa mengunci stok, 9 test lulus. | `e656a18` |
| 2026-11-11 | 4 | `addresses`, `orders`, `order_items`, `stock_reservations`, `CheckoutService`, expiry command, 6 test lulus. Migration ke Neon. | `e656a18` |

---

## 3. Slice 0 — Setup project

**Tujuan:** aplikasi Laravel dasar bisa dijalankan dan di-deploy ke staging.

**Prasyarat:** tidak ada.

**Langkah:**

1. Buat project Laravel 13.
2. Konfigurasi PostgreSQL.
3. Setup Git, `.gitignore`, `.env.example`.
4. Setup Vite + Tailwind + Alpine.
5. Setup layout dasar dan halaman kosong.
6. Setup queue database dan scheduler.
7. Setup storage public/private.
8. Setup staging + HTTPS.
9. Setup fake provider config.

**File utama:**

- `composer.json`
- `package.json`
- `.env.example`
- `vite.config.js`
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/admin.blade.php`
- `config/services.php`

**Acceptance criteria:**

- `php artisan serve` menampilkan halaman awal.
- Koneksi PostgreSQL berhasil.
- Asset build berhasil.
- Halaman admin kosong bisa diakses.
- Staging bisa dibuka.

**Test:**

- `php artisan about`
- `php artisan migrate`
- `npm run build`

**Commit:** `chore: initialize Laravel project and base layout`

---

## 4. Slice 1 — Auth dan role

**Tujuan:** pembeli bisa daftar/login; Admin Master dan Admin Biasa punya akses berbeda.

**Prasyarat:** slice 0.

**Langkah:**

1. Migration `role` dan `is_active` pada users.
2. Enum `UserRole`.
3. Register, login, logout, reset password.
4. Seeder Admin Master.
5. Admin Master membuat/menonaktifkan Admin Biasa.
6. Middleware `role`.
7. Gate dasar.
8. Layout dashboard per role.
9. Rate limit login.

**File utama:**

- `app/Enums/UserRole.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Middleware/EnsureRole.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Providers/AppServiceProvider.php`
- `database/seeders/DemoUserSeeder.php`
- `resources/views/auth/*`
- `resources/views/dashboard.blade.php`

**Acceptance criteria:**

- Pembeli daftar dan login.
- Admin Master login dari seeder.
- Admin Biasa dibuat Admin Master.
- Buyer membuka `/admin` → 403.
- Admin Biasa membuka kelola admin → 403.
- Guest membuka halaman protected → redirect login.
- Logout memakai POST + CSRF.

**Test:**

- Feature test register/login/logout.
- Feature test role access.
- Feature test guest redirect.

**Commit:** `feat: add authentication and role-based access`

---

## 5. Slice 2 — Katalog dan varian

**Tujuan:** produk dan varian tampil; admin bisa mengelola.

**Prasyarat:** slice 1.

**Langkah:**

1. Migration categories, products, product_variants.
2. Model dan relasi.
3. Admin CRUD produk.
4. Upload foto lokal + optimasi.
5. Admin CRUD varian.
6. Katalog publik: search, filter, pagination.
7. Detail produk: galeri, ukuran, stok.
8. Seeder data dari `docs/data/`.

**File utama:**

- `app/Models/Product.php`
- `app/Models/ProductVariant.php`
- `app/Http/Controllers/Admin/ProductController.php`
- `app/Http/Controllers/ProductController.php`
- `app/Http/Requests/StoreProductRequest.php`
- `app/Http/Requests/UpdateProductRequest.php`
- `resources/views/products/*`
- `resources/views/admin/products/*`
- `database/seeders/DemoCatalogSeeder.php`

**Acceptance criteria:**

- 10 produk demo tampil.
- Filter kategori dan pencarian bekerja.
- Varian ukuran tampil.
- Stok habis tidak bisa ditambahkan.
- Admin bisa tambah/edit produk dan varian.
- Foto tampil dan dioptimasi.

**Test:**

- Feature test katalog/filter/pagination.
- Feature test admin CRUD.
- Feature test authorization Admin Biasa.

**Commit:** `feat: add product catalog and variants`

---

## 6. Slice 3 — Keranjang persisten

**Tujuan:** pembeli bisa menyimpan produk tanpa mengunci stok.

**Prasyarat:** slice 2.

**Langkah:**

1. Migration `cart_items`.
2. `CartService`.
3. Tambah/ubah/hapus item.
4. Validasi stok tersedia.
5. Tampilan keranjang dan ringkasan.
6. Empty state.
7. Merge cart setelah login bila relevan.

**File utama:**

- `app/Models/CartItem.php`
- `app/Services/CartService.php`
- `app/Http/Controllers/CartController.php`
- `resources/views/cart/index.blade.php`

**Acceptance criteria:**

- Item masuk keranjang.
- Stok tidak berubah.
- Qty tidak melebihi stok.
- Item bisa dihapus.
- Keranjang tetap ada setelah refresh.
- Harga dihitung ulang di server.

**Test:**

- Feature test add/update/remove.
- Feature test stok tidak berubah.
- Feature test qty melebihi stok.

**Commit:** `feat: add persistent cart without stock lock`

---

## 7. Slice 4 — Checkout dan reservasi

**Tujuan:** checkout membuat order dan menahan stok sementara.

**Prasyarat:** slice 3.

**Langkah:**

1. Migration addresses, orders, order_items, stock_reservations.
2. Alamat default dan form alamat.
3. Halaman checkout.
4. `CheckoutService` dalam transaction.
5. Cek stok + row lock.
6. Buat order + reservation.
7. Fake ongkir/flat.
8. Halaman sukses.
9. Scheduler `ExpireStockReservations`.

**File utama:**

- `app/Services/CheckoutService.php`
- `app/Services/StockService.php`
- `app/Http/Controllers/CheckoutController.php`
- `app/Http/Requests/CheckoutRequest.php`
- `app/Models/Order.php`
- `app/Models/StockReservation.php`
- `app/Jobs/ExpireStockReservations.php`
- `resources/views/checkout/*`

**Acceptance criteria:**

- Checkout berhasil membuat order.
- Stok tersedia berkurang sesuai reservasi.
- Stok fisik belum berkurang.
- Reservasi kedaluwarsa 30/60 menit.
- Jika stok habis, order ditolak.
- Keranjang tetap ada setelah order.

**Test:**

- Feature test checkout sukses.
- Feature test stok habis.
- Feature test reservasi kedaluwarsa.
- Feature test transaction rollback.

**Commit:** `feat: add checkout with stock reservation`

---

## 8. Slice 5 — Pembayaran

**Tujuan:** QRIS/VA/e-wallet via gateway dan transfer manual fallback.

**Prasyarat:** slice 4.

**Langkah:**

1. Migration payments.
2. `PaymentGateway` interface.
3. `FakePaymentGateway`.
4. Transfer manual: upload bukti.
5. Admin verifikasi transfer.
6. Midtrans/Xendit adapter sandbox.
7. Webhook + signature + idempotency.
8. Status payment dan order.
9. Halaman pembayaran.
10. Scheduler pembayaran kedaluwarsa.

**File utama:**

- `app/Services/Payment/PaymentGateway.php`
- `app/Services/Payment/FakePaymentGateway.php`
- `app/Services/Payment/MidtransGateway.php`
- `app/Services/PaymentService.php`
- `app/Http/Controllers/PaymentCallbackController.php`
- `app/Http/Controllers/Admin/OrderController.php`
- `resources/views/payments/*`
- `config/services.php`

**Acceptance criteria:**

- QRIS/VA sandbox membuat transaksi.
- Webhook valid mengubah status.
- Webhook duplikat tidak menggandakan stok.
- Transfer manual bisa diverifikasi.
- Pembayaran kedaluwarsa membatalkan order.
- Status tidak bisa diubah dari browser.

**Test:**

- Feature test fake payment.
- Feature test manual transfer.
- Feature test webhook signature.
- Feature test duplicate callback.
- Feature test expiry.

**Commit:** `feat: add payment gateway and manual transfer fallback`

---

## 9. Slice 6 — Pengiriman

**Tujuan:** ongkir, pickup, label, tracking via Biteship; fallback manual.

**Prasyarat:** slice 4.

**Langkah:**

1. Migration shipments.
2. `ShippingProvider` interface.
3. `FakeShippingProvider`.
4. `BiteshipProvider`.
5. Cek ongkir di checkout.
6. Admin buat shipment/pickup.
7. Simpan resi.
8. Webhook tracking.
9. Fallback flat/manual.
10. Halaman tracking pembeli.

**File utama:**

- `app/Services/Shipping/ShippingProvider.php`
- `app/Services/Shipping/FakeShippingProvider.php`
- `app/Services/Shipping/BiteshipProvider.php`
- `app/Services/ShippingService.php`
- `app/Http/Controllers/BiteshipWebhookController.php`
- `app/Http/Controllers/Admin/OrderController.php`
- `resources/views/orders/tracking.blade.php`

**Acceptance criteria:**

- Ongkir tampil dari provider/fake.
- Checkout tetap bisa jika provider gagal.
- Admin membuat shipment.
- Resi tersimpan.
- Tracking tampil sebagai teks.
- Webhook invalid ditolak.

**Test:**

- Feature test rates.
- Feature test fallback.
- Feature test create shipment.
- Feature test tracking webhook.

**Commit:** `feat: add Biteship shipping and tracking fallback`

---

## 10. Slice 7 — Pesanan dan tracking

**Tujuan:** pembeli dan admin melihat status pesanan yang konsisten.

**Prasyarat:** slice 5 dan 6.

**Langkah:**

1. Daftar pesanan pembeli.
2. Detail pesanan.
3. Daftar pesanan admin.
4. Filter status/kanal/tanggal.
5. Aksi admin: proses, kirim, tracking, batalkan.
6. Konfirmasi terima pembeli.
7. Auto-selesai 7 hari.
8. Order status logs + Observer.
9. Notifikasi web.

**File utama:**

- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/Admin/OrderController.php`
- `app/Services/OrderService.php`
- `app/Observers/OrderObserver.php`
- `app/Jobs/AutoCompleteOrders.php`
- `resources/views/orders/*`
- `resources/views/admin/orders/*`

**Acceptance criteria:**

- Pembeli hanya melihat order sendiri.
- Admin melihat semua.
- Status mengikuti state machine.
- Auto-selesai berjalan.
- Timeline status tampil.
- Notifikasi web muncul.

**Test:**

- Feature test ownership.
- Feature test transitions.
- Feature test auto-complete.
- Feature test observer log.

**Commit:** `feat: add order lifecycle and tracking`

---

## 11. Slice 8 — Custom PO

**Tujuan:** pengajuan PO, penawaran, DP, produksi, pelunasan, selesai.

**Prasyarat:** slice 4 dan 5.

**Langkah:**

1. Migration custom_pos, po_status_logs.
2. Form PO pembeli.
3. Upload referensi (private).
4. Admin Master quote: harga, DP, deadline.
5. Pembayaran DP.
6. Admin update status produksi.
7. Pelunasan.
8. Pengiriman.
9. Timeline PO.
10. `wa.me` link.

**File utama:**

- `app/Models/CustomPo.php`
- `app/Services/CustomPoService.php`
- `app/Http/Controllers/CustomPoController.php`
- `app/Http/Controllers/Admin/CustomPoController.php`
- `resources/views/custom-pos/*`
- `resources/views/admin/custom-pos/*`

**Acceptance criteria:**

- PO bisa diajukan.
- Admin memberi quote.
- DP 50% (configurable).
- PO tidak masuk produksi sebelum DP.
- Status mengikuti state machine.
- Pelunasan dan pengiriman tercatat.

**Test:**

- Feature test create PO.
- Feature test DP required.
- Feature test transitions.
- Feature test ownership.

**Commit:** `feat: add custom pre-order workflow`

---

## 12. Slice 9 — Review dan retur

**Tujuan:** review setelah selesai, balas review, moderasi, retur via CS.

**Prasyarat:** slice 7.

**Langkah:**

1. Migration reviews, review_photos.
2. Eligibility: order item selesai, ≤30 hari, belum review.
3. Form review + foto.
4. Tampilan review di produk.
5. Admin balas review.
6. Admin Master sembunyikan review.
7. Tombol retur/komplain ke `wa.me`.
8. Observer rating produk.

**File utama:**

- `app/Models/Review.php`
- `app/Services/ReviewService.php`
- `app/Http/Controllers/ReviewController.php`
- `app/Http/Controllers/Admin/ReviewController.php`
- `app/Observers/ReviewObserver.php`
- `resources/views/reviews/*`
- `resources/views/admin/reviews/*`

**Acceptance criteria:**

- Review hanya untuk order selesai.
- Review ganda ditolak.
- Review di luar 30 hari ditolak.
- Foto review tampil.
- Admin bisa balas satu kali.
- Admin Master bisa hide.
- Retur mengarah ke `wa.me`.

**Test:**

- Feature test eligibility.
- Feature test duplicate.
- Feature test reply.
- Feature test hide.
- Feature test retur link.

**Commit:** `feat: add review reply and moderation`

---

## 13. Slice 10 — Stok dan kartu stok

**Tujuan:** semua perubahan stok tercatat dan bisa diaudit.

**Prasyarat:** slice 2 dan 4.

**Langkah:**

1. Migration stock_movements.
2. `StockService`: in, out, adjustment, release.
3. Admin stok masuk/keluar.
4. Penyesuaian dengan alasan.
5. Kartu stok per varian.
6. Stok kritis.
7. Stock opname manual.
8. Pastikan stok tidak negatif.

**File utama:**

- `app/Models/StockMovement.php`
- `app/Services/StockService.php`
- `app/Http/Controllers/Admin/StockController.php`
- `resources/views/admin/stock/*`

**Acceptance criteria:**

- Setiap perubahan punya mutasi.
- Stok tidak negatif.
- Kartu stok kronologis.
- Penyesuaian wajib alasan.
- Stok kritis tampil.

**Test:**

- Unit test stock service.
- Feature test adjustment.
- Feature test negative stock.
- Feature test ledger.

**Commit:** `feat: add stock ledger and adjustments`

---

## 14. Slice 11 — Shopee CSV

**Tujuan:** import pesanan/stok Shopee dan rekonsiliasi harian.

**Prasyarat:** slice 10.

**Langkah:**

1. Migration shopee_imports.
2. Upload CSV.
3. Validasi header, SKU, duplikat.
4. Simpan baris berhasil/gagal.
5. Laporan rekonsiliasi.
6. Export stok untuk Shopee.
7. Laporan penjualan harian.

**File utama:**

- `app/Services/ShopeeImportService.php`
- `app/Http/Controllers/Admin/ShopeeImportController.php`
- `resources/views/admin/shopee/*`
- `docs/data/shopee_orders_sample.csv`

**Acceptance criteria:**

- CSV valid bisa diimport.
- Duplikat ditolak.
- SKU tidak dikenal ditolak.
- Baris gagal ditampilkan.
- Stok tidak berkurang dua kali.
- Rekonsiliasi menampilkan selisih.

**Test:**

- Feature test import valid.
- Feature test duplicate.
- Feature test invalid SKU.
- Feature test reconciliation.

**Commit:** `feat: add Shopee CSV import and reconciliation`

---

## 15. Slice 12 — Laporan dan export

**Tujuan:** admin master bisa melihat dan mengekspor rekap.

**Prasyarat:** slice 7, 8, 10, 11.

**Langkah:**

1. Laporan penjualan harian.
2. Laporan kartu stok.
3. Laporan PO.
4. Rekonsiliasi Shopee.
5. Filter tanggal/kanal/produk.
6. Export Excel/CSV.
7. Pagination dengan filter.

**File utama:**

- `app/Services/ReportService.php`
- `app/Http/Controllers/Admin/ReportController.php`
- `resources/views/admin/reports/*`

**Acceptance criteria:**

- Angka laporan sesuai data.
- Filter bekerja.
- Pagination mempertahankan filter.
- Export bisa dibuka di Excel.
- Hanya Admin Master.

**Test:**

- Feature test report totals.
- Feature test filter.
- Feature test pagination query string.
- Feature test export.

**Commit:** `feat: add reports and CSV export`

---

## 16. Slice 13 — Settings dan integrasi

**Tujuan:** admin master bisa melihat status integrasi dan mengatur toko.

**Prasyarat:** slice 1.

**Langkah:**

1. Migration settings.
2. Pengaturan toko: bank, kurir, origin.
3. Halaman status integrasi.
4. Log webhook.
5. Link ke dashboard provider.
6. Jangan tampilkan secret.

**File utama:**

- `app/Models/Setting.php`
- `app/Http/Controllers/Admin/SettingController.php`
- `resources/views/admin/settings/*`

**Acceptance criteria:**

- Admin Master bisa ubah pengaturan.
- Admin Biasa tidak bisa.
- Status integrasi tampil.
- Secret tidak tampil.

**Test:**

- Feature test settings access.
- Feature test update.
- Feature test secret hidden.

**Commit:** `feat: add store settings and integration status`

---

## 17. Slice 14 — Security, QA, UAT, deploy

**Tujuan:** aplikasi siap dipakai client.

**Prasyarat:** slice 0–13.

**Langkah:**

1. Security review Laravel OWASP.
2. Rate limit dan upload validation.
3. Feature test seluruh AC.
4. Browser test alur utama.
5. UAT dengan data client.
6. Staging → production.
7. Backup, monitoring, queue, scheduler.
8. Dokumentasi dan handover.

**Acceptance criteria:**

- Semua AC lulus.
- Tidak ada akses tanpa izin.
- Webhook valid/invalid diuji.
- Backup dan restore diuji.
- Client bisa menjalankan alur.
- README dan akun demo tersedia.

**Test:**

- `php artisan test`
- Browser test
- Manual UAT checklist
- Security checklist

**Commit:** `chore: prepare production release`

---

## 18. Urutan yang bisa dimulai sekarang

Tanpa menunggu data client:

```text
Slice 0 → 1 → 2 → 3 → 4
```

Slice 5 dan 6 memakai fake provider dulu. Integrasi sandbox setelah akun tersedia.

Slice 8 bisa mulai setelah 4. Slice 9 setelah 7. Slice 10 setelah 4. Slice 11 setelah 10.

---

## 19. Yang tidak boleh dilakukan

- Menunggu data client untuk mulai setup.
- Memasukkan API key ke Git.
- Memanggil provider langsung dari controller.
- Mengubah stok dari Blade.
- Mengandalkan tombol tersembunyi untuk authorization.
- Menghapus order/payment/mutasi stok.
- Memakai `migrate:fresh` di production.
- Menambah fitur di luar scope tanpa change request.
