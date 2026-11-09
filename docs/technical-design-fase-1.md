# Technical Design Fase 1 — The Things Corner

**Versi:** 0.1  
**Tanggal:** 6 November 2026  
**Status:** Draft untuk review  
**Referensi:** PRD Fase 1 v0.2, ERD v0.1, RFP v0.3

---

## 1. Ringkasan arsitektur

Fase 1 memakai **satu aplikasi Laravel** dengan Blade, bukan SPA terpisah.

```text
Browser
  → Nginx / PHP-FPM
  → Laravel route
  → Middleware (auth, role, csrf)
  → Form Request
  → Controller
  → Service
  → Model / Eloquent
  → PostgreSQL
  → Blade / JSON
```

Layanan pendukung:

- queue database untuk job;
- scheduler untuk reservasi kedaluwarsa dan auto-selesai;
- local storage untuk foto publik dan file privat;
- Cloudflare untuk DNS/CDN/SSL;
- adapter provider untuk payment dan shipping.

Prinsip:

- Controller tipis, Service memegang aturan bisnis.
- Validasi input di Form Request.
- Authorization di Policy/Gate, bukan hanya di Blade.
- Mutasi stok selalu lewat Service dan transaksi database.
- Provider tidak dipanggil langsung dari controller.
- Selalu ada fallback manual.

---

## 2. Struktur folder yang dituju

```text
app/
├── Enums/
│   ├── OrderStatus.php
│   ├── PaymentStatus.php
│   ├── ShipmentStatus.php
│   ├── CustomPoStatus.php
│   └── UserRole.php
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── OrderController.php
│   │   ├── CustomPoController.php
│   │   ├── ReviewController.php
│   │   ├── NotificationController.php
│   │   ├── PaymentCallbackController.php
│   │   ├── BiteshipWebhookController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── ProductController.php
│   │       ├── StockController.php
│   │       ├── OrderController.php
│   │       ├── CustomPoController.php
│   │       ├── ReviewController.php
│   │       ├── ShopeeImportController.php
│   │       ├── ReportController.php
│   │       └── UserController.php
│   ├── Requests/
│   ├── Middleware/
│   └── Policies/
├── Models/
├── Services/
│   ├── CartService.php
│   ├── CheckoutService.php
│   ├── StockService.php
│   ├── OrderService.php
│   ├── PaymentService.php
│   ├── ShippingService.php
│   ├── CustomPoService.php
│   ├── ReviewService.php
│   ├── ShopeeImportService.php
│   └── ReportService.php
├── Services/Payment/
│   ├── PaymentGateway.php
│   ├── FakePaymentGateway.php
│   └── MidtransGateway.php
├── Services/Shipping/
│   ├── ShippingProvider.php
│   ├── FakeShippingProvider.php
│   └── BiteshipProvider.php
├── Observers/
│   ├── OrderObserver.php
│   └── ReviewObserver.php
└── Jobs/
```

---

## 3. Alur request

### 3.1 Checkout

```text
POST /checkout
→ auth
→ CheckoutRequest
→ CheckoutController@store
→ CheckoutService
    - validasi stok
    - hitung total di server
    - buat order
    - buat stock_reservations
    - buat payment
→ redirect ke halaman pembayaran
```

### 3.2 Pembayaran gateway

```text
Buyer bayar di Midtrans/Xendit
→ provider kirim callback ke /webhooks/payment/{provider}
→ verifikasi signature/token
→ PaymentService
    - cek external_id unik
    - update payment
    - ubah order menjadi DIBAYAR
    - konversi reservasi menjadi stok keluar
→ 200 OK
```

Jika callback tidak datang:

- admin dapat verifikasi manual;
- scheduler menandai pembayaran kedaluwarsa;
- stok dilepas.

### 3.3 Biteship

```text
Admin klik "Buat Pengiriman"
→ ShippingService
    - hitung ongkir
    - buat shipment
    - simpan resi
→ Biteship webhook /webhooks/biteship
→ ShippingService update status
→ buyer lihat tracking teks
```

Jika Biteship gagal:

- fallback flat ongkir;
- input resi manual;
- checkout tetap bisa.

### 3.4 Custom PO

```text
Buyer isi form PO
→ CustomPoService membuat PO status DIAJUKAN
→ Admin Master memberi harga dan DP
→ Buyer bayar DP
→ Admin Biasa update PRODUKSI
→ pelunasan
→ DIKIRIM
→ SELESAI
```

---

## 4. State machine

### 4.1 Order

```text
BARU
→ MENUNGGU_PEMBAYARAN
→ DIBAYAR
→ DIKEMAS
→ DIKIRIM
→ SELESAI

Dari BARU / MENUNGGU_PEMBAYARAN:
→ DIBATALKAN
→ KEDALUWARSA
```

Aturan:

- `SELESAI` hanya dari `DIKIRIM`, oleh buyer atau auto-selesai 7 hari.
- `DIBATALKAN` hanya sebelum `DIKEMAS`.
- `KEDALUWARSA` hanya jika pembayaran tidak selesai.
- Refund dicatat terpisah; tidak mengubah riwayat.

### 4.2 Payment

```text
PENDING
→ PAID
→ REFUNDED

PENDING
→ EXPIRED
→ FAILED
```

### 4.3 Shipment

```text
PENDING
→ PICKUP
→ IN_TRANSIT
→ DELIVERED

IN_TRANSIT
→ RETURNED
```

### 4.4 Custom PO

```text
DIAJUKAN
→ DITINJAU
→ MENUNGGU_DP
→ PRODUKSI
→ SELESAI_PRODUKSI
→ MENUNGGU_PELUNASAN
→ DIKIRIM
→ SELESAI

Bisa DIBATALKAN sebelum produksi selesai.
```

### 4.5 Review

```text
PUBLISHED
→ HIDDEN
```

Review tidak dihapus. Balasan toko disimpan di `reply_text`.

Semua transisi status harus lewat Service, bukan `update()` langsung dari controller.

---

## 5. Route map

### 5.1 Public

| Method | URL | Handler |
|---|---|---|
| GET | `/` | LandingController@index |
| GET | `/products` | ProductController@index |
| GET | `/products/{product:slug}` | ProductController@show |
| GET | `/login` | AuthController@showLogin |
| POST | `/login` | AuthController@login |
| GET | `/register` | AuthController@showRegister |
| POST | `/register` | AuthController@register |
| POST | `/logout` | AuthController@logout |
| GET | `/forgot-password` | PasswordResetController@request |
| POST | `/forgot-password` | PasswordResetController@send |
| GET | `/reset-password/{token}` | PasswordResetController@form |
| POST | `/reset-password` | PasswordResetController@reset |

### 5.2 Buyer

| Method | URL | Handler |
|---|---|---|
| GET | `/cart` | CartController@index |
| POST | `/cart/items` | CartController@store |
| PATCH | `/cart/items/{item}` | CartController@update |
| DELETE | `/cart/items/{item}` | CartController@destroy |
| GET | `/checkout` | CheckoutController@index |
| POST | `/checkout` | CheckoutController@store |
| GET | `/orders` | OrderController@index |
| GET | `/orders/{order}` | OrderController@show |
| POST | `/orders/{order}/cancel` | OrderController@cancel |
| POST | `/orders/{order}/confirm` | OrderController@confirmReceived |
| GET | `/orders/{order}/review` | ReviewController@create |
| POST | `/orders/{order}/review` | ReviewController@store |
| GET | `/custom-pos` | CustomPoController@index |
| GET | `/custom-pos/create` | CustomPoController@create |
| POST | `/custom-pos` | CustomPoController@store |
| GET | `/custom-pos/{po}` | CustomPoController@show |
| GET | `/notifications` | NotificationController@index |
| POST | `/notifications/{notification}/read` | NotificationController@read |
| GET | `/profile` | ProfileController@edit |
| PATCH | `/profile` | ProfileController@update |
| GET | `/addresses` | AddressController@index |
| POST | `/addresses` | AddressController@store |
| PATCH | `/addresses/{address}` | AddressController@update |
| DELETE | `/addresses/{address}` | AddressController@destroy |

### 5.3 Webhook

| Method | URL | Handler |
|---|---|---|
| POST | `/webhooks/payment/midtrans` | PaymentCallbackController@midtrans |
| POST | `/webhooks/payment/xendit` | PaymentCallbackController@xendit |
| POST | `/webhooks/biteship` | BiteshipWebhookController@handle |

Webhook tidak memakai CSRF, tetapi wajib verifikasi signature/token.

### 5.4 Admin Biasa

| Method | URL | Handler |
|---|---|---|
| GET | `/admin` | Admin\DashboardController@index |
| GET | `/admin/products` | Admin\ProductController@index |
| GET | `/admin/products/create` | Admin\ProductController@create |
| POST | `/admin/products` | Admin\ProductController@store |
| GET | `/admin/products/{product}/edit` | Admin\ProductController@edit |
| PATCH | `/admin/products/{product}` | Admin\ProductController@update |
| DELETE | `/admin/products/{product}` | Admin\ProductController@destroy |
| GET | `/admin/stock` | Admin\StockController@index |
| GET | `/admin/stock/{variant}` | Admin\StockController@show |
| POST | `/admin/stock/{variant}/movements` | Admin\StockController@storeMovement |
| POST | `/admin/stock/adjustment` | Admin\StockController@adjust |
| GET | `/admin/orders` | Admin\OrderController@index |
| GET | `/admin/orders/{order}` | Admin\OrderController@show |
| POST | `/admin/orders/{order}/verify-payment` | Admin\OrderController@verifyPayment |
| POST | `/admin/orders/{order}/process` | Admin\OrderController@process |
| POST | `/admin/orders/{order}/ship` | Admin\OrderController@ship |
| POST | `/admin/orders/{order}/tracking` | Admin\OrderController@tracking |
| POST | `/admin/orders/{order}/cancel` | Admin\OrderController@cancel |
| GET | `/admin/custom-pos` | Admin\CustomPoController@index |
| GET | `/admin/custom-pos/{po}` | Admin\CustomPoController@show |
| POST | `/admin/custom-pos/{po}/quote` | Admin\CustomPoController@quote |
| POST | `/admin/custom-pos/{po}/status` | Admin\CustomPoController@status |
| POST | `/admin/custom-pos/{po}/payment` | Admin\CustomPoController@payment |
| GET | `/admin/reviews` | Admin\ReviewController@index |
| POST | `/admin/reviews/{review}/reply` | Admin\ReviewController@reply |
| GET | `/admin/shopee/import` | Admin\ShopeeImportController@create |
| POST | `/admin/shopee/import` | Admin\ShopeeImportController@store |
| GET | `/admin/shopee/reconciliation` | Admin\ShopeeImportController@reconciliation |
| GET | `/admin/reports/sales` | Admin\ReportController@sales |
| GET | `/admin/reports/stock` | Admin\ReportController@stock |
| GET | `/admin/reports/pos` | Admin\ReportController@pos |
| GET | `/admin/reports/export/{type}` | Admin\ReportController@export |

### 5.5 Admin Master

| Method | URL | Handler |
|---|---|---|
| GET | `/admin/users` | Admin\UserController@index |
| POST | `/admin/users` | Admin\UserController@store |
| PATCH | `/admin/users/{user}/toggle` | Admin\UserController@toggle |
| POST | `/admin/users/{user}/reset-password` | Admin\UserController@resetPassword |
| POST | `/admin/reviews/{review}/hide` | Admin\ReviewController@hide |
| POST | `/admin/orders/{order}/refund` | Admin\OrderController@refund |
| GET | `/admin/settings` | Admin\SettingController@edit |
| PATCH | `/admin/settings` | Admin\SettingController@update |

---

## 6. Authorization

### 6.1 Middleware

- `auth` untuk semua halaman pembeli dan admin.
- `role:admin_master,admin` untuk area admin.
- `role:admin_master` untuk akun admin, refund, dan settings.

### 6.2 Gate

| Gate | Role |
|---|---|
| `manage-admins` | admin_master |
| `manage-products` | admin_master, admin |
| `manage-stock` | admin_master, admin |
| `process-orders` | admin_master, admin |
| `verify-payments` | admin_master, admin |
| `manage-pos` | admin_master, admin |
| `reply-review` | admin_master, admin |
| `hide-review` | admin_master |
| `view-reports` | admin_master |
| `manage-settings` | admin_master |
| `refund-order` | admin_master |

### 6.3 Policy

| Policy | Aturan |
|---|---|
| `OrderPolicy@view` | Pemilik atau admin |
| `OrderPolicy@cancel` | Pemilik dan status `BARU`/`MENUNGGU_PEMBAYARAN` |
| `OrderPolicy@confirm` | Pemilik dan status `DIKIRIM` |
| `CustomPoPolicy@view` | Pemilik atau admin |
| `CustomPoPolicy@update` | Pemilik saat `DIAJUKAN`; admin sesuai status |
| `ReviewPolicy@create` | Pemilik order item, status `SELESAI`, ≤ 30 hari, belum ada review |
| `ReviewPolicy@reply` | admin_master/admin |
| `ReviewPolicy@hide` | admin_master |
| `PaymentPolicy@verify` | admin_master/admin |
| `ShipmentPolicy@update` | admin_master/admin |

---

## 7. Tanggung jawab lapisan

| Lapisan | Tugas | Tidak boleh |
|---|---|---|
| Route | Mapping URL dan middleware | Business rule |
| Form Request | Validasi bentuk input, `authorize()` | Query data kompleks |
| Controller | Orkestrasi request/response | Aturan stok/transisi panjang |
| Service | Business rule, transaksi, state transition | Render HTML |
| Model | Relasi, cast, scope | Response HTTP |
| Policy | Izin aktor terhadap resource | Mutasi data |
| Observer | Log perubahan penting | Menjadi satu-satunya penjaga izin |
| Blade | Presentasi | Query database |

---

## 8. Kontrak integrasi

### 8.1 Payment gateway

Interface:

```text
PaymentGateway
  create(Order $order): PaymentInstruction
  handleCallback(Request $request): PaymentEvent
```

Implementasi:

- `FakePaymentGateway` untuk lokal;
- `MidtransGateway` untuk sandbox/production.

Aturan:

- `external_id` unik.
- Verifikasi signature/token.
- Idempotent: callback duplikat tidak menambah stok keluar.
- Simpan log request/response tanpa secret.
- Status mapping:
  - `settlement`/`capture` → `PAID`;
  - `pending` → `PENDING`;
  - `expire` → `EXPIRED`;
  - `deny`/`cancel` → `FAILED`.
- Jika gateway gagal, transfer manual tetap tersedia.

### 8.2 Biteship

Interface:

```text
ShippingProvider
  rates(Address $address, array $items): array
  createShipment(Order $order): ShipmentResult
  handleTracking(array $payload): TrackingEvent
```

Aturan:

- Simpan `tracking_number`, kurir, biaya, status.
- Rate limit dan timeout.
- Fallback flat per zona.
- Jika webhook gagal, admin dapat update resi manual.

### 8.3 `wa.me`

Tidak ada API. Buat helper:

```text
WhatsAppLink::forOrder(Order $order, string $template): string
```

Contoh:

```text
https://wa.me/628xxxxxxxxxx?text=Halo%20Budi%2C%20pesanan%20INV-2026-0001...
```

Tombol tersedia di:

- detail pesanan pembeli;
- detail pesanan admin;
- detail PO;
- halaman retur.

### 8.4 Shopee CSV

Kolom minimal:

```text
order_sn, order_date, sku, product_name, quantity, status, buyer_name, city, total
```

Aturan:

- Validasi header.
- Validasi SKU ada.
- Tolak `order_sn + sku` duplikat.
- Simpan hasil import dan baris gagal.
- Hasil dipakai untuk laporan rekonsiliasi.
- Tidak mengubah stok dua kali.

### 8.5 Email

- Laravel Mail, queue.
- Dipakai untuk reset password dan invoice opsional.
- Jika email gagal, admin tetap bisa reset manual.

### 8.6 Storage

- `public` disk: foto produk, foto review.
- `private` disk: bukti transfer, referensi PO.
- Akses file privat lewat route ber-Policy.
- Optimasi gambar: resize, WebP, lazy loading.

---

## 9. Background job dan scheduler

| Job | Frekuensi | Fungsi |
|---|---|---|
| `ExpireStockReservations` | tiap menit | Lepas reservasi kedaluwarsa |
| `ExpireUnpaidOrders` | tiap 5 menit | Batalkan order belum dibayar |
| `AutoCompleteOrders` | harian | Selesai 7 hari setelah dikirim |
| `SendNotification` | queue | Notifikasi web/email |
| `RetryWebhook` | queue | Retry callback gagal |
| `ImportShopeeCsv` | queue | Import file besar |
| `GenerateDailyReport` | harian | Rekap penjualan harian |

Scheduler production:

```cron
* * * * * cd /path/app && php artisan schedule:run >> /dev/null 2>&1
```

Queue worker:

```bash
php artisan queue:work --tries=3 --timeout=60
```

---

## 10. Keamanan

- CSRF untuk semua form.
- Rate limit login dan endpoint publik.
- Password hash bcrypt/argon2.
- Validasi upload: MIME, ekstensi, ukuran.
- File privat tidak bisa diakses langsung.
- Policy server-side untuk setiap resource.
- Webhook signature/token.
- Idempotency untuk payment dan tracking.
- Jangan simpan kartu atau credential mentah.
- Secret di `.env`, bukan Git.
- Log tidak memuat password, token, atau data sensitif.
- Security review dengan `laravel-owasp-security`.

---

## 11. Error handling dan fallback

| Kegagalan | Perilaku |
|---|---|
| Payment gateway down | Tampilkan transfer manual |
| Webhook terlambat | Status tetap pending; admin verifikasi |
| Biteship down | Flat ongkir; resi manual |
| Email gagal | Admin reset manual |
| `wa.me` gagal | WhatsApp Business manual |
| CSV invalid | Tampilkan baris gagal; tidak import sebagian |
| Stok habis saat checkout | Tolak order; keranjang tetap |

Semua error integrasi dicatat di `integration_logs` tanpa secret.

---

## 12. Testing

### 12.1 Feature test

- Registrasi/login/logout.
- Guest tidak bisa checkout/admin.
- Buyer tidak bisa melihat order orang lain.
- Admin Biasa tidak bisa kelola admin.
- Checkout stok habis.
- Reservasi kedaluwarsa.
- Pembayaran valid/duplikat/palsu.
- Biteship sukses/gagal.
- PO tanpa DP.
- Review ganda/di luar 30 hari.
- Review hide/reply.
- Import CSV duplikat.
- Export laporan.

### 12.2 Unit test

- `StockService` reserve/release/commit.
- `OrderService` transition.
- `CustomPoService` transition.
- `ReviewService` eligibility.
- `ShopeeImportService` duplicate detection.

### 12.3 Browser test

- Katalog → cart → checkout → pembayaran.
- Admin verifikasi transfer.
- Admin input resi.
- Buyer review.
- Admin balas review.
- Retur link ke `wa.me`.

### 12.4 Manual UAT

Gunakan sample data di `docs/data/`. Uji semua status pesanan, PO, review, dan laporan.

---

## 13. Urutan implementasi

| Slice | Isi | Output |
|---|---|---|
| 0 | Setup Laravel, PostgreSQL, Git, staging | Aplikasi dasar jalan |
| 1 | Auth, role, akun admin | Login dan akses terkontrol |
| 2 | Katalog, kategori, varian | Produk tampil |
| 3 | Keranjang persisten | Cart tanpa mengunci stok |
| 4 | Checkout, alamat, ongkir fake | Order dibuat |
| 5 | Payment fake + transfer manual | Pembayaran dan status |
| 6 | Biteship sandbox + tracking | Ongkir dan resi |
| 7 | Pesanan, status, auto-selesai | Alur pesanan lengkap |
| 8 | Custom PO | PO dan DP |
| 9 | Review, balas, moderasi, retur | Ulasan dan CS |
| 10 | Stok, kartu stok, penyesuaian | Audit stok |
| 11 | Shopee CSV, rekonsiliasi | Laporan harian |
| 12 | Laporan dan export | Rekap |
| 13 | Settings, keamanan, QA, deploy | Rilis |

Setiap slice:

- migration/model;
- service/business rule;
- Form Request;
- Policy;
- controller;
- Blade;
- test;
- commit.

---

## 14. Deployment

### 14.1 Lokal

- Laravel + PostgreSQL.
- `php artisan serve`.
- Fake payment/shipping.
- `ngrok` untuk webhook.
- Data dummy dari `docs/data/`.

### 14.2 Staging

- VPS + Nginx + PHP-FPM + PostgreSQL.
- Domain staging + SSL.
- Sandbox provider.
- Queue worker dan scheduler.
- UAT client.

### 14.3 Production

- Domain client.
- Akun provider client.
- Production key.
- Backup harian.
- Monitoring log.
- Webhook URL didaftarkan di dashboard provider.

Perintah deploy:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan queue:restart
```

Jangan jalankan `migrate:fresh` di production.

---

## 15. Keputusan yang masih terbuka

| Topik | Dampak | Default sementara |
|---|---|---|
| Model stok | Skema stok | Satu pool |
| Provider payment | Implementasi adapter | Midtrans |
| Provider shipping | Implementasi adapter | Biteship |
| PO satu/banyak pasang | Tabel PO | Satu pasang per PO |
| Alamat gudang | Ongkir | Bandung |
| Kurir | Ongkir | JNE, J&T, SiCepat |
| Retur | Alur | Manual via `wa.me` |
| Shopee API | Fase 2 | CSV dulu |

---

## 16. Checklist go-live

- [ ] Domain dan HTTPS aktif.
- [ ] `.env` production benar.
- [ ] `APP_DEBUG=false`.
- [ ] Database migration dan seeder aman.
- [ ] Akun demo dinonaktifkan.
- [ ] Payment production key terpasang.
- [ ] Webhook payment terdaftar dan diuji.
- [ ] Biteship production key terpasang.
- [ ] Webhook tracking terdaftar.
- [ ] Storage public/private benar.
- [ ] Queue worker berjalan.
- [ ] Scheduler berjalan.
- [ ] Backup dan restore diuji.
- [ ] Log dan monitoring aktif.
- [ ] UAT client selesai.
