# Data yang Dibutuhkan dari Client — The Things Corner

**Versi:** 0.1  
**Tanggal:** 9 November 2026  
**Tujuan:** daftar data yang perlu diminta ke client untuk membangun, mengisi, dan menguji website.

---

## 1. Prinsip

- Jangan minta semua data sekaligus. Mulai dari **sample kecil** untuk validasi, lalu minta data lengkap sebelum UAT/production.
- Data pelanggan asli tidak perlu diminta. Pembeli akan mendaftar sendiri di website.
- Jangan pernah meminta password, token, atau credential.
- Untuk data pesanan, cukup data transaksi tanpa nama lengkap, nomor HP, dan alamat lengkap pelanggan.
- Minta file asli, bukan screenshot.
- Format paling aman: **CSV atau Excel** dengan satu baris per varian/SKU.
- Foto produk: file asli, bukan screenshot Shopee.

---

## 2. Paket data

### P0 — Dibutuhkan sekarang untuk desain dan seeding awal

| Data | Isi | Format | Kenapa |
|---|---|---|---|
| Produk contoh | 10–20 produk | CSV/Excel | Validasi struktur produk |
| Varian contoh | SKU, ukuran, warna, harga, stok | CSV/Excel | Validasi stok per varian |
| Foto contoh | 3–5 foto per produk contoh | JPG/PNG | Validasi galeri dan optimasi |
| Kategori | Daftar kategori toko | CSV/Excel | Filter katalog |
| Berat | Berat per varian dalam gram | CSV/Excel | Ongkir Biteship |
| Alamat gudang | Alamat, kota, kode pos | Teks | Origin ongkir |
| Kurir | JNE/J&T/SiCepat/lain | Teks | Pilihan pengiriman |
| Kebijakan toko | Privasi, retur, review, pengiriman | Dokumen/teks | Halaman statis |
| Akun admin | Nama, email, role | CSV/Excel | Seeder admin |
| Contoh pesanan | 20–50 pesanan terakhir | CSV/Excel | Uji laporan dan status |
| Contoh PO | 10–20 PO dari WhatsApp | CSV/Excel | Uji alur PO |
| Contoh laporan | Rekap yang biasa dipakai | Excel/PDF | Menyamakan definisi laporan |

### P1 — Dibutuhkan sebelum UAT dan production

| Data | Isi | Format | Kenapa |
|---|---|---|---|
| Semua produk | 164 produk atau sesuai kondisi | CSV/Excel | Katalog final |
| Semua varian | SKU, ukuran, warna, harga, stok | CSV/Excel | Stok dan transaksi |
| Foto produk final | Minimal 3 per produk | JPG/PNG | Katalog final |
| Stok opname | Stok fisik per SKU + tanggal | CSV/Excel | Titik awal stok |
| Riwayat pesanan | 3–6 bulan terakhir | CSV/Excel | Laporan dan rekonsiliasi |
| Riwayat PO | Semua PO yang masih berjalan | CSV/Excel | Migrasi PO |
| Mapping Shopee | SKU internal ↔ item/variant Shopee | CSV/Excel | Rekonsiliasi |
| Aturan ongkir | Berat, dimensi, kurir, zona | CSV/Excel | Ongkir |
| Rekening toko | Bank, nomor, nama | Teks | Transfer manual |
| Dokumen provider | KTP/NIB/rekening | PDF/foto | Pendaftaran Midtrans/Biteship |
| Contoh review | Review lama jika ada | CSV/Excel | Modul review |

### P2 — Nice to have

| Data | Isi | Kenapa |
|---|---|---|
| Harga modal | HPP per SKU | Laporan margin (belum Fase 1) |
| Supplier | Nama dan kontak | Pembelian (belum Fase 1) |
| Bahan baku | Material dan stok | BOM (belum Fase 1) |
| Data pelanggan lama | Dengan consent | Migrasi akun (belum Fase 1) |
| Data promo | Kupon/diskon | Fase 2 |
| Data COD | Riwayat COD | Fase 2 |

---

## 3. Template file

Gunakan template di `docs/data/`:

| Template | Isi |
|---|---|
| `products.csv` | Produk |
| `product_variants.csv` | SKU, ukuran, warna, harga, stok |
| `stock_movements.csv` | Mutasi stok |
| `orders_sample.csv` | Pesanan |
| `order_items_sample.csv` | Item pesanan |
| `payments_sample.csv` | Pembayaran |
| `shipments_sample.csv` | Pengiriman |
| `custom_pos.csv` | Custom PO |
| `reviews.csv` | Review |
| `shopee_orders_sample.csv` | Pesanan Shopee |
| `users.csv` | Akun admin/demo |
| `addresses.csv` | Contoh alamat |

Untuk data asli, ganti nama menjadi:

```text
products_real.csv
product_variants_real.csv
stock_real.csv
orders_real.csv
order_items_real.csv
custom_pos_real.csv
shopee_products_real.csv
shopee_orders_real.csv
```

---

## 4. Kolom minimal

### Produk

```text
nama, kategori, deskripsi, status, berat_gram
```

### Varian

```text
nama_produk, sku, ukuran, warna, harga, stok
```

### Pesanan

```text
order_id, tanggal, kanal, sku, qty, harga, status, kota
```

### Custom PO

```text
po_id, tanggal, model, bahan, ukuran, qty, harga, dp, status, deadline, catatan
```

### Shopee

```text
order_sn, order_date, sku, product_name, quantity, status, city, total
```

---

## 5. Aturan format

- Satu baris = satu SKU/varian.
- Jangan gabungkan beberapa ukuran dalam satu sel.
- Harga berupa angka, tanpa `Rp` dan tanpa titik.
- Stok berupa bilangan bulat.
- Ukuran memakai standar yang konsisten, misalnya `37`, `38`, `39`.
- Warna memakai satu ejaan, misalnya `Black`, bukan campur `Hitam/black`.
- SKU unik.
- Nama file jelas dan bertanggal.
- Foto diberi nama sesuai SKU, misalnya `DEMO-MULES-AYU-38-BLK-1.jpg`.
- Jangan memakai rumus Excel di kolom data; kirim nilai jadi.

---

## 6. Data yang tidak perlu diminta

- Password akun.
- Nomor kartu atau credential payment gateway.
- Data pelanggan lengkap tanpa consent.
- Screenshot chat pribadi tanpa izin.
- Data kesehatan atau data sensitif.
- Seluruh isi database Shopee mentah jika ada data pribadi.

---

## 7. Urutan permintaan

```text
1. Produk + varian contoh
2. Foto contoh
3. Alamat gudang + kurir
4. Contoh pesanan + PO
5. Kebijakan toko
6. Data lengkap produk + stok opname
7. Mapping Shopee
8. Riwayat pesanan/PO
9. Dokumen provider
```

---

## 8. Pesan singkat untuk client

> Kak, untuk membangun website The Things Corner, kami butuh data bertahap. Tahap pertama cukup 10–20 produk contoh, varian ukuran/warna/SKU, harga, stok, foto produk, alamat gudang, daftar kurir, dan 20–50 contoh pesanan/PO. Formatnya boleh Excel/CSV. Untuk data pelanggan, kami tidak perlu nama/alamat lengkap; nanti pembeli daftar sendiri di website. Jangan kirim password atau credential apa pun. Setelah struktur data cocok, kami minta data lengkap untuk UAT dan production.

---

## 9. Checklist permintaan

- [ ] 10–20 produk contoh
- [ ] Varian dan SKU
- [ ] Harga dan stok awal
- [ ] Berat per varian
- [ ] Foto produk contoh
- [ ] Kategori
- [ ] Alamat gudang dan kode pos
- [ ] Daftar kurir
- [ ] Kebijakan privasi/retur/review
- [ ] Akun admin (nama/email/role)
- [ ] 20–50 contoh pesanan
- [ ] 10–20 contoh PO
- [ ] Contoh laporan lama
- [ ] Export produk Shopee
- [ ] Export pesanan Shopee
- [ ] Mapping SKU Shopee
- [ ] Data lengkap sebelum UAT
