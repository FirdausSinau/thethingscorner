# Decision Log — The Things Corner

**Versi:** 0.8  
**Tanggal:** 12 November 2026  
**Status:** Draft  
**Referensi:** RFP v0.3, PRD Fase 1 v0.2

---

## 1. Cara memakai

Status:

- **Confirmed** — sudah disepakati untuk Fase 1.
- **Assumption** — dipakai sementara sampai client memberi jawaban.
- **TBD** — keputusan client, belum dikunci.
- **Deferred** — masuk Fase 2 atau daftar pertimbangan.

Aturan:

- Jangan hardcode keputusan `Assumption` atau `TBD` di Blade/controller.
- Simpan di config, settings, atau kolom database.
- Setiap perubahan keputusan harus memperbarui dokumen ini dan PRD.
- Jika perubahan menyentuh skema, tambahkan migration baru, jangan edit migration lama di production.

---

## 2. Ringkasan keputusan

| ID | Topik | Keputusan / asumsi | Status | Dampak jika berubah | Pemilik |
|---|---|---|---|---|---|
| D-01 | Bentuk produk | Toko sederhana, bukan marketplace besar | Confirmed | Arah UI dan scope | Tim + client |
| D-02 | Role | Pembeli, Admin Biasa, Admin Master | Confirmed | Authorization dan UI | Client |
| D-03 | Registrasi | Pembeli daftar sendiri; Admin Master dari seeder; Admin Biasa dibuat Admin Master; guest checkout tidak ada | Confirmed | Auth dan route | Tim |
| D-04 | Keranjang | Persisten, tidak mengunci stok | Confirmed | Cart dan reservasi | Tim |
| D-05 | Reservasi stok | Saat checkout: 30 menit QRIS/VA, 60 menit transfer | Confirmed | Stok dan scheduler | Tim |
| D-06 | Payment gateway | Midtrans sebagai default; Xendit alternatif; transfer manual fallback | Assumption | Adapter payment | Client |
| D-07 | Shipping | Biteship; fallback flat/manual | Confirmed | Adapter shipping | Client |
| D-08 | DP PO | 50% dari harga PO | Assumption | Nilai DP dan invoice | Client |
| D-09 | WhatsApp | `wa.me` saja; WA API fase 2 | Confirmed | Notifikasi dan CS | Tim |
| D-10 | Notifikasi | Pusat notifikasi web; email opsional | Confirmed | UI notifikasi | Tim |
| D-11 | Review | 30 hari, satu per order item, balas satu kali, moderasi Admin Master | Confirmed | Tabel review | Client |
| D-12 | Retur | Manual via CS/`wa.me`; tidak ada refund otomatis | Confirmed | Alur CS | Client |
| D-13 | Alamat | Tabel alamat per user; UI fase 1 satu default | Confirmed | Skema alamat | Tim |
| D-14 | Media | Local storage + Cloudflare; Cloudinary fase 2 | Confirmed | Storage | Tim |
| D-15 | Shopee | CSV phase 1; API phase 2 | Confirmed | Import/reconciliation | Client |
| D-16 | Model stok | Satu pool fisik | Assumption | Skema stok | Client |
| D-17 | Provider payment final | Midtrans atau Xendit | TBD | Adapter dan akun | Client |
| D-18 | DP final | 50% atau lain | TBD | `dp_amount` | Client |
| D-19 | Gudang/kurir | Bandung; JNE/J&T/SiCepat | Assumption | Ongkir dan pickup | Client |
| D-20 | Data client | Belum tersedia; pakai sample data | TBD | Seeding dan UAT | Client |
| D-21 | Akun provider | Client memiliki akun merchant; developer integrasi | Confirmed | Legal dan settlement | Client |
| D-22 | Deployment | Staging lalu production; domain/host belum dipilih | Assumption | Infrastruktur | Client + tim |
| D-23 | Desain | Sederhana, jelas, tanpa animasi berat | Confirmed | UI | Tim |
| D-24 | Fitur ditunda | WA API, Google Maps, analytics, kupon, COD, loyalty, Sentry, PDF, CAPTCHA, Cloudinary, chat, Q&A, wishlist | Deferred | Scope | Client |
| D-25 | Perubahan scope | Lewat change request; fitur baru masuk daftar fase 2 | Confirmed | Timeline | Tim + client |
| D-26 | Database | PostgreSQL 16+ untuk local, staging, dan production | Confirmed | Config `.env`, backup, deployment | Tim |
| D-27 | Diskon | Ditunda sampai aturan bisnis dan perhitungan jelas | Deferred | Order, checkout, laporan | Client + tim |
| D-28 | Produk reject | Sumbernya biasanya kesalahan produksi PO; aturan belum dikunci | TBD | Stok, PO, katalog, harga | Client + tim |
| D-29 | Struktur katalog | Produk -> warna -> varian ukuran; foto bisa per produk dan per warna | Confirmed | Model, admin, katalog | Tim produk |
| D-30 | Brand | Nama resmi `Thethingscorner`, huruf T besar dan satu kata | Confirmed | UI, email, dokumen | Client |
| D-31 | Media produk | `PRODUCT_IMAGES_DISK=public` untuk lokal, `s3` untuk Neon Object Storage | Confirmed | Upload, URL, hapus gambar | Tim |
| D-32 | Status pesanan | `MENUNGGU_PEMBAYARAN` → `DIBAYAR` → `MENUNGGU_KONFIRMASI` → `DIKEMAS` → `DIKIRIM` → `SELESAI` | Confirmed | State machine, UI, laporan | Tim |
| D-33 | Preorder dan Custom PO | Konsep yang sama. Produk preorder tidak lewat keranjang, pembeli mengajukan lewat form PO | Assumption | Katalog, keranjang, checkout, alur PO, pembayaran DP | Tim |
| D-34 | Log status pesanan | Tabel `order_status_logs` append-only, mencatat status asal, status tujuan, aktor, dan catatan | Confirmed | Audit dan halaman admin | Tim |

---

## 3. Keputusan yang belum dikunci

### D-16 — Model stok

Pilihan:

- **A. Satu pool** — stok fisik yang sama untuk web dan Shopee. Default sementara.
- **B. Terpisah per kanal** — stok Shopee dan web dialokasikan sendiri.
- **C. Satu pool + alokasi** — stok total dengan booking per kanal.

Dampak:

- A: skema paling sederhana; risiko oversell jika input Shopee terlambat.
- B: perlu tabel/kolom stok per kanal.
- C: paling aman, paling rumit.

Default sementara: **A**. Jika client memilih B/C, tambahkan tabel `channel_stocks` atau `stock_allocations`; jangan ubah tabel inti.

### D-17 — Provider payment

Default sementara: **Midtrans**.

Alasan:

- Snap memudahkan QRIS/VA/e-wallet.
- Dokumentasi Indonesia.
- Bisa diganti ke Xendit lewat adapter.

Jika client memilih Xendit:

- tambah `XenditGateway`;
- ubah `config/services.php`;
- tidak perlu ubah controller/Blade.

### D-18 — DP PO

Default sementara: **50%**.

Implementasi:

- simpan `dp_amount` di `custom_pos`;
- jangan hardcode `0.5` di Blade;
- persentase bisa disimpan di `settings`.

### D-19 — Gudang dan kurir

Default sementara:

- origin: Bandung;
- kurir: JNE, J&T, SiCepat;
- berat produk dalam gram.

Jika berubah:

- ubah `settings.shipping_origin`;
- ubah daftar kurir di `settings`;
- tidak perlu ubah kode checkout.

### D-20 — Data client

Belum tersedia. Pakai:

- `docs/data/*.csv`;
- seeder/factory;
- data dummy untuk UAT internal.

Saat data asli tersedia:

- validasi SKU;
- import produk/varian;
- stock opname awal;
- jangan import data pelanggan asli ke repo publik.

### D-26 - Database

Keputusan: **PostgreSQL 16+** untuk local, staging, dan production.

Alasan:

- Tim tidak memakai MySQL.
- PostgreSQL sudah tersedia di laptop pengembang.
- Laravel mendukung `pgsql` tanpa perubahan kode.
- Belum ada query atau fitur yang bergantung pada MySQL.

Implementasi:

- `.env`: `DB_CONNECTION=pgsql`, port `5432`.
- Ekstensi PHP: `pdo_pgsql`.
- Test tetap memakai SQLite in-memory; migrasi dan test suite juga diuji pada PostgreSQL 18.6.
- Backup production memakai `pg_dump`.

Jika berubah:

- ganti `DB_CONNECTION` dan kredensial di `.env`;
- jalankan ulang migrasi di database baru;
- tidak perlu ubah controller/Blade.

### D-27 - Diskon

Status: **Deferred**.

Diskon belum diimplementasikan karena aturan bisnis belum jelas:

- diskon per produk, per varian, per pesanan, atau kode kupon;
- persentase atau nominal;
- boleh digabung atau tidak;
- batas waktu dan kuota;
- perlakuan di laporan dan rekonsiliasi Shopee;
- apakah harga diskon disimpan sebagai snapshot.

Sampai aturan ini dikunci, jangan tambah kolom diskon ke order atau checkout.

### D-28 - Produk reject

Status: **TBD**.

Produk reject biasanya berasal dari kesalahan produksi custom PO. Pertanyaan yang perlu dijawab client:

- reject dijual sebagai produk diskon, disimpan sebagai stok internal, atau write-off;
- apakah reject terikat ke PO asal;
- apakah butuh grade/condition dan catatan cacat;
- apakah tampil di katalog publik atau hanya dijual via CS/Shopee;
- bagaimana dampaknya ke stok dan laporan.

Jangan tambah kolom atau tabel reject sebelum aturan ini disepakati.

### D-29 - Struktur katalog

Status: **Confirmed**.

Struktur yang dipakai:

```text
products -> product_colors -> product_variants
products -> product_images
product_colors -> product_color_images
```

Alasan:

- warna punya foto sendiri;
- ukuran dan stok terikat ke warna;
- harga varian bisa override harga dasar;
- preorder bisa disimpan di level produk.

Field lanjutan yang ditambahkan 11 November 2026:

- `products.weight_grams`;
- `products.rating_avg`;
- `products.rating_count`;
- `product_variants.stock_reserved`.

### D-30 - Brand

Status: **Confirmed**.

Nama brand yang dipakai di aplikasi dan komunikasi: **`Thethingscorner`**.

Aturan:

- tulis huruf T besar, satu kata;
- gunakan di title halaman, header, footer, dan email;
- `APP_NAME` di `.env` memakai `Thethingscorner`;
- dokumen formal boleh tetap memakai nama panjang sebagai judul dokumen.

Keputusan 12 November 2026: kapitalisasi resmi menjadi `Thethingscorner`, menggantikan penulisan huruf kecil sebelumnya.

### D-31 - Media produk

Status: **Confirmed**.

Foto produk memakai disk yang bisa diatur lewat `.env`.

Untuk lokal:

```dotenv
FILESYSTEM_DISK=local
PRODUCT_IMAGES_DISK=public
```

Untuk kerja bersama dengan Neon Object Storage:

```dotenv
FILESYSTEM_DISK=neon
PRODUCT_IMAGES_DISK=neon
NEON_STORAGE_ACCESS_KEY_ID=...
NEON_STORAGE_SECRET_ACCESS_KEY=...
NEON_STORAGE_REGION=ap-southeast-1
NEON_STORAGE_BUCKET=product
NEON_STORAGE_ENDPOINT=...
```

Aturan:

- Default lokal: `public`, file di `storage/app/public`.
- Disk `neon` memakai kredensial `NEON_STORAGE_*`. Kalau kosong, config memakai `AWS_*` sebagai fallback.
- Credential hanya di `.env`, tidak di Git.
- `use_path_style_endpoint=true` untuk endpoint Neon.
- Setelah mengubah `.env`, jalankan `php artisan config:clear`.
- Accessor `$image->url` mengikuti disk yang aktif.
- File dihapus setelah data database berhasil dihapus.

### D-32 - Status pesanan

Status `BARU` dihapus karena checkout web langsung membuat order dengan `MENUNGGU_PEMBAYARAN`.

Alur:

```text
MENUNGGU_PEMBAYARAN
→ DIBAYAR
→ MENUNGGU_KONFIRMASI
→ DIKEMAS
→ DIKIRIM
→ SELESAI
```

Status lain:

```text
DIBATALKAN
KEDALUWARSA
```

Arti:

- `MENUNGGU_PEMBAYARAN`: order sudah dibuat, pembayaran belum diterima.
- `DIBAYAR`: pembayaran sudah diterima.
- `MENUNGGU_KONFIRMASI`: pembayaran diterima, pesanan menunggu konfirmasi admin sebelum dikemas.
- `DIBATALKAN`: dibatalkan sebelum dikemas.
- `KEDALUWARSA`: pembayaran tidak selesai sampai batas reservasi.

`PENDING` bukan status pesanan. `PENDING` adalah status pembayaran dan akan dipisahkan di `PaymentStatus` pada Slice 5. Status `PENDING` sempat ditambahkan sebagai status pesanan di PR #3, lalu dihapus lagi pada 12 November 2026 karena bertentangan dengan keputusan ini.

### D-33 - Preorder dan Custom PO

Preorder dan Custom PO adalah konsep yang sama. Konsep ini dikonfirmasi tim pada 12 November 2026.

Alur yang diasumsikan sampai client mengonfirmasi:

- Produk preorder tidak masuk keranjang dan tidak bisa di-checkout biasa.
- Pembeli mengajukan lewat form PO. Admin memberi penawaran, DP, lalu produksi dan pelunasan (Slice 8).

Yang belum diputuskan:

- Apakah flag `is_preorder` tetap di produk sebagai penanda "Ajukan PO", atau dipindah ke data PO.
- Apakah `preorder_close_date` dan `estimated_ready_date` tetap di produk atau pindah ke data PO.
- Pembayaran DP dan pelunasan perlu referensi ke PO. Tabel `payments` saat ini hanya punya `order_id`. Keputusan ini harus ada sebelum Slice 5 selesai.

Guard keranjang dan checkout sudah ada di `CartService` dan `CheckoutService`. Flag `is_preorder` sekarang bisa diatur dari admin.

### D-34 - Log status pesanan

Setiap perubahan status pesanan dicatat di `order_status_logs`:

- `order_id`;
- `from_status`;
- `to_status`;
- `actor_id` (null untuk sistem);
- `note`;
- `created_at`.

Log append-only: tidak diubah atau dihapus. Halaman detail pesanan admin menampilkan riwayat ini.

---

## 4. Fitur yang ditunda

| Fitur | Status | Alasan |
|---|---|---|
| WA API | Deferred | `wa.me` cukup |
| Google Maps | Deferred | Tracking teks cukup |
| Analytics | Deferred | Laporan internal cukup |
| Kupon/diskon | Deferred | Menunggu aturan bisnis dan perhitungan |
| COD | Deferred | Menambah rekonsiliasi |
| Loyalty/poin | Deferred | Belum dibutuhkan |
| Sentry | Deferred | Laravel log cukup |
| PDF report | Deferred | Excel/CSV cukup |
| CAPTCHA | Deferred | Rate limit dulu |
| Cloudinary | Deferred | Local storage cukup |
| In-app chat | Deferred | `wa.me` cukup |
| Q&A produk | Deferred | `wa.me` cukup |
| Wishlist | Deferred | Tidak diminta |
| Guest checkout | Deferred | Wajib login |
| Shopee API | Deferred | CSV dulu |
| Retur mandiri | Deferred | Manual via CS |
| Multi-alamat UI | Deferred | Satu default dulu |

---

## 5. Dampak perubahan

| Jika client mengubah | Yang perlu diubah |
|---|---|
| Model stok | Migration, StockService, laporan, rekonsiliasi |
| Provider payment | Adapter + config + sandbox key |
| DP PO | Settings/`dp_amount` + invoice |
| Gudang/kurir | Settings + Biteship origin |
| Alamat gudang | Settings + ongkir |
| Review window | Config/policy |
| Retur | Alur CS; mungkin tabel baru |
| Shopee API | Adapter baru; fase 2 |

---

## 6. Change log

| Versi | Tanggal | Perubahan |
|---|---|---|
| 0.1 | 6 Nov 2026 | Decision log awal |
| 0.2 | 9 Nov 2026 | Tambah D-26: database default PostgreSQL |
| 0.3 | 11 Nov 2026 | Tambah D-27 diskon, D-28 produk reject, D-29 struktur katalog warna/varian |
| 0.4 | 11 Nov 2026 | Brand resmi `thethingscorner`; field ongkir, reservasi, dan rating ditambahkan |
| 0.5 | 11 Nov 2026 | Media produk memakai disk configurable public/s3 |
| 0.6 | 11 Nov 2026 | Status `BARU` dihapus; tambah `MENUNGGU_KONFIRMASI`; `PENDING` dipisahkan sebagai status pembayaran |
| 0.7 | 12 Nov 2026 | Preorder disamakan dengan Custom PO. Produk preorder lewat alur PO, bukan keranjang (D-33, Assumption) |
| 0.8 | 12 Nov 2026 | Brand resmi `Thethingscorner`; status `PENDING` dihapus dari pesanan; log status pesanan ditambahkan (D-34) |
