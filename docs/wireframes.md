# Wireframe Sederhana — The Things Corner

**Versi:** 0.1  
**Tanggal:** 6 November 2026  
**Status:** Draft  
**Referensi:** PRD Fase 1 v0.2, Design System v0.1

---

## 1. Cara membaca

- `[ ]` = kotak/komponen.
- `→` = aksi utama.
- `…` = isi dinamis.
- `(state)` = keadaan UI.
- Semua wireframe mobile-first; desktop hanya memperlebar grid.

---

## 2. Global layout

### 2.1 Header pembeli

```text
┌──────────────────────────────────────────────────────────────┐
│ THE THINGS CORNER      Katalog   Custom PO   Pesanan   Akun  │
│                                          [🔔]  [Keranjang 2] │
└──────────────────────────────────────────────────────────────┘
```

- Logo kiri.
- Navigasi utama tengah.
- Notifikasi, keranjang, akun kanan.
- Mobile: hamburger + keranjang.

### 2.2 Footer

```text
┌──────────────────────────────────────────────────────────────┐
│ The Things Corner   |   Kebijakan Privasi   |   Retur        │
│ WhatsApp   Instagram   Shopee                                 │
│ © 2026 The Things Corner                                      │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Home

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Sepatu handmade custom dari Bandung.                        │
│  Dibuat perlahan, dipakai setiap hari.                       │
│  [ Lihat Katalog → ]   [ Custom PO ]                         │
│                                                              │
│  [ Gambar hero: sepatu utama ]                               │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│ Kategori: [Mules] [Heels] [Boots] [Flats] [Sneakers]         │
├──────────────────────────────────────────────────────────────┤
│ Produk pilihan                                               │
│ [Card] [Card] [Card] [Card]                                  │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│ Kenapa The Things Corner?                                    │
│ • Handmade custom                                            │
│ • Bisa pilih ukuran dan bahan                                │
│ • Update pesanan via WhatsApp                                │
├──────────────────────────────────────────────────────────────┤
│ [ Mulai Custom PO → ]                                        │
└──────────────────────────────────────────────────────────────┘
```

Aksi utama: **Lihat Katalog**. Aksi sekunder: Custom PO.

---

## 4. Katalog

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Katalog Sepatu                                               │
│ Cari: [____________________]  Kategori [Semua v]             │
│ Urutkan: [Terbaru v]                                         │
├──────────────────────────────────────────────────────────────┤
│ [Card] [Card] [Card] [Card]                                  │
│ [Card] [Card] [Card] [Card]                                  │
│                                                              │
│ [ < Sebelumnya ]  Halaman 1 dari 3  [ Berikutnya > ]         │
└──────────────────────────────────────────────────────────────┘
```

State:

- Loading: skeleton card.
- Empty: “Produk tidak ditemukan. Coba kata kunci lain.”
- Error: “Gagal memuat katalog. Coba lagi.”

---

## 5. Detail produk

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ [ Gambar utama ]        Mules Ayu                            │
│ [thumb][thumb][thumb]   Rp219.000                            │
│                         ★ 4.8 (12 review)                    │
│                                                              │
│                         Ukuran:                              │
│                         [37] [38] [39]                       │
│                         Stok tersedia: 10                    │
│                                                              │
│                         Jumlah: [−] [1] [+]                  │
│                         [ Tambah ke Keranjang ]              │
│                         [ Tanya via WhatsApp ]               │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│ Deskripsi                                                    │
│ Bahan, ukuran, perawatan…                                    │
├──────────────────────────────────────────────────────────────┤
│ Review                                                       │
│ [Review card] [Review card]                                  │
│ [ Lihat semua review ]                                       │
└──────────────────────────────────────────────────────────────┘
```

Aturan:

- Tombol utama hanya “Tambah ke Keranjang”.
- Jika stok habis: tombol nonaktif + “Stok habis”.
- Ukuran yang habis diberi label, bukan hanya warna.

---

## 6. Keranjang

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Keranjang (2)                                                │
├──────────────────────────────────────────────────────────────┤
│ [Gambar] Mules Ayu         [−] 1 [+]   Rp219.000   [Hapus]   │
│          Ukuran 38                                           │
│                                                              │
│ [Gambar] Sneakers Vio      [−] 1 [+]   Rp249.000   [Hapus]   │
│          Ukuran 39                                           │
├──────────────────────────────────────────────────────────────┤
│ Ringkasan                                                    │
│ Subtotal                         Rp468.000                   │
│ Ongkir                           Dihitung saat checkout      │
│ [ Checkout → ]                                               │
│                                                              │
│ Catatan: stok belum dikunci sampai checkout.                 │
└──────────────────────────────────────────────────────────────┘
```

State:

- Empty: “Keranjang masih kosong.”
- Item stok habis: tandai dan minta hapus.
- Total dihitung ulang di server.

---

## 7. Checkout

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Checkout                                                     │
├──────────────────────────────────────────────────────────────┤
│ Alamat Pengiriman                                            │
│ [Alamat utama]  [Ganti alamat]                               │
│ Budi Santoso · 0812… · Jl. Merdeka No. 10, Bandung           │
│                                                              │
│ Kurir                                                        │
│ [JNE REG — Rp20.000] [J&T EZ — Rp25.000]                     │
│                                                              │
│ Ringkasan Pesanan                                            │
│ Mules Ayu x1                          Rp219.000              │
│ Sneakers Vio x1                       Rp249.000              │
│ Ongkir                                Rp25.000               │
│ Total                                 Rp493.000              │
│                                                              │
│ [ Buat Pesanan → ]                                           │
└──────────────────────────────────────────────────────────────┘
```

State:

- Alamat kosong: form tambah alamat.
- Ongkir gagal: fallback flat, beri info.
- Stok berubah: tampilkan pesan dan minta review ulang.

---

## 8. Pembayaran

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Pesanan INV-2026-0004                                        │
│ Total Rp289.000 · Batas bayar 30 menit                       │
├──────────────────────────────────────────────────────────────┤
│ Metode Pembayaran                                            │
│ [QRIS]      [Virtual Account]      [Transfer Manual]         │
│                                                              │
│ (QRIS)                                                       │
│ [ Gambar QR ]                                                │
│ Bayar dengan aplikasi bank/e-wallet.                         │
│                                                              │
│ (Transfer Manual)                                            │
│ Transfer ke BCA 1234567890 a.n. The Things Corner            │
│ [ Upload Bukti ]                                             │
│ [ Saya Sudah Transfer ]                                      │
├──────────────────────────────────────────────────────────────┤
│ Status: Menunggu Pembayaran                                  │
└──────────────────────────────────────────────────────────────┘
```

State:

- QRIS: menunggu callback.
- Transfer: menunggu verifikasi admin.
- Kedaluwarsa: pesanan batal, stok dilepas.

---

## 9. Tracking pesanan

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Pesanan INV-2026-0002                                        │
│ Status: Dikirim                                              │
│ Kurir: J&T EZ · Resi: DEMO-JNT-0002                          │
├──────────────────────────────────────────────────────────────┤
│ Timeline                                                     │
│ ● Pesanan dibuat              28 Okt 10:00                    │
│ ● Pembayaran diterima         28 Okt 10:10                    │
│ ● Dikemas                     29 Okt 08:00                    │
│ ● Dikirim                     29 Okt 09:00                    │
│ ○ Sedang diantar                                             │
│ ○ Diterima                                                   │
├──────────────────────────────────────────────────────────────┤
│ [ Hubungi CS via WhatsApp ]                                  │
│ [ Ajukan Retur ]                                             │
└──────────────────────────────────────────────────────────────┘
```

Tracking berupa teks, bukan peta.

---

## 10. Akun pembeli

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Akun Saya                                                    │
│ [Profil] [Alamat] [Pesanan] [Custom PO] [Notifikasi]         │
├──────────────────────────────────────────────────────────────┤
│ Pesanan terakhir                                             │
│ INV-2026-0002 · Dikirim · Rp274.000 · [Lihat]                │
│ INV-2026-0001 · Selesai · Rp239.000 · [Lihat] [Review]       │
├──────────────────────────────────────────────────────────────┤
│ Alamat                                                       │
│ [Alamat utama] [Tambah alamat]                               │
└──────────────────────────────────────────────────────────────┘
```

---

## 11. Form Custom PO

```text
┌──────────────────────────────────────────────────────────────┐
│ Header                                                        │
├──────────────────────────────────────────────────────────────┤
│ Custom PO                                                    │
│ Model: [__________________________]                          │
│ Bahan: [__________________________]                          │
│ Ukuran: [38 v]   Jumlah: [1]                                 │
│ Referensi gambar: [ Upload ]                                 │
│ Catatan: [____________________________________]              │
│                                                              │
│ [ Kirim Pengajuan → ]                                        │
│                                                              │
│ Info: Admin akan menghubungi via WhatsApp untuk harga        │
│ dan estimasi produksi.                                       │
└──────────────────────────────────────────────────────────────┘
```

---

## 12. Status Custom PO

```text
┌──────────────────────────────────────────────────────────────┐
│ PO-2026-0001 · Mules Custom Kulit                            │
│ Status: Produksi                                             │
│ DP: Rp175.000 · Sisa: Rp175.000 · Estimasi: 20 Nov 2026      │
├──────────────────────────────────────────────────────────────┤
│ Timeline                                                     │
│ ● Diajukan                                                   │
│ ● Ditinjau                                                   │
│ ● DP diterima                                                │
│ ● Produksi        ← sekarang                                 │
│ ○ Selesai produksi                                           │
│ ○ Pelunasan                                                  │
│ ○ Dikirim                                                    │
│ ○ Selesai                                                    │
├──────────────────────────────────────────────────────────────┤
│ [ Hubungi Admin via WhatsApp ]                               │
└──────────────────────────────────────────────────────────────┘
```

---

## 13. Admin dashboard

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin  |  Dashboard  Produk  Stok  Pesanan  PO  Review  Laporan│
├──────────────────────────────────────────────────────────────┤
│ Halo, Admin Biasa                                            │
│                                                              │
│ [Pesanan baru: 3] [Perlu dikemas: 2] [Stok kritis: 4]        │
│ [PO produksi: 1] [Review baru: 1]                            │
│                                                              │
│ Pesanan terbaru                                              │
│ [Tabel: kode, pembeli, status, total, aksi]                  │
│                                                              │
│ Stok kritis                                                  │
│ [Tabel: SKU, produk, ukuran, stok]                           │
└──────────────────────────────────────────────────────────────┘
```

Admin Master melihat tambahan: laporan, akun admin, pengaturan, rekonsiliasi.

---

## 14. Admin produk

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin > Produk                                               │
│ [ + Tambah Produk ]                                          │
├──────────────────────────────────────────────────────────────┤
│ Cari: [__________]  Status [Aktif v]                         │
├──────────────────────────────────────────────────────────────┤
│ [Gambar] Mules Ayu   3 varian   Rp219.000   [Edit] [Nonaktif]│
│ [Gambar] Heels Rina  3 varian   Rp189.000   [Edit] [Nonaktif]│
└──────────────────────────────────────────────────────────────┘
```

Form produk:

- nama, slug, kategori, deskripsi;
- berat;
- status;
- foto;
- varian: ukuran, warna, SKU, harga, stok.

---

## 15. Admin pesanan

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin > Pesanan                                              │
│ Filter: [Status v] [Kanal v] [Tanggal]                       │
├──────────────────────────────────────────────────────────────┤
│ INV-2026-0004  Budi     Menunggu Pembayaran   Rp289.000      │
│ INV-2026-0003  Rina     Dibayar               Rp209.000      │
│ INV-2026-0002  Sari     Dikirim               Rp274.000      │
└──────────────────────────────────────────────────────────────┘
```

Detail pesanan:

```text
┌──────────────────────────────────────────────────────────────┐
│ INV-2026-0003 · Rina · Dibayar                               │
├──────────────────────────────────────────────────────────────┤
│ Item: Heels Rina 37 x1 · Rp189.000                           │
│ Alamat: Jl. Diponegoro No. 8, Surabaya                       │
│ Pembayaran: Midtrans QRIS · PAID                             │
│ Pengiriman: belum dibuat                                     │
├──────────────────────────────────────────────────────────────┤
│ [ Verifikasi Pembayaran ]  [ Proses ]  [ Buat Pengiriman ]   │
│ [ Kirim WA ]  [ Batalkan ]                                   │
└──────────────────────────────────────────────────────────────┘
```

---

## 16. Admin pengiriman

```text
┌──────────────────────────────────────────────────────────────┐
│ Buat Pengiriman · INV-2026-0003                              │
├──────────────────────────────────────────────────────────────┤
│ Kurir: [JNE v]   Layanan: [REG v]                            │
│ Berat: 600 gram                                              │
│ Ongkir: Rp20.000                                             │
│ [ Buat Pickup di Biteship ]                                  │
├──────────────────────────────────────────────────────────────┤
│ Setelah dibuat:                                              │
│ Resi: DEMO-JNE-0003                                          │
│ Status: PICKUP                                               │
│ [ Cetak Label ]  [ Update Tracking ]                         │
└──────────────────────────────────────────────────────────────┘
```

Fallback: input kurir + resi manual.

---

## 17. Admin stok

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin > Stok                                                 │
│ Cari SKU/produk: [__________]                                │
├──────────────────────────────────────────────────────────────┤
│ SKU                     Produk        Ukuran  Stok  Aksi     │
│ DEMO-MULES-AYU-38-BLK   Mules Ayu     38      10    [Detail] │
│ DEMO-HEELS-RINA-37-BLK  Heels Rina    37       6    [Detail] │
├──────────────────────────────────────────────────────────────┤
│ [ + Stok Masuk ]  [ + Penyesuaian ]                          │
└──────────────────────────────────────────────────────────────┘
```

Detail:

```text
Kartu Stok · DEMO-MULES-AYU-38-BLK
[Tanggal] [Tipe] [Sumber] [Qty] [Sebelum] [Sesudah] [Catatan]
```

---

## 18. Admin PO

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin > Custom PO                                            │
│ Filter: [Status v]                                           │
├──────────────────────────────────────────────────────────────┤
│ PO-2026-0001  Budi   Produksi        Rp350.000  [Detail]     │
│ PO-2026-0002  Sari   Menunggu DP     Rp400.000  [Detail]     │
│ PO-2026-0003  Rina   Selesai         Rp450.000  [Detail]     │
└──────────────────────────────────────────────────────────────┘
```

Detail:

```text
PO-2026-0001 · Mules Custom Kulit
Pelanggan: Budi
Bahan: Kulit sintetis · Ukuran 38 · Qty 1
Harga: Rp350.000 · DP: Rp175.000
Status: Produksi · Deadline: 20 Nov 2026
[ Update Status ] [ Kirim Tagihan Pelunasan ] [ Kirim WA ]
```

---

## 19. Admin review

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin > Review                                               │
│ Filter: [Semua v] [Disembunyikan v]                          │
├──────────────────────────────────────────────────────────────┤
│ ★5 Mules Ayu · Budi · “Nyaman dipakai.”                      │
│    Balasan: “Terima kasih Kak!”                              │
│    [ Balas ] [ Sembunyikan ]                                 │
│                                                              │
│ ★4 Docmart Lita · Sari · “Sesuai foto.”                      │
│    [ Balas ]                                                 │
└──────────────────────────────────────────────────────────────┘
```

Admin Biasa boleh membalas. Admin Master boleh menyembunyikan.

---

## 20. Admin laporan dan Shopee

```text
┌──────────────────────────────────────────────────────────────┐
│ Admin > Laporan                                              │
│ [Penjualan] [Stok] [PO] [Rekonsiliasi Shopee]                │
├──────────────────────────────────────────────────────────────┤
│ Filter: [Tanggal] [Kanal] [Produk]                           │
│ [ Export Excel/CSV ]                                         │
├──────────────────────────────────────────────────────────────┤
│ Ringkasan: Total | Diproses | Dikirim | Selesai             │
│ Tabel per produk/sekolah…                                    │
└──────────────────────────────────────────────────────────────┘
```

Shopee:

```text
┌──────────────────────────────────────────────────────────────┐
│ Import Pesanan Shopee                                        │
│ [ Upload CSV ]                                               │
│ Hasil: 8 baris · 7 berhasil · 1 gagal                        │
│ [ Lihat baris gagal ]                                        │
├──────────────────────────────────────────────────────────────┤
│ Rekonsiliasi Stok                                            │
│ SKU | Stok sistem | Stok Shopee | Selisih | Aksi             │
└──────────────────────────────────────────────────────────────┘
```

---

## 21. State wajib

Setiap halaman daftar/form:

- loading;
- empty;
- error;
- success;
- disabled;
- unauthorized (403).

Contoh empty state admin:

> Belum ada pesanan baru.  
> Pesanan dari web dan Shopee akan muncul di sini.

---

## 22. Prioritas wireframe

| Prioritas | Halaman |
|---|---|
| P1 | Home, katalog, detail, cart, checkout, pembayaran, tracking, login/register |
| P1 | Admin dashboard, pesanan, detail pesanan, pengiriman, stok |
| P2 | Custom PO form/status, review, admin review, laporan, Shopee import |
| P2 | Akun, alamat, notifikasi |
| P3 | Pengaturan, integrasi health, audit log |

Wireframe ini menjadi acuan UI pertama; detail visual mengikuti `design-system.md`.
