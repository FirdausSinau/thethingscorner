# Backend Overview - The Things Corner

**Versi:** 0.1  
**Tanggal:** 9 November 2026  
**Untuk:** anggota tim yang ingin memahami atau mengambil bagian backend

Dokumen ini menjelaskan cara kerja backend Laravel di project ini. Sebagian service masih berupa rencana sampai slice terkait dikerjakan.

---

## 1. Prinsip utama

- Controller tipis. Validasi di FormRequest, aturan bisnis di service/action.
- Authorization dicek di server, bukan hanya menyembunyikan tombol di Blade.
- Uang dan stok dihitung di server.
- Perubahan stok dan pembuatan order memakai database transaction.
- Semua aturan penting punya feature test.
- Jangan membuat query yang sama di banyak controller tanpa scope atau service.

Alur umum:

```text
Browser
  -> routes/web.php, auth.php, storefront.php, admin.php
  -> middleware auth, role
  -> FormRequest validasi + authorize
  -> Controller
  -> Service / Action
  -> Model / Eloquent
  -> PostgreSQL
  -> Blade atau redirect
```

Contoh nyata yang sudah ada:

```text
POST /admin/admins
  -> routes/admin.php
  -> middleware auth + role:admin_master
  -> StoreAdminRequest
  -> Admin\UserController@store
  -> User model
  -> redirect + flash message
```

---

## 2. Tanggung jawab folder

| Folder | Isi | Catatan |
|---|---|---|
| `routes/` | Definisi URL per domain | `web.php` hanya include route lain |
| `app/Http/Middleware/` | Auth, role, active user | `EnsureRole` sudah ada |
| `app/Http/Requests/` | Validasi dan authorization request | Satu FormRequest per aksi penting |
| `app/Http/Controllers/` | Orchestrasi request | Jangan taruh aturan stok/pembayaran di sini |
| `app/Services/` | Aturan bisnis yang dipakai ulang | Cart, checkout, stok, payment, shipping, import |
| `app/Models/` | Relasi, cast, scope | Schema dan relasi dipegang Firdaus |
| `app/Policies/` | Kepemilikan data | Contoh: buyer hanya boleh melihat order sendiri |
| `app/Events/`, `Listeners/` | Efek samping | Notifikasi, log status |
| `app/Observers/` | Audit model | Perubahan stok dan status |
| `app/Jobs/` | Pekerjaan async | Email, import besar, webhook |
| `database/migrations/` | Schema | Satu pintu, review Firdaus |
| `database/seeders/` | Data demo/awal | `DemoUserSeeder` sudah ada |
| `tests/Feature/` | Test per domain | Wajib untuk aturan penting |

---

## 3. Modul backend dan service yang direncanakan

| Modul | Service/komponen | Tanggung jawab |
|---|---|---|
| Auth dan role | `AuthController`, `EnsureRole` | Login, register, reset password, akses per role |
| Katalog | `Product`, `ProductVariant`, scope | Produk aktif, varian, stok tersedia, pencarian, filter |
| Cart | `CartService` | Tambah/ubah/hapus item, validasi qty, tidak mengunci stok |
| Checkout | `CheckoutService` | Transaksi, row lock, cek stok, buat order, reservasi |
| Stok | `StockService` | Mutasi stok, `stock_movements`, cegah stok negatif |
| Pesanan | `OrderService`, state machine | Transisi status yang sah, log status, auto-selesai |
| Pembayaran | `PaymentService`, gateway adapter | Buat pembayaran, verifikasi transfer, webhook idempotent |
| Pengiriman | `ShippingService`, Biteship adapter | Ongkir, buat shipment, resi, tracking |
| Custom PO | `CustomPoService` | Quote, DP, status produksi, pelunasan |
| Review | `ReviewService` | Eligibility 30 hari, satu review per order item, balasan, moderasi |
| Shopee | `ShopeeImportService` | Parse CSV, validasi SKU, preview, commit, rekonsiliasi |
| Laporan | `ReportService` | Query agregasi, export CSV/Excel |

Service dibuat saat slice-nya dikerjakan. Jangan membuat semua service di awal hanya karena terlihat rapi.

---

## 4. Alur kritis

### 4.1 Checkout dan reservasi stok

```text
1. Validasi cart masih milik user.
2. Buka database transaction.
3. Lock baris varian yang dibeli (SELECT ... FOR UPDATE).
4. Cek stok tersedia = stock_on_hand - stock_reserved.
5. Hitung ulang harga di server.
6. Buat order dan order_items dengan snapshot harga.
7. Buat stock_reservations dengan expires_at.
8. Tambah stock_reserved.
9. Commit.
10. Buat pembayaran di luar transaction.
```

Aturan:

- Cart tidak mengunci stok.
- Reservasi baru dibuat saat checkout.
- QRIS/VA 30 menit, transfer manual 60 menit.
- Kalau checkout gagal, semua perubahan stok dibatalkan.
- Harga dari request tidak dipercaya. Selalu ambil dari database.

### 4.2 Webhook pembayaran

```text
1. Terima request tanpa CSRF.
2. Verifikasi signature/token.
3. Cek external_id, jangan proses dua kali.
4. Update payment.
5. Ubah status order sesuai state machine.
6. Commit stok atau lepaskan reservasi.
7. Catat log.
8. Kirim notifikasi.
```

Webhook harus idempotent. Request duplikat tidak boleh menggandakan stok atau pembayaran.

### 4.3 Perubahan stok manual

Semua perubahan stok lewat `StockService`:

```text
stock_on_hand berubah
  -> tulis stock_movements (qty, stock_before, stock_after, sumber, actor)
  -> commit
```

Tidak boleh ada update stok langsung dari controller tanpa catatan mutasi.

### 4.4 Import CSV Shopee

```text
1. Upload file.
2. Parse dan validasi header.
3. Cocokkan SKU ke product_variants.
4. Tampilkan preview baris valid dan error.
5. Commit dalam transaction.
6. Buat stock_movements dan data rekonsiliasi.
7. Simpan riwayat import.
```

Import harus bisa diulang tanpa menggandakan data. Gunakan kunci unik dari order Shopee.

---

## 5. Database dan transaksi

- Database: PostgreSQL 16+.
- Migration hanya lewat review Firdaus.
- Gunakan foreign key dan index yang sudah direncanakan di `docs/erd.md`.
- Histori pesanan, pembayaran, dan stok tidak dihapus.
- Produk/varian yang sudah dipakai transaksi dinonaktifkan, bukan dihapus.
- Gunakan `DB::transaction()` untuk checkout, pembayaran, stok, dan import.
- Gunakan `lockForUpdate()` saat membaca stok yang akan diubah.
- Test memakai SQLite in-memory; migrasi dan test suite juga diuji di PostgreSQL.

---

## 6. Authorization dan keamanan

- Route admin memakai middleware `auth` + `role:admin,admin_master`.
- Route khusus Admin Master memakai `role:admin_master`.
- Data milik pembeli dicek dengan Policy, bukan hanya `user_id` di query.
- FormRequest `authorize()` dipakai untuk aksi yang bergantung pada role/ownership.
- Jangan percaya `role`, `price`, `stock`, atau `status` dari form.
- Semua form memakai CSRF.
- Login, register, dan reset password memakai rate limit.
- File privat disimpan di disk private dan diakses lewat route ber-Policy.

---

## 7. Testing

Test wajib untuk:

- authorization dan role;
- validasi input;
- perhitungan harga;
- stok habis dan qty berlebih;
- reservasi kedaluwarsa;
- webhook valid, duplikat, dan palsu;
- satu review per order item;
- import CSV duplikat.

Perintah:

```powershell
php artisan test
npm run build
```

Test feature memakai `RefreshDatabase`. Jangan menulis test yang hanya mengecek halaman tampil tanpa mengecek aturan bisnisnya.

---

## 8. Jalur belajar backend untuk anggota tim

Urutan yang disarankan:

### Tahap 1 - pahami alur request

Baca:

- `routes/auth.php`
- `app/Http/Requests/LoginRequest.php`
- `app/Http/Controllers/AuthController.php`
- `app/Models/User.php`
- `tests/Feature/AuthTest.php`

Lalu buat satu CRUD admin sederhana dengan FormRequest dan feature test.

### Tahap 2 - stok dan transaksi

Kerjakan:

- `StockService`
- `stock_movements`
- test stok tidak boleh negatif
- test mutasi tercatat

Ini cara paling aman untuk belajar transaction tanpa menyentuh uang.

### Tahap 3 - import CSV

Kerjakan:

- parsing CSV
- validasi SKU
- preview error
- idempotency
- test import duplikat

### Tahap 4 - laporan

Kerjakan query agregasi dan export. Perhatikan index dan N+1.

Payment dan checkout ditunda sampai tahap 1-2 stabil. Firdaus tetap menjadi reviewer untuk schema dan transaction.

---

## 9. Status implementasi

Sudah ada:

- Laravel 13, PostgreSQL, Vite, Tailwind.
- Auth, role, middleware, dashboard, reset password.
- Kelola Admin Biasa oleh Admin Master.
- Feature test auth dan authorization.

Belum ada:

- katalog, cart, checkout, payment, shipping, PO, review, stok, Shopee, laporan.

Rencana per slice ada di `docs/implementation-plan-fase-1.md`.
