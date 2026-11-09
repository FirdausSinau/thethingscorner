# Design System Sederhana — The Things Corner

**Versi:** 0.1  
**Tanggal:** 6 November 2026  
**Status:** Draft  
**Referensi:** PRD Fase 1 v0.2, wireframes

---

## 1. Arah visual

The Things Corner adalah toko sepatu handmade custom dari Bandung. Kesan yang ingin dibangun:

- hangat dan personal;
- rapi dan terpercaya;
- tidak korporat;
- tidak meniru marketplace besar;
- produk menjadi fokus utama.

Prinsip:

1. Satu halaman punya satu tujuan utama.
2. Satu layar punya satu aksi utama.
3. Banyak ruang kosong, tidak penuh badge dan banner.
4. Warna aksen dipakai hemat.
5. Animasi kecil, bukan pertunjukan.
6. Teks jelas, bukan jargon.

---

## 2. Design token

### 2.1 Warna

| Token | Hex | Pemakaian |
|---|---|---|
| `--color-primary` | `#4B2E83` | Tombol utama, link, aksen |
| `--color-primary-dark` | `#2E1B4F` | Hover, heading |
| `--color-accent` | `#C08457` | Detail hangat, badge, garis |
| `--color-bg` | `#FAF7F2` | Latar halaman |
| `--color-surface` | `#FFFFFF` | Card, form, panel |
| `--color-ink` | `#1F1B24` | Teks utama |
| `--color-muted` | `#6B6475` | Teks sekunder |
| `--color-border` | `#E7E1EA` | Garis, border |
| `--color-success` | `#15803D` | Berhasil, selesai |
| `--color-warning` | `#B45309` | Menunggu, perhatian |
| `--color-danger` | `#B91C1C` | Gagal, batal |
| `--color-info` | `#1D4ED8` | Informasi |

Status badge:

| Status | Warna |
|---|---|
| Baru / Menunggu | warning |
| Dibayar / Diproses | info |
| Dikirim | primary |
| Selesai | success |
| Dibatalkan / Kedaluwarsa | danger |

### 2.2 Tipografi

| Token | Font | Pemakaian |
|---|---|---|
| Display | `Fraunces`, Georgia, serif | Judul halaman, hero |
| Body/UI | `Plus Jakarta Sans`, system-ui, sans-serif | Teks, form, tombol |

Skala:

| Token | Ukuran | Line height |
|---|---|---|
| `text-xs` | 12px | 16px |
| `text-sm` | 14px | 20px |
| `text-base` | 16px | 26px |
| `text-lg` | 18px | 28px |
| `text-xl` | 22px | 30px |
| `text-2xl` | 28px | 36px |
| `text-3xl` | 36px | 44px |
| `text-4xl` | 48px | 56px |

Aturan:

- Satu `h1` per halaman.
- Heading tidak lebih dari 2 baris di desktop.
- Body maksimal 70 karakter per baris.
- Jangan memakai font weight 300 untuk teks kecil.
- Angka harga memakai `font-variant-numeric: tabular-nums`.

### 2.3 Spacing

Skala 4px:

```text
4, 8, 12, 16, 24, 32, 48, 64, 96
```

Pemakaian:

- jarak antar-field: 16px;
- jarak antar-card: 16–24px;
- padding card: 20–24px;
- section: 48–64px;
- hero: 64–96px.

### 2.4 Radius dan shadow

| Token | Nilai |
|---|---|
| `radius-sm` | 8px |
| `radius-md` | 12px |
| `radius-lg` | 16px |
| `radius-pill` | 999px |
| `shadow-sm` | `0 1px 2px rgba(31,27,36,.06)` |
| `shadow-md` | `0 8px 24px rgba(31,27,36,.08)` |

Gunakan shadow hanya untuk card yang butuh elevasi. Jangan bayangan gelap.

### 2.5 Layout

- Max width: 1200px.
- Grid: 12 kolom, gap 24px.
- Padding halaman: 20px mobile, 32px tablet, 48px desktop.
- Breakpoint:
  - 360px
  - 768px
  - 1024px
  - 1280px

Mobile-first.

---

## 3. Komponen

### 3.1 Tombol

| Varian | Pemakaian |
|---|---|
| Primary | Aksi utama: Checkout, Simpan, Bayar |
| Secondary | Aksi pendukung: Lihat Detail |
| Ghost | Aksi ringan: Batal, Kembali |
| Danger | Hapus, Batalkan |

Aturan:

- Tinggi minimal 44px.
- Label jelas, bukan ikon saja.
- State: hover, focus-visible, disabled, loading.
- Loading memakai spinner + teks “Memproses…”.
- Satu layar maksimal satu tombol primary.

### 3.2 Form

- Label selalu di atas input.
- Placeholder bukan pengganti label.
- Helper text di bawah label.
- Error di bawah input, warna danger, `aria-invalid`.
- Required ditandai teks, bukan hanya warna.
- Input file menampilkan nama file dan batas ukuran.
- Fokus ring 2px primary.

### 3.3 Product card

Isi:

- gambar 4:5;
- nama produk;
- harga;
- ukuran tersedia;
- badge status jika perlu;
- tombol “Lihat Detail”.

Aturan:

- Jangan menampilkan terlalu banyak badge.
- Harga tebal, ukuran jelas.
- Jika stok habis, tampilkan “Stok habis” dan nonaktifkan aksi.

### 3.4 Status badge

- Pill, teks kecil, kontras cukup.
- Gunakan teks status, bukan hanya warna.
- Contoh: `Menunggu Pembayaran`, `Dikirim`, `Selesai`.

### 3.5 Table admin

- Header tebal, latar surface.
- Baris zebra tipis opsional.
- Aksi di kanan.
- Mobile: ubah menjadi card list atau scroll horizontal.
- Pagination di bawah tabel.

### 3.6 Empty state

- Ikon sederhana.
- Judul singkat.
- Satu kalimat penjelasan.
- Satu aksi utama.

Contoh:

> Keranjang masih kosong.  
> Mulai pilih sepatu favoritmu.  
> **Lihat Katalog**

### 3.7 Alert dan toast

- Success: hijau lembut.
- Warning: amber lembut.
- Danger: merah lembut.
- Info: biru lembut.
- Toast muncul 3–5 detik, bisa ditutup.

### 3.8 Modal

- Hanya untuk konfirmasi penting.
- Fokus terkunci di modal.
- Tombol tutup jelas.
- Jangan memakai modal untuk form panjang.

### 3.9 Quantity stepper

- Tombol minus/plus 44px.
- Input angka di tengah.
- Batas minimal 1 dan maksimal stok.
- Tampilkan stok tersedia.

### 3.10 Order summary

- Subtotal.
- Ongkir.
- Diskon jika ada.
- Total tebal.
- Catatan: “Total dihitung ulang di server.”

---

## 4. Aksesibilitas

- Kontras teks minimal 4.5:1.
- Fokus terlihat di semua elemen interaktif.
- Gunakan `label` untuk setiap input.
- Gunakan `aria-invalid` dan `aria-describedby` untuk error.
- Jangan mengandalkan warna saja untuk status.
- Navigasi keyboard berfungsi.
- Gambar produk punya `alt`.
- Hormati `prefers-reduced-motion`.
- Tombol minimal 44×44px.

---

## 5. Motion

- Durasi 150–200ms.
- Easing: `ease-out`.
- Hanya `transform` dan `opacity`.
- Hover card: naik 2px atau shadow tipis.
- Jangan memakai parallax, autoplay video, atau animasi scroll berat.
- Tidak perlu GSAP untuk Fase 1.

---

## 6. Nada tulisan

- Bahasa Indonesia yang jelas.
- Sapaan “Kak” boleh, tapi tidak berlebihan.
- Hindari “silakan” berulang.
- Error menjelaskan apa yang salah dan cara memperbaikinya.
- Contoh:
  - “Stok ukuran 38 habis. Coba ukuran lain.”
  - “Bukti transfer belum diunggah.”
  - “Pembayaran kedaluwarsa. Pesanan dibatalkan dan stok dilepas.”

---

## 7. CSS token contoh

```css
:root {
  --color-primary: #4B2E83;
  --color-primary-dark: #2E1B4F;
  --color-accent: #C08457;
  --color-bg: #FAF7F2;
  --color-surface: #FFFFFF;
  --color-ink: #1F1B24;
  --color-muted: #6B6475;
  --color-border: #E7E1EA;
  --color-success: #15803D;
  --color-warning: #B45309;
  --color-danger: #B91C1C;
  --color-info: #1D4ED8;

  --font-display: "Fraunces", Georgia, serif;
  --font-body: "Plus Jakarta Sans", system-ui, sans-serif;

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;

  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-6: 24px;
  --space-8: 32px;
  --space-12: 48px;
  --space-16: 64px;

  --shadow-sm: 0 1px 2px rgba(31, 27, 36, .06);
  --shadow-md: 0 8px 24px rgba(31, 27, 36, .08);

  --container: 1200px;
}
```

---

## 8. Aturan implementasi

- Pakai Blade component untuk button, input, card, badge, alert.
- Jangan menulis ulang class tombol di banyak file.
- Simpan token di satu file CSS.
- Jangan memakai library UI besar.
- Tailwind boleh, tetapi tetap gunakan token dan komponen.
- Semua state: default, hover, focus, disabled, loading, error, empty.
- Cek di 360px, 768px, dan 1280px.
- Audit akhir dengan `web-design-guidelines`.
