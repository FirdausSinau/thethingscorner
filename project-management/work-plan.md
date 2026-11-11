# Work Plan - Domain, Slice, dan Urutan Kerja

**Versi:** 1.0  
**Tanggal:** 9 November 2026  
**Untuk:** Firdaus, Anggota A, Anggota B  
**Status:** Aktif

Dokumen ini menjawab satu hal: siapa mengerjakan bagian apa, dan dalam urutan apa.

---

## 1. Model kerja

Yang dipakai bukan "satu slice satu orang", tetapi:

> **Domain untuk ownership, slice untuk urutan delivery dan checkpoint integrasi.**

Artinya:

- Setiap orang punya domain tetap.
- Satu slice boleh dikerjakan beberapa orang sesuai domainnya.
- Firdaus menyiapkan schema, model, dan kontrak route sebelum bagian lain dimulai.
- Setiap slice diakhiri dengan integrasi, test, dan demo singkat.
- Maksimal 2 slice aktif bersamaan supaya tidak terjadi integration hell.

Yang dimaksud "selesai" untuk satu slice:

- semua bagian domain sudah merge ke `main`;
- feature test relevan lulus;
- authorization dan validasi dicek;
- UI responsif di mobile dan desktop;
- bisa didemokan tanpa data palsu di luar seeder;
- dokumentasi terkait diperbarui.

---

## 2. Domain tetap

| Domain | Owner | Cakupan | Kedalaman backend |
|---|---|---|---|
| Backend core dan integrasi | Firdaus | Schema, migration, model, auth, service bersama, payment, shipping, scheduler, security, deploy | Penuh |
| Buyer storefront | Anggota A | Layout pembeli, design system, katalog publik, cart, checkout UI, pesanan pembeli, PO pembeli, review pembeli | Menengah, bisa naik ke `CartService` |
| Admin operations dan reporting | Anggota B | Layout admin, dashboard, produk, stok, pesanan admin, PO admin, review admin, Shopee CSV, laporan | Menengah ke backend, cocok untuk `StockService` dan import |

Kalau ada yang ingin memperdalam backend:

- Anggota A bisa mengambil `CartService`.
- Anggota B bisa mengambil `StockService`, `ShopeeImportService`, dan query laporan.
- Firdaus tetap memegang schema dan review transaksi.

Detail arsitektur backend ada di [`../docs/backend-overview.md`](../docs/backend-overview.md).

---

## 3. Pembagian per slice

Kolom di bawah menunjukkan bagian masing-masing, bukan pemilik tunggal slice.

| Urutan | Slice | Firdaus | Anggota A | Anggota B | Prasyarat |
|---|---|---|---|---|---|
| 0 | Setup project | Laravel, PostgreSQL, Git, Vite, layout dasar | Review | Review | - |
| 1 | Auth dan role | Enum role, migration, middleware, auth, reset password, kelola admin | Review UI login/register | Review akses admin | 0 |
| 2 | Katalog dan varian | Schema categories/products/variants, model, relasi, seeder | Katalog publik, search, filter, detail produk, varian | CRUD produk/varian admin, upload foto | 1 |
| 3 | Keranjang persisten | `CartService`, validasi stok, test | Controller, halaman cart, update qty, empty state | - | 2 |
| 4 | Checkout dan reservasi | `CheckoutService`, transaction, row lock, reservasi, alamat | Form checkout, ringkasan, halaman pembayaran awal | Admin melihat order masuk | 2, 3 |
| 5 | Pembayaran | Adapter payment, fake gateway, webhook, idempotency, scheduler | Halaman pembayaran, instruksi QRIS/VA/transfer | Verifikasi transfer manual | 4 |
| 6 | Pengiriman | Adapter Biteship, fake ongkir, tracking | Pilihan kurir, tracking pembeli | Input resi, status kirim | 4 |
| 7 | Pesanan dan tracking | State machine, observer, log status | Daftar/detail pesanan pembeli, konfirmasi terima | Daftar/detail pesanan admin, filter, aksi status | 4, 5, 6 |
| 8 | Custom PO | `CustomPoService`, status log, DP, policy | Form PO, upload referensi, status PO pembeli | Quote harga, DP, update produksi, pelunasan | 1, 5 |
| 9 | Review dan retur | Policy eligibility, aturan 30 hari, satu review per item | Form review, daftar review, retur via CS | Balas review, moderasi, retur manual | 7 |
| 10 | Stok dan kartu stok | `StockService`, `stock_movements`, test transaksi | - | Halaman stok, kartu stok, stock opname | 2, 4 |
| 11 | Shopee CSV | `ShopeeImportService`, validasi, idempotency, rekonsiliasi | - | Upload CSV, preview error, riwayat import | 10 |
| 12 | Laporan dan export | Review query, index, agregasi | - | Laporan penjualan, stok, PO, rekonsiliasi, export CSV/Excel | 7, 8, 10, 11 |
| 13 | Settings dan integrasi | Settings, status integrasi, link provider, secret handling | - | UI settings, status integrasi | 1 |
| 14 | Security, QA, UAT, deploy | Security review, staging, production, backup | UI audit, responsive, aksesibilitas | QA operasional, regression, UAT | Semua |

---

## 4. Urutan pengerjaan

### Batch 0 - Fondasi

Sudah selesai:

- Slice 0 setup.
- Slice 1 auth dan role.
- Database PostgreSQL.

### Batch 1 - Katalog

**Status 11 November 2026:** selesai direkonsiliasi. Schema mengikuti struktur produk -> warna -> varian dari tim produk. Katalog publik, detail, admin produk, warna, varian, dan foto sudah ada.

Fokus: Slice 2.

Urutan:

1. Firdaus membuat migration, model, relasi, seeder, dan route contract.
2. Setelah schema di-merge, A dan B mulai paralel.
3. A mengerjakan katalog publik.
4. B mengerjakan CRUD admin dan upload foto.
5. Integrasi: produk yang dibuat admin harus muncul di katalog publik.
6. Demo: 10 produk demo, filter kategori, pencarian, detail varian.

### Batch 2 - Cart dan checkout

**Status 11 November 2026:** backend Slice 3 dan 4 selesai. Menunggu UI cart/checkout dari Anggota A dan admin order read-only dari Anggota B.

Fokus: Slice 3 lalu Slice 4.

Urutan:

1. Firdaus menyiapkan `CartService` dan test validasi stok.
2. A mengerjakan halaman cart dan checkout.
3. Firdaus menyiapkan `CheckoutService`, transaction, dan reservasi.
4. B menyiapkan daftar order admin sederhana untuk melihat order yang masuk.
5. Integrasi: cart tidak mengunci stok, checkout membuat reservasi.
6. Demo: tambah ke cart, checkout, stok berubah hanya setelah checkout.

### Batch 3 - Pembayaran dan pengiriman

Fokus: Slice 5 dan Slice 6.

Urutan:

1. Firdaus membuat fake payment dan fake shipping adapter.
2. A membuat halaman pembayaran dan pilihan kurir.
3. B membuat verifikasi transfer manual dan input resi.
4. Integrasi: order bisa dibayar dan dikirim dengan data fake.
5. Demo: QRIS fake, transfer manual, ongkir, resi.

### Batch 4 - Pesanan dan tracking

Fokus: Slice 7.

Urutan:

1. Firdaus membuat state machine dan log status.
2. A membuat daftar/detail pesanan pembeli.
3. B membuat daftar/detail pesanan admin.
4. Integrasi: status berubah konsisten di sisi pembeli dan admin.
5. Demo: alur dari dibayar sampai selesai.

### Batch 5 - PO dan review

Fokus: Slice 8 dan Slice 9.

Urutan:

1. Firdaus menyiapkan service PO, status log, dan policy review.
2. A mengerjakan form PO dan review pembeli.
3. B mengerjakan quote PO, produksi, balasan review, dan moderasi.
4. Integrasi: PO hanya masuk produksi setelah DP, review hanya setelah selesai.
5. Demo: PO end-to-end dan review dengan balasan toko.

### Batch 6 - Stok, Shopee, dan laporan

Fokus: Slice 10, 11, dan 12.

Urutan:

1. Firdaus menyiapkan `StockService` dan `ShopeeImportService`.
2. B mengerjakan halaman stok, kartu stok, import CSV, dan laporan.
3. Integrasi: setiap perubahan stok punya mutasi, import tidak menggandakan data.
4. Demo: kartu stok, import CSV, laporan dan export.

### Batch 7 - Settings

Fokus: Slice 13.

Urutan:

1. Firdaus menyiapkan settings dan status integrasi.
2. B mengerjakan UI settings.
3. Demo: pengaturan toko dan status provider tanpa menampilkan secret.

### Batch 8 - QA dan deploy

Fokus: Slice 14.

Urutan:

1. Semua regression test.
2. A audit UI dan responsive.
3. B audit operasional dan data.
4. Firdaus security review, staging, production, backup.
5. Demo: UAT dengan client.

---

## 5. Contoh sprint

Sesuaikan dengan kalender kampus. Contoh 6 sprint:

| Sprint | Slice | Fokus |
|---|---|---|
| 1 | 2 | Katalog publik dan admin produk |
| 2 | 3, 4 | Cart dan checkout |
| 3 | 5, 6, 7 | Pembayaran, pengiriman, tracking |
| 4 | 8, 9 | PO dan review |
| 5 | 10, 11, 12 | Stok, Shopee, laporan |
| 6 | 13, 14 | Settings, QA, UAT, deploy |

Kalau waktu lebih ketat, Slice 10 dan 11 bisa dimulai setelah Slice 4 stabil, paralel dengan Slice 5-7.

---

## 6. Aturan mulai slice

Sebelum satu slice dimulai:

1. Firdaus menulis kontrak singkat di issue/PR:
   - tabel dan kolom baru;
   - route dan nama route;
   - service method;
   - format data yang dikirim ke Blade.
2. Firdaus merge schema dan model ke `main`.
3. A dan B pull `main`, lalu merge ke branch masing-masing.
4. Bagian A dan B dikerjakan di branch sendiri.
5. PR dibuat per subtile.
6. Setelah semua bagian merge, lakukan demo dan test integrasi.

Yang tidak boleh:

- A atau B membuat migration untuk tabel yang sama tanpa koordinasi.
- Mengubah schema di tengah slice tanpa memberi tahu tim.
- Menunggu semua fitur selesai baru merge.
- Dua orang mengedit file layout yang sama dalam waktu lama.

---

## 7. Checklist integrasi per slice

- [ ] Route bisa diakses sesuai role.
- [ ] FormRequest memvalidasi input.
- [ ] Authorization diuji.
- [ ] Aturan bisnis ada di service, bukan di Blade.
- [ ] Feature test relevan lulus.
- [ ] `npm run build` lulus.
- [ ] UI responsif di 360px, 768px, dan 1280px.
- [ ] Tidak ada `dd()`, `dump()`, atau debug lain.
- [ ] Data demo tersedia lewat seeder.
- [ ] Dokumentasi diperbarui.
- [ ] Bisa didemokan dalam 3 menit.

---

## 8. Ringkasan

- Firdaus: schema, core service, integrasi, security, deploy.
- Anggota A: semua yang dilihat dan dialami pembeli.
- Anggota B: semua operasional admin dan laporan.
- Slice adalah urutan kerja, bukan pemilik.
- Setiap slice punya checkpoint integrasi.
- Backend learner mulai dari stok dan import, bukan payment.
