# Sprint 2 - Checkout Contract

**Versi:** 1.0  
**Tanggal:** 11 November 2026  
**Untuk:** Firdaus, Anand, Hafiez  
**Status:** Backend dan UI checkout selesai; admin order index, detail, dan aksi status selesai

Dokumen ini adalah kontrak Slice 4: checkout, order, dan reservasi stok.

---

## 1. Schema

### addresses

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| user_id | bigint | FK users, cascade delete |
| label | string | default Rumah |
| recipient | string | nama penerima |
| phone | string(30) | |
| province | string | |
| city | string | |
| district | string | |
| postal_code | string(10) | |
| detail | text | alamat lengkap |
| is_default | boolean | default false |
| created_at, updated_at | timestamp | |

Index `(user_id, is_default)`. Satu default per user diatur di service/controller, bukan constraint database.

### orders

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| order_code | string(40) | unik, format `TTC-YYYYMMDD-XXXXXX` |
| user_id | bigint | FK users, restrict delete |
| channel | string(20) | `web` / `shopee` |
| status | string(30) | enum OrderStatus |
| payment_method | string(30) | enum PaymentMethod |
| subtotal | bigint | |
| shipping_cost | bigint | default 0, placeholder sampai Slice 6 |
| total | bigint | subtotal + shipping |
| recipient | string | snapshot |
| phone | string(30) | snapshot |
| address | text | snapshot alamat |
| city | string | snapshot |
| postal_code | string(10) | snapshot |
| paid_at | timestamp | nullable |
| completed_at | timestamp | nullable |
| created_at, updated_at | timestamp | |

Index `(user_id, created_at)` dan `(status, created_at)`.

### order_items

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| order_id | bigint | FK orders, cascade delete |
| variant_id | bigint | FK product_variants, restrict delete |
| product_name | string | snapshot |
| sku | string | snapshot |
| color | string(50) | snapshot |
| size | string(10) | snapshot |
| price | bigint | snapshot harga satuan |
| qty | integer | minimal 1 |
| subtotal | bigint | price * qty |
| created_at, updated_at | timestamp | |

### stock_reservations

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| order_id | bigint | FK orders, cascade delete |
| variant_id | bigint | FK product_variants, restrict delete |
| qty | integer | minimal 1 |
| expires_at | timestamp | |
| status | string(20) | active/released/consumed/expired |
| created_at, updated_at | timestamp | |

Index `(expires_at, status)`, `order_id`, `variant_id`.

### order_status_logs

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| order_id | bigint | FK orders, cascade delete |
| from_status | string(30) | nullable |
| to_status | string(30) | |
| actor_id | bigint | FK users, nullable, null on delete |
| note | text | nullable |
| created_at | timestamp | append-only |

Index `(order_id, created_at)`.

---

## 2. Enum

### OrderStatus

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

### OrderChannel

```text
web
shopee
```

### PaymentMethod

```text
qris
va
ewallet
manual_transfer
```

Window reservasi:

- QRIS, VA, e-wallet: 30 menit.
- Transfer manual: 60 menit.

Sementara QRIS dipakai 30 detik untuk pengujian lewat `QRIS_RESERVATION_SECONDS=30`. Kembalikan ke `1800` detik (30 menit) sebelum produksi.

### ReservationStatus

```text
active
released
consumed
expired
```

---

## 3. Model

```php
$address->user;

$order->user;
$order->items;
$order->reservations;
$order->reservation_expires_at; // accessor, min expires_at

$item->order;
$item->variant;

$reservation->order;
$reservation->variant;
StockReservation::active()->get();
```

---

## 4. CheckoutService

`App\Services\CheckoutService`

```php
$order = app(CheckoutService::class)->placeOrder(
    $user,
    $address,
    PaymentMethod::ManualTransfer,
    shippingCost: 0
);
```

Alur di dalam transaction:

1. Pastikan address milik user.
2. Ambil cart items dengan lock.
3. Lock varian dengan `SELECT ... FOR UPDATE`.
4. Cek produk aktif dan stok tersedia.
5. Hitung ulang harga dari database.
6. Buat order dan order items snapshot.
7. Buat stock reservation dengan expires_at.
8. Tambah `product_variants.stock_reserved`.
9. Hapus cart items.
10. Commit.

Kalau gagal, semua perubahan di-rollback. Stok fisik `stock` tidak berubah.

---

## 5. Route yang perlu dibuat

### Checkout, milik Anand

| Method | URL | Name | Handler |
|---|---|---|---|
| GET | `/checkout` | `checkout.index` | `CheckoutController@index` |
| POST | `/checkout` | `checkout.store` | `CheckoutController@store` |

Semua memakai middleware `auth`.

Form checkout minimal:

- pilih alamat default atau isi alamat;
- pilih metode pembayaran;
- ringkasan cart dan ongkir placeholder;
- tombol buat pesanan.

Controller:

1. Validasi alamat dan `payment_method`.
2. Simpan/perbarui alamat default user.
3. Panggil `CheckoutService`.
4. Redirect ke halaman pesanan atau pembayaran Slice 5.

### Admin order, milik Hafiez

| Method | URL | Name | Handler |
|---|---|---|---|
| GET | `/admin/orders` | `admin.orders.index` | `Admin\OrderController@index` |
| GET | `/admin/orders/{order}` | `admin.orders.show` | `Admin\OrderController@show` |
| PATCH | `/admin/orders/{order}/status` | `admin.orders.status` | `Admin\OrderController@updateStatus` |
| PATCH | `/admin/orders/bulk-status` | `admin.orders.bulk-status` | `Admin\OrderController@bulkStatus` |

Aksi status memakai `OrderStatusService`. Perubahan status dan stok berjalan dalam satu transaksi, memakai urutan lock order, reservasi, lalu varian. Setiap perubahan dicatat di `order_status_logs`. Aksi auto-selesai, refund, dan aksi pengiriman menyusul di Slice 7.

---

## 6. Data untuk Blade

Checkout index:

```php
[
    'items' => $summary['items'],
    'subtotal' => $summary['subtotal'],
    'count' => $summary['count'],
    'defaultAddress' => $defaultAddress,
    'paymentMethods' => PaymentMethod::cases(),
]
```

Admin order index:

```php
[
    'orders' => $orders, // paginator, eager load user, items
    'counts' => $counts, // jumlah per status
    'statuses' => OrderStatus::cases(),
    'sumTotal' => $sumTotal,
]
```

Admin order detail memuat `user`, `items`, `reservations.variant.color.product`, dan `statusLogs.actor`.

---

## 7. Reservasi kedaluwarsa

Command:

```powershell
php artisan reservations:release-expired
```

Scheduler sudah berjalan setiap menit di `routes/console.php`.

Yang dilakukan:

- cari reservation `active` dengan `expires_at <= now()`;
- lock order, reservasi, lalu varian (urutan yang sama dengan `OrderStatusService`);
- baca ulang status reservasi di bawah lock supaya tidak dilepas dua kali;
- kurangi `stock_reserved` varian;
- ubah reservation menjadi `expired`;
- kalau semua reservasi order sudah tidak aktif dan order masih `MENUNGGU_PEMBAYARAN`, ubah order menjadi `KEDALUWARSA` dan catat di `order_status_logs`.

---

## 8. Aturan penting

- Cart tidak mengunci stok.
- Reservasi dibuat hanya saat checkout.
- Stok fisik tidak berubah saat checkout.
- `stock_reserved` bertambah saat checkout dan berkurang saat expired/paid.
- Harga dan stok dihitung ulang di server.
- Alamat dan item order disimpan sebagai snapshot.
- Payment gateway belum diintegrasikan. Status pembayaran dan halaman pembayaran masuk Slice 5.
- Ongkir masih placeholder. Biteship masuk Slice 6.
- Hanya Firdaus yang migrate ke Neon.

---

## 9. Test

`tests/Feature/CheckoutServiceTest.php`

- checkout membuat order, item, dan reservasi;
- QRIS expiry 30 menit;
- cart kosong ditolak;
- stok tidak cukup membatalkan seluruh checkout;
- alamat user lain ditolak;
- reservasi kedaluwarsa melepas stok dan mengubah order menjadi kedaluwarsa.

Status service: 6 test lulus, 32 assertions.  
Test UI cart/checkout/order: 7 test lulus, 32 assertions.  
Test admin order: 15 test, termasuk render semua status, otorisasi, transisi stok, pembatalan setelah bayar, pencarian, bulk status, dan audit log.  
Test admin produk: 8 test, termasuk render halaman, nonaktifkan, guard hapus varian/warna, dan validasi stok.  
Total suite: 104 test lulus, 296 assertions. Termasuk test QRIS yang kedaluwarsa setelah 30 detik.

---

## 10. Yang belum

- halaman pembayaran;
- integrasi Midtrans/Xendit;
- shipment dan resi;
- auto-selesai, refund, dan aksi pengiriman;
- `stock_movements` untuk audit perubahan stok (Slice 10).

