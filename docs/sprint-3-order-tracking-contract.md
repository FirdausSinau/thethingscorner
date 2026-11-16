# Sprint 3 - Order Tracking Contract

**Versi:** 1.0  
**Tanggal:** 16 November 2026  
**Untuk:** Firdaus, Anand, Hafiez  
**Status:** Backend selesai: kontrak, policy pembatalan/konfirmasi, guard transisi, auto-selesai, dan refund service. Timeline pembeli Anand, UI admin Hafiez, dan notifikasi web menyusul.  
**Keputusan terkait:** D-32, D-34, D-35, D-36

Dokumen ini mengunci lifecycle pesanan Sprint 3: pembayaran, pengemasan, pengiriman, pembatalan, konfirmasi terima, auto-selesai, dan refund.

---

## 1. Prinsip

- `OrderStatus` tetap satu-satunya status pesanan.
- `PaymentStatus` dan `ShipmentStatus` adalah status pendukung, bukan pengganti status pesanan.
- Semua perubahan status pesanan lewat `OrderStatusService` agar lock, stok, dan log konsisten.
- `order_status_logs` append-only. Tidak ada update atau hapus.
- `MENUNGGU_KONFIRMASI` selalu tampil sebagai **Dipending**.
- Aksi pembeli dan admin dibatasi policy, bukan hanya tombol yang disembunyikan.

## 2. Tabel transisi

| Dari | Ke | Aktor | Syarat |
|---|---|---|---|
| MENUNGGU_PEMBAYARAN | DIBAYAR | sistem | PaymentStatus PAID, nominal cocok |
| MENUNGGU_PEMBAYARAN | DIBATALKAN | pembeli, admin | reservasi masih aktif |
| MENUNGGU_PEMBAYARAN | KEDALUWARSA | sistem | semua reservasi kedaluwarsa |
| DIBAYAR | MENUNGGU_KONFIRMASI | admin | pembayaran sudah diverifikasi |
| DIBAYAR | DIBATALKAN | Admin Master | refund manual dicatat |
| MENUNGGU_KONFIRMASI | DIKEMAS | admin | stok sudah terpotong saat pembayaran |
| MENUNGGU_KONFIRMASI | DIBATALKAN | pembeli, admin | belum dikemas, stok dikembalikan |
| DIKEMAS | DIKIRIM | admin, sistem | shipment aktif punya resi |
| DIKEMAS | DIBATALKAN | Admin Master | refund manual, stok dikembalikan |
| DIKIRIM | SELESAI | pembeli, sistem | diterima pembeli atau auto-selesai |
| DIKIRIM | DIBATALKAN | Admin Master | shipment RETURNED, refund manual |
| SELESAI | - | - | final |
| DIBATALKAN | - | - | final |
| KEDALUWARSA | - | - | final |

Catatan:

- `DIBAYAR -> DIBATALKAN`, `DIKEMAS -> DIBATALKAN`, dan `DIKIRIM -> DIBATALKAN` hanya untuk Admin Master.
- `DIKIRIM -> DIBATALKAN` baru boleh setelah shipment terakhir `RETURNED`.
- `KEDALUWARSA` hanya boleh diatur sistem. Admin tidak boleh memilihnya dari dropdown.
- `DIBATALKAN` setelah pembayaran wajib punya catatan alasan dan status pembayaran `REFUNDED` atau alasan refund tertunda.

## 3. Mapping pembayaran

| PaymentStatus | OrderStatus | Stok |
|---|---|---|
| PENDING | MENUNGGU_PEMBAYARAN | ditahan |
| PAID | DIBAYAR | fisik berkurang sekali |
| EXPIRED | KEDALUWARSA | ditahan lalu dilepas |
| FAILED | tidak berubah | tetap ditahan sampai expiry |
| REFUNDED | DIBATALKAN bila belum dikirim | dikembalikan sesuai aturan |

`FAILED` tidak langsung membatalkan order karena provider bisa mengirim status gagal sementara. Order menunggu sampai batas waktu atau admin memutuskan.

## 4. Mapping pengiriman

| ShipmentStatus | OrderStatus | Aksi |
|---|---|---|
| PENDING | DIKEMAS | belum ada perubahan order |
| PICKUP | DIKIRIM | isi `shipped_at`, order `DIKIRIM` |
| IN_TRANSIT | DIKIRIM | tracking diperbarui |
| DELIVERED | SELESAI | isi `delivered_at`; pembeli bisa konfirmasi lebih dulu |
| RETURNED | DIKIRIM | admin memutuskan kirim ulang atau batalkan |

`DELIVERED` dari webhook boleh menyelesaikan order otomatis. `RETURNED` tidak otomatis membatalkan order karena perlu keputusan admin dan refund.

## 5. Pembatalan

Aturan pembeli:

- Boleh membatalkan saat `MENUNGGU_PEMBAYARAN` dan `MENUNGGU_KONFIRMASI`.
- Tidak boleh membatalkan saat `DIKEMAS`, `DIKIRIM`, `SELESAI`, `DIBATALKAN`, atau `KEDALUWARSA`.
- Pembatalan saat `MENUNGGU_PEMBAYARAN` melepas reservasi tanpa mengurangi stok fisik.
- Pembatalan saat `MENUNGGU_KONFIRMASI` mengembalikan stok fisik karena stok sudah terpotong saat pembayaran.
- Alasan pembatalan wajib diisi dan masuk `order_status_logs`.

Aturan admin:

- Admin biasa boleh membatalkan sebelum `DIKEMAS`.
- Admin Master boleh membatalkan setelah pembayaran dan setelah `DIKEMAS` dengan refund manual.
- Pembatalan setelah `DIKIRIM` tidak lewat tombol biasa; harus lewat alur retur dan refund.

## 6. Konfirmasi terima dan auto-selesai

- Pembeli boleh menekan **Pesanan diterima** saat `DIKIRIM`.
- Konfirmasi pembeli mengubah order menjadi `SELESAI` dan mengisi `completed_at`.
- Command `orders:auto-complete` mengubah `DIKIRIM` menjadi `SELESAI` setelah 7 hari sejak `shipments.shipped_at`.
- Jika `shipped_at` kosong, pakai `order_status_logs` saat status menjadi `DIKIRIM`.
- Command dijalankan harian oleh scheduler dan memakai `bySystem = true`.
- Auto-selesai tidak berjalan untuk shipment `RETURNED`.

## 7. Refund

- Refund hanya manual dan hanya Admin Master.
- Refund mengubah `payments.status` menjadi `REFUNDED` dan mencatat `verified_by`.
- Refund tidak memanggil provider asli selama Sprint 3.
- Refund setelah order `DIKEMAS` mengembalikan stok fisik.
- Refund setelah `DIKIRIM` menunggu barang kembali; stok dikembalikan saat shipment `RETURNED`.
- Alasan refund wajib diisi dan masuk `integration_logs` serta `order_status_logs`.

## 8. Notifikasi

Notifikasi web direncanakan untuk:

- pembayaran diterima;
- pembayaran kedaluwarsa;
- pesanan dikemas;
- pesanan dikirim beserta resi;
- pesanan selesai;
- pembatalan atau refund.

Notifikasi tidak boleh menjadi satu-satunya sumber kebenaran. Status tetap dibaca dari database.

## 9. Filter admin

Filter minimum:

- status pesanan;
- kanal (`web`, `whatsapp`, `shopee` bila aktif);
- tanggal dibuat;
- metode pembayaran;
- status pembayaran;
- status pengiriman;
- pencarian kode pesanan, nama penerima, atau nomor telepon.

## 10. Test wajib

- Pembeli hanya melihat pesanannya sendiri.
- Admin melihat semua pesanan.
- Transisi yang tidak sah ditolak.
- Pembatalan sebelum dan sesudah aturan yang diizinkan.
- Pembatalan mengembalikan stok hanya jika stok sudah terpotong.
- Konfirmasi terima hanya saat `DIKIRIM`.
- Auto-selesai berjalan setelah 7 hari dan tidak berjalan untuk `RETURNED`.
- Refund hanya Admin Master.
- Timeline status tampil di sisi pembeli dan admin.
- Setiap perubahan status menghasilkan satu baris `order_status_logs`.

## 11. Di luar kontrak

- Retur penuh setelah `DELIVERED`.
- Refund otomatis ke gateway.
- Notifikasi email/WhatsApp.
- Review produk setelah selesai.
- Pembatalan otomatis karena stok hilang.

## 12. Lampiran: `integration_logs`

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| provider | string(30) | `fake`, `manual`, `midtrans`, `biteship` |
| type | string(20) | `payment`, `shipping` |
| event | string(50) | contoh `callback.received`, `callback.processed` |
| external_id | string(100) | nullable, referensi payment atau resi |
| status | string(20) | `received`, `processed`, `failed`, `ignored` |
| payload | json | nullable, payload yang sudah dibersihkan dari secret |
| message | text | nullable, alasan gagal atau catatan |
| created_at | timestamp | append-only, tanpa `updated_at` |

Index: `(provider, external_id)`, `(type, created_at)`, `status`.

Aturan:

- Secret, signature, dan token tidak boleh masuk `payload`.
- Callback duplikat tetap dicatat, tetapi statusnya `ignored` dan stok tidak berubah.
- Log tidak dihapus. Retensi diatur terpisah saat deploy.
