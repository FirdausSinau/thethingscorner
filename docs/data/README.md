# Sample Dataset — The Things Corner

**Versi:** 0.1  
**Tanggal:** 6 November 2026  
**Status:** Data dummy untuk desain, seeding, dan pengujian. Bukan data client.

---

## 1. Tujuan

Folder ini berisi contoh data kecil untuk:

- memvalidasi ERD dan struktur tabel;
- menguji alur katalog, keranjang, checkout, pembayaran, pengiriman, PO, review, dan laporan;
- menjadi template CSV saat client memberikan data asli;
- menghindari ketergantungan pada data client sebelum tersedia.

**Jangan memakai data pelanggan asli di folder ini.** Semua nama, alamat, nomor HP, dan pesanan adalah fiktif.

---

## 2. Akun demo

Semua password demo: `rahasia123` (di-hash oleh seeder).

| Nama | Email | Role |
|---|---|---|
| Admin Master | `admin@demo.local` | `admin_master` |
| Admin Biasa | `adminbiasa@demo.local` | `admin` |
| Budi Santoso | `budi@demo.local` | `buyer` |
| Sari Dewi | `sari@demo.local` | `buyer` |
| Rina Putri | `rina@demo.local` | `buyer` |

---

## 3. File

| File | Isi |
|---|---|
| `users.csv` | Akun demo tanpa password. |
| `addresses.csv` | Alamat pembeli. |
| `products.csv` | 10 produk demo. |
| `product_variants.csv` | Varian ukuran/warna dan stok. |
| `stock_movements.csv` | Contoh mutasi stok. |
| `orders_sample.csv` | 10 pesanan dengan status berbeda. |
| `order_items_sample.csv` | Item pesanan. |
| `payments_sample.csv` | Pembayaran gateway/manual. |
| `shipments_sample.csv` | Pengiriman dan resi. |
| `custom_pos.csv` | 3 custom PO. |
| `reviews.csv` | 3 review, termasuk 1 disembunyikan. |
| `shopee_orders_sample.csv` | 8 baris pesanan Shopee untuk uji import. |

---

## 4. Urutan import yang disarankan

```text
users
→ addresses
→ products
→ product_variants
→ stock_movements
→ orders
→ order_items
→ payments
→ shipments
→ custom_pos
→ reviews
→ shopee_orders
```

Untuk data asli client, urutan yang sama berlaku. Jangan import pesanan sebelum produk dan varian ada.

---

## 5. Asumsi data

- Harga dalam Rupiah, tanpa desimal.
- Stok dalam satuan pasang.
- `stock_reserved` di contoh ini 0 karena reservasi baru dibuat saat checkout.
- Model stok sementara: satu pool fisik. Jika client memilih stok terpisah per kanal, kolom dan aturan bisa berubah.
- Satu custom PO mewakili satu pasang sepatu. Jika client butuh banyak pasang per PO, tambahkan `custom_po_items`.
- `channel` pesanan: `web` atau `shopee`.
- Status pesanan: `BARU`, `MENUNGGU_PEMBAYARAN`, `DIBAYAR`, `DIKEMAS`, `DIKIRIM`, `SELESAI`, `DIBATALKAN`, `KEDALUWARSA`.
- Status pembayaran: `PENDING`, `PAID`, `EXPIRED`, `FAILED`, `REFUNDED`.
- Status pengiriman: `PENDING`, `PICKUP`, `IN_TRANSIT`, `DELIVERED`.
- Status PO: `DIAJUKAN`, `DITINJAU`, `MENUNGGU_DP`, `PRODUKSI`, `SELESAI_PRODUKSI`, `MENUNGGU_PELUNASAN`, `DIKIRIM`, `SELESAI`, `DIBATALKAN`.

---

## 6. Catatan untuk data asli

Saat client memberikan data asli:

1. Ganti nama file menjadi `products_real.csv`, `variants_real.csv`, dan seterusnya.
2. Validasi SKU unik dan harga.
3. Cocokkan SKU dengan Shopee.
4. Import produk dan varian terlebih dahulu.
5. Lakukan stock opname awal sebelum menerima pesanan.
6. Jangan import data pelanggan asli ke repository publik.
7. Simpan file asli di luar Git atau di storage privat.
