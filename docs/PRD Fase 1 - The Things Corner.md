# PRD Fase 1 — Website Toko & Manajemen Stok The Things Corner

**Versi:** 0.2  
**Tanggal:** 6 November 2026  
**Status:** Draft setelah review fitur  
**Referensi:** RFP v0.3, Form Deskripsi Topik dan Kebutuhan Awal Proyek  
**Produk:** Website toko, custom PO, stok, dan rekonsiliasi Shopee

---

## 1. Ringkasan

The Things Corner adalah UMKM sepatu handmade custom di Bandung yang menjual produk siap stok dan custom order melalui Shopee, Tokopedia, TikTok, dan WhatsApp. Masalah utamanya: stok Shopee dan stok gudang dicatat terpisah, penjualan Shopee dicocokkan manual, dan PO custom tersebar di chat.

Fase 1 membangun website yang menjadi sumber data untuk katalog, pesanan, pembayaran, pengiriman, PO, stok, review, dan laporan. Pembeli mendapat pengalaman yang jelas: cek stok, ongkir, QRIS/VA, tracking teks, dan riwayat pesanan. Admin Biasa mengurus operasional. Admin Master memegang laporan, moderasi, dan rekonsiliasi.

Prinsip produk:

- Sederhana dan bisa dijelaskan, bukan meniru marketplace besar.
- Fokus ke alur jual-beli, stok, PO, dan rekonsiliasi.
- Tidak menambah fitur yang tidak dipakai client.
- Selalu punya fallback manual.

### 1.1 Tujuan

- Menyatukan data stok, pesanan, dan PO.
- Mengurangi input manual dan selisih stok.
- Memberi pengalaman belanja yang mudah.
- Menyediakan laporan untuk mencocokkan stok dengan Shopee.
- Menjaga akses dan data tetap aman.

### 1.2 Ukuran keberhasilan

| Metrik | Target awal |
|---|---|
| Checkout selesai | ≥ 60% dari checkout yang dimulai |
| Pembayaran berhasil | ≥ 85% dari transaksi yang dibayar |
| Waktu proses pesanan | ≤ 1 hari kerja sebelum dikirim |
| Selisih stok harian | Menurun dibanding proses manual |
| Review terkumpul | ≥ 10% pesanan selesai |
| Waktu admin untuk rekap | Turun minimal 50% |

---

## 2. Ruang Lingkup

### 2.1 Fase 1

| Area | Fitur |
|---|---|
| Akun | Registrasi pembeli, login/logout, Admin Master, Admin Biasa, Pembeli |
| Katalog | Produk, varian ukuran/warna, foto, harga, stok tersedia, pencarian, filter |
| Keranjang | Persisten, tidak mengunci stok |
| Checkout | Alamat, kurir, ongkir, reservasi stok saat checkout |
| Pembayaran | QRIS/VA/e-wallet via payment gateway; transfer manual fallback |
| Pengiriman | Biteship: ongkir, pickup, label, tracking teks; resi manual fallback |
| Pesanan | Status, tracking, pembatalan sesuai aturan |
| Custom PO | Pengajuan, penawaran, DP, produksi, pelunasan, pengiriman |
| Review | Rating, komentar, foto, balas review, moderasi dasar |
| Retur | Manual melalui CS/`wa.me` |
| Admin | Produk, konten, harga, stok, pesanan, PO, review, akun admin |
| Stok | Kartu stok, mutasi, penyesuaian, reservasi saat checkout |
| Shopee | Import/export CSV dan laporan harian |
| Laporan | Penjualan, kartu stok, PO, rekonsiliasi, export Excel/CSV |
| Notifikasi | Pusat notifikasi web, email opsional, tombol `wa.me` |
| Media | Local storage untuk foto produk dan file privat untuk bukti transfer/referensi PO |

### 2.2 Fase 2 dan daftar pertimbangan

- Shopee Open Platform API.
- WA API dan WA omnichannel.
- Google Maps autocomplete.
- Analytics, retargeting, dan rekomendasi produk.
- Kupon/diskon.
- COD.
- Loyalty/poin.
- Multi-alamat UI lengkap.
- Sentry.
- PDF report.
- CAPTCHA.
- Cloudinary/object storage.
- Aplikasi mobile.
- Retur mandiri/refund otomatis.
- Multi-gudang, BOM, dan akuntansi.

### 2.3 Catatan fase

Fase 1 adalah baseline. Jika client serius, dokumen usaha lengkap, provider siap, data siap, dan waktu UAT cukup, fitur Fase 2 bisa digabung. Jika tidak, Fase 1 dulu agar toko bisa berjalan.

---

## 3. Persona dan Role

| Role | Siapa | Kebutuhan utama | Batasan |
|---|---|---|---|
| Pembeli | Pelanggan toko | Katalog, stok, checkout, pembayaran, tracking, review, PO, retur via CS | Hanya data miliknya |
| Admin Biasa | Staf operasional | Produk, stok, pesanan, pengemasan, label, status PO, chat, balas review, retur | Tidak bisa kelola akun admin, hapus data, refund, atau laporan keuangan |
| Admin Master | Pemilik/pengelola utama | Semua data, laporan, rekonsiliasi, refund, moderasi review, pengaturan integrasi | Tidak ada batasan dalam sistem |

### 3.1 Access matrix

| Aktivitas | Pembeli | Admin Biasa | Admin Master |
|---|---|---|---|
| Lihat katalog | Ya | Ya | Ya |
| Checkout | Ya | Tidak | Tidak |
| Lihat pesanan sendiri | Ya | Ya | Ya |
| Lihat semua pesanan | Tidak | Ya | Ya |
| Ubah produk/harga | Tidak | Ya | Ya |
| Ubah stok | Tidak | Ya | Ya |
| Verifikasi transfer manual | Tidak | Ya | Ya |
| Refund | Tidak | Tidak | Ya |
| Review produk | Ya, miliknya | Tidak | Tidak |
| Balas review | Tidak | Ya | Ya |
| Sembunyikan review | Tidak | Tidak | Ya |
| Retur/komplain | Ya, via CS | Ya | Ya |
| Kelola akun admin | Tidak | Tidak | Ya |
| Laporan keuangan | Tidak | Tidak | Ya |
| Pengaturan integrasi | Tidak | Tidak | Ya |
| Rekonsiliasi Shopee | Tidak | Lihat | Ya |

---

## 4. User Journey

### 4.1 Pembeli — produk siap stok

1. Buka katalog, cari produk, pilih varian.
2. Masukkan ke keranjang. Keranjang persisten dan tidak mengunci stok.
3. Checkout: pilih alamat, kurir, dan ongkir.
4. Sistem membuat pesanan dan menahan stok sementara.
5. Pilih QRIS/VA/e-wallet atau transfer manual.
6. Bayar; status berubah otomatis atau menunggu verifikasi.
7. Admin mengemas dan membuat pickup/label Biteship.
8. Pembeli melihat tracking teks.
9. Pesanan selesai; pembeli dapat memberi review dalam 30 hari.
10. Jika ada masalah, pembeli menghubungi CS via `wa.me` atau mengajukan retur manual.

### 4.2 Pembeli — custom PO

1. Isi form PO: model, bahan, ukuran, referensi gambar, catatan.
2. Admin Master meninjau dan memberi harga, estimasi, dan DP.
3. Pembeli menyetujui dan membayar DP.
4. Admin Biasa memperbarui status produksi.
5. Setelah selesai, pembeli melunasi.
6. Admin mengirim; pembeli melihat tracking.
7. PO selesai.

### 4.3 Admin Biasa — harian

- Cek pesanan baru dan pembayaran.
- Verifikasi transfer manual.
- Siapkan pesanan, cetak label Biteship, request pickup.
- Update status dan resi.
- Update status PO.
- Balas chat dan review.
- Tangani retur/komplain via `wa.me`.
- Cek stok kritis dan ajukan penyesuaian.

### 4.4 Admin Master — mingguan

- Cek penjualan, stok, PO, dan review.
- Cocokkan stok sistem dengan Shopee.
- Setujui penyesuaian/refund.
- Moderasi review.
- Export laporan.
- Cek kesehatan integrasi dan error.

---

## 5. Functional Requirements

| ID | Requirement | Prioritas | Catatan |
|---|---|---|---|
| FR-01 | Pembeli dapat registrasi, login, logout, dan reset password | Wajib | Email unik; password hash |
| FR-02 | Sistem membedakan role dan menolak akses tidak sah di server | Wajib | Middleware + policy |
| FR-03 | Admin Master dapat membuat/menonaktifkan Admin Biasa | Wajib | Tidak ada registrasi admin publik |
| FR-04 | Admin Master dibuat lewat seeder | Wajib | Password wajib diganti |
| FR-05 | Katalog menampilkan produk, varian, harga, stok tersedia, foto | Wajib | Filter dan pencarian |
| FR-06 | Admin Biasa dapat mengelola produk, konten, harga, foto | Wajib | Perubahan harga tidak mengubah pesanan lama |
| FR-07 | Keranjang persisten untuk pembeli login | Wajib | Tidak mengunci stok |
| FR-08 | Checkout memvalidasi stok, alamat, kurir, total | Wajib | Reservasi stok saat checkout |
| FR-09 | Pembayaran QRIS/VA/e-wallet melalui payment gateway | Wajib | Webhook + signature |
| FR-10 | Transfer manual dengan upload bukti dan verifikasi admin | Wajib | Fallback |
| FR-11 | Ongkir, pickup, label, tracking melalui Biteship | Wajib | Fallback flat/manual |
| FR-12 | Pesanan memiliki status dan tracking teks | Wajib | Auto-selesai 7 hari |
| FR-13 | Custom PO memiliki alur pengajuan, penawaran, DP, produksi, pelunasan | Wajib | Status terkontrol |
| FR-14 | Pembeli dapat review setelah selesai, maksimal 30 hari | Wajib | Satu review per order item |
| FR-15 | Toko dapat membalas review satu kali | Wajib | Admin Biasa/Admin Master |
| FR-16 | Admin Master dapat menyembunyikan review dengan alasan | Wajib | Audit |
| FR-17 | Setiap perubahan stok tercatat di kartu stok | Wajib | Sumber dan referensi |
| FR-18 | Admin dapat import/export CSV Shopee | Wajib | Validasi duplikat |
| FR-19 | Laporan penjualan harian, kartu stok, PO, rekonsiliasi | Wajib | Export Excel/CSV |
| FR-20 | Pusat notifikasi web dan tombol `wa.me` | Wajib | Tidak ada WA API |
| FR-21 | Observer mencatat perubahan status/stok penting | Wajib | Tidak menyimpan data sensitif |
| FR-22 | Retur/komplain manual melalui CS/`wa.me` | Wajib | Tidak ada alur otomatis |
| FR-23 | Tabel alamat per user; UI satu alamat default | Wajib | Multi-alamat UI fase 2 |
| FR-24 | Pembeli dapat membatalkan pesanan sebelum diproses | Pendukung | Sesuai aturan |
| FR-25 | Admin Master dapat refund | Pendukung | Manual/gateway |
| FR-26 | Sistem menyimpan sumber Shopee: manual/CSV/API | Wajib | Mencegah duplikasi |
| FR-27 | UI responsif, aksesibel, dan sederhana | Wajib | Tidak perlu animasi kompleks |

---

## 6. Business Rules

| ID | Aturan | Alasan |
|---|---|---|
| BR-01 | Stok tidak boleh negatif; pesanan siap stok hanya dikonfirmasi jika stok cukup | Mencegah overselling |
| BR-02 | Setiap perubahan stok harus melalui mutasi dan tercatat | Audit dan rekonsiliasi |
| BR-03 | Satu pesanan Shopee hanya dicatat sekali berdasarkan nomor unik | Mencegah stok ganda |
| BR-04 | Custom PO masuk produksi setelah DP dikonfirmasi; pembatalan setelah produksi hanya oleh Admin Master | Melindungi produksi |
| BR-05 | Selisih stok diselesaikan lewat penyesuaian beralasan; riwayat tidak dihapus | Audit |
| BR-06 | Produk/varian dengan riwayat transaksi tidak boleh dihapus | Integritas data |
| BR-07 | Perubahan harga hanya berlaku untuk pesanan baru | Nilai transaksi stabil |
| BR-08 | Keranjang persisten dan tidak mengunci stok. Reservasi dimulai saat checkout: 30 menit QRIS/VA, 60 menit transfer manual | Mencegah stok tertahan |
| BR-09 | Setiap data Shopee menyimpan sumber dan nomor referensi unik | Mencegah duplikasi |
| BR-10 | Jika pihak ketiga gagal, transaksi tidak dianggap selesai; fallback manual | Mencegah status palsu |
| BR-11 | Review hanya untuk order item selesai, maksimal 30 hari, satu review per order item; toko boleh membalas satu kali | Mencegah review palsu |
| BR-12 | Retur/komplain manual melalui CS/`wa.me`; tidak ada refund otomatis | Menjaga scope |
| BR-13 | Pembeli wajib login untuk checkout, PO, dan review | Akun dan riwayat |
| BR-14 | Model stok (satu pool/terpisah/alokasi) masih menunggu keputusan client | Memengaruhi perhitungan |

---

## 7. Data Requirements

> Ini kebutuhan field, bukan data asli client. Data asli menyusul saat seeding/UAT.

| Entitas | Field utama | Relasi/Constraint |
|---|---|---|
| users | name, email, phone, password, role, is_active | role: buyer/admin/admin_master |
| addresses | user_id, label, recipient, phone, province, city, district, postal_code, detail, is_default | belongsTo user; satu default |
| categories | name, slug | hasMany products |
| products | name, slug, category_id, description, status, weight_grams, rating_avg, rating_count | hasMany variants |
| stock_reservations | order_id, variant_id, qty, expires_at, status | aktif saat checkout; index expires_at |
| product_variants | product_id, sku, size, color, price, stock_on_hand, stock_reserved | unique sku |
| stock_movements | variant_id, type, source, reference_type, reference_id, qty, stock_before, stock_after, note, actor_id | immutable log |
| cart_items | user_id, variant_id, qty | keranjang persisten |
| orders | user_id, address_id, channel, status, subtotal, shipping_cost, discount, total, paid_at, completed_at | hasMany items |
| order_status_logs | order_id, from_status, to_status, actor_id, note | append-only |
| order_items | order_id, variant_id, product_name, sku, qty, price, subtotal | snapshot harga |
| payments | order_id, provider, method, external_id, amount, status, proof_path, paid_at, verified_by | unique external_id |
| shipments | order_id, courier, service, tracking_number, cost, status, shipped_at, delivered_at | belongsTo order |
| custom_pos | user_id, specification, reference_images, price, dp_amount, dp_paid_at, status, deadline | hasMany status logs |
| po_status_logs | custom_po_id, from_status, to_status, actor_id, note, created_at | immutable |
| reviews | order_item_id, product_id, user_id, rating, comment, reply_text, replied_by, replied_at, is_hidden, hidden_reason | unique order_item_id |
| review_photos | review_id, path, sort_order | belongsTo review |
| notifications | user_id, type, title, body, read_at | pusat notifikasi |
| shopee_imports | filename, source, rows_total, rows_imported, rows_failed, imported_by | audit |
| integration_logs | provider, event, external_id, status, request_summary, response_summary | tanpa secret |
| settings | key, value, type, group | payment/shipping/store |

**Catatan:** `stock_on_hand` dan `stock_reserved` disimpan untuk performa, tetapi semua perubahan tetap lewat service dan mutasi.

---

## 8. Non-Functional Requirements

| Aspek | Requirement |
|---|---|
| Keamanan | CSRF, validasi upload, password hash, rate limit login, policy server-side, OWASP |
| Privasi | Pembeli hanya lihat data sendiri; bukti transfer dan referensi PO private |
| Performa | Katalog < 2 detik; gambar dioptimasi; pagination |
| Aksesibilitas | Label form, focus ring, kontras, aria-invalid, keyboard navigation |
| Responsif | 360px, 768px, 1280px |
| Reliabilitas | Webhook idempotent, retry, fallback manual, health check |
| Observability | Log Laravel; Sentry fase 2 |
| Backup | Backup database harian; restore diuji |
| Maintainability | Service/Form Request/Policy terpisah; test untuk aturan penting |
| Desain | Sederhana, jelas, tidak perlu animasi kompleks |

---

## 9. Integrasi dan Fallback

| Integrasi | Fungsi | Fallback |
|---|---|---|
| Midtrans/Xendit | QRIS, VA, e-wallet, webhook | Transfer manual + bukti |
| Biteship | Ongkir, pickup, label, tracking teks | Flat per zona + resi manual |
| Shopee CSV | Pesanan/stok harian | Input manual |
| `wa.me` | CS, retur, update manual | WhatsApp Business |
| Email SMTP | Reset password, invoice | Admin reset manual |
| Local storage + Cloudflare | Foto dan file privat | Object storage fase 2 |

### 9.1 Aturan integrasi

- Key di `.env` dan `config/services.php`; tidak di Git.
- Webhook di route tanpa CSRF; verifikasi signature/token.
- Simpan `external_id` unik; idempotent.
- Simpan log tanpa data sensitif.
- Queue untuk retry; timeout.
- Jika provider gagal, jangan gagalkan checkout; gunakan fallback.
- Uji sandbox sebelum production.

### 9.2 Ditunda

- WA API.
- Shopee API.
- Google Maps.
- Cloudinary.
- Sentry.
- PDF report.
- CAPTCHA.

---

## 10. Screen List dan State

### 10.1 Pembeli

- Home/landing.
- Katalog dan pencarian.
- Detail produk dan varian.
- Keranjang.
- Checkout dan alamat.
- Pembayaran (QRIS/VA/transfer).
- Status pesanan dan tracking teks.
- Akun: profil, alamat, pesanan, PO, notifikasi.
- Form custom PO.
- Detail PO dan status.
- Review form.
- Tombol retur/CS via `wa.me`.

### 10.2 Admin

- Dashboard Admin Biasa dan Admin Master.
- Produk dan varian.
- Stok dan kartu stok.
- Pesanan dan detail.
- Pengiriman/Biteship.
- Custom PO dan status.
- Review, balas review, moderasi.
- Retur/komplain.
- Import/export Shopee.
- Laporan dan export.
- Akun admin (Admin Master).
- Pengaturan integrasi (Admin Master).

### 10.3 State UI

Setiap daftar/form harus punya state: loading, empty, error, success, disabled, unauthorized.

---

## 11. Acceptance Criteria

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| AC-01 | Registrasi, login, logout, guest buka halaman protected | Berhasil; guest diarahkan ke login |
| AC-02 | Role Buyer membuka halaman admin | 403 |
| AC-03 | Admin Biasa membuka kelola akun admin | 403 |
| AC-04 | Katalog, filter, pencarian, varian | Hasil sesuai; stok tersedia benar |
| AC-05 | Masuk keranjang, tunggu lama | Stok tidak berubah; keranjang tetap |
| AC-06 | Checkout stok habis | Ditolak; tidak ada order |
| AC-07 | Checkout berhasil, reservasi 30/60 menit | Stok ditahan; order dibuat |
| AC-08 | QRIS/VA berhasil | Webhook mengubah status; stok berkurang sekali |
| AC-09 | Transfer manual | Bukti tersimpan; admin verifikasi; status benar |
| AC-10 | Biteship ongkir/tracking | Ongkir tampil; resi tersimpan; tracking terlihat |
| AC-11 | Biteship gagal | Fallback flat/manual; checkout tetap bisa |
| AC-12 | Pesanan selesai | Auto-selesai 7 hari; review terbuka 30 hari |
| AC-13 | Review ganda / di luar 30 hari | Ditolak server |
| AC-14 | Balas review | Balasan tampil; satu balasan |
| AC-15 | Admin Master sembunyikan review | Review hilang dari publik; audit |
| AC-16 | Custom PO | DP, produksi, pelunasan, kirim, selesai |
| AC-17 | PO tanpa DP masuk produksi | Ditolak |
| AC-18 | Import CSV Shopee duplikat | Ditolak; tidak mengurangi stok dua kali |
| AC-19 | Kartu stok dan laporan harian | Angka cocok; export bisa dibuka |
| AC-20 | Webhook palsu/gagal | Ditolak; status tidak berubah |
| AC-21 | Retur/komplain | Diarahkan ke CS/`wa.me`; tidak ada refund otomatis |
| AC-22 | Guest checkout | Ditolak; wajib login |

---

## 12. Out of Scope Fase 1

- Shopee API otomatis.
- WA API dan WA omnichannel.
- Google Maps.
- Analytics dan rekomendasi.
- Kupon/diskon.
- COD.
- Loyalty/poin.
- Multi-alamat UI lengkap.
- Sentry.
- PDF report.
- CAPTCHA.
- Cloudinary/object storage.
- Aplikasi mobile.
- Retur mandiri/refund otomatis.
- Multi-gudang, BOM, akuntansi.
- Wishlist, Q&A, live chat.
- Guest checkout.
- Migrasi otomatis 164 produk Shopee.

---

## 13. Asumsi dan Pertanyaan Terbuka

| Topik | Asumsi sementara | Perlu keputusan |
|---|---|---|
| Model stok | Satu pool fisik | Client |
| Payment gateway | Midtrans, fallback transfer | Client |
| DP PO | 50% | Client |
| Review | 30 hari, satu per order item, boleh balas | Client |
| Shipping | Biteship; fallback flat | Client |
| WA | `wa.me`; WA API fase 2 | Client |
| Stock opname | Manual oleh Admin Biasa, final Admin Master | Client |
| Data produk | Import CSV + foto | Client |
| Alamat gudang | Bandung | Client |
| Retur | Manual via CS | Client |
| Multi-alamat | Tabel disiapkan, UI satu default | Client |

---

## 14. Roadmap Fase 2

1. Shopee Open Platform API.
2. WA API/omnichannel.
3. Google Maps autocomplete.
4. Analytics dan retargeting.
5. Kupon/diskon, COD, loyalty.
6. Multi-alamat UI lengkap.
7. Sentry, PDF report, CAPTCHA.
8. Cloudinary/object storage.
9. Retur mandiri/refund otomatis.
10. Multi-gudang, BOM, akuntansi, mobile app.

---

## 15. Definition of Done

- Requirement dan acceptance criteria terpenuhi.
- Feature test untuk aturan penting lulus.
- Tidak ada akses tanpa izin.
- Webhook dan fallback diuji.
- UI responsif dan aksesibel.
- Security review Laravel OWASP.
- Laporan bisa diexport.
- Dokumentasi dan akun demo tersedia.
- Client UAT selesai.
- Deployment staging/production dan backup berjalan.

---

## 16. Change Log

| Versi | Tanggal | Perubahan |
|---|---|---|
| 0.1 | 6 Nov 2026 | Draft awal berdasarkan RFP v0.2 |
| 0.2 | 6 Nov 2026 | Keranjang persisten; reservasi saat checkout; WA API, Google Maps, Cloudinary, Sentry, PDF, CAPTCHA ditunda; review reply dan retur manual ditambahkan |
