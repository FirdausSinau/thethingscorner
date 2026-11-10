# Aturan Database Neon dan Migrasi

**Versi:** 1.1  
**Tanggal:** 10 November 2026  
**Status:** Aktif  
**Berlaku untuk:** Firdaus, Anggota A, Anggota B

Dokumen ini dipakai kalau tim memakai Neon sebagai database bersama. Aturan yang sama berlaku untuk staging dan production.

**Keputusan tim saat ini:** satu database Neon bersama, tanpa branch. Karena itu aturan ketat di dokumen ini berlaku: hanya satu migration owner, tidak ada `migrate:fresh`, dan test tetap memakai SQLite.

---

## 1. Keputusan

Neon boleh dipakai, tetapi **satu database bersama tidak boleh dipakai untuk kerja harian semua orang tanpa aturan**.

Dua model yang diperbolehkan:

| Model | Keterangan | Rekomendasi |
|---|---|---|
| Lokal + Neon staging | Kerja harian di PostgreSQL lokal, Neon untuk demo/UAT | Paling disarankan |
| Neon branch per orang | Setiap orang punya branch Neon sendiri, `main` untuk staging | Disarankan kalau tidak mau install lokal |
| Satu Neon branch bersama | Semua orang connect ke branch yang sama | Boleh, tetapi aturannya paling ketat |

Kalau memakai satu branch bersama, hanya **satu orang** yang boleh menjalankan migrasi.

---

## 2. Peran

| Peran | Siapa | Tugas |
|---|---|---|
| Migration owner | Firdaus | Satu-satunya yang menjalankan migrasi ke Neon staging/main |
| Application role | Aplikasi | Role database dengan hak DML, bukan owner |
| Migration role | Firdaus | Role dengan hak DDL, hanya dipakai saat migrasi |
| Developer | A dan B | Migrasi hanya di lokal atau branch Neon sendiri |

Jangan memakai role `neondb_owner` untuk aplikasi sehari-hari. Buat role aplikasi yang hanya boleh `SELECT`, `INSERT`, `UPDATE`, `DELETE`.

---

## 3. Boleh dan tidak boleh

### Boleh

- `php artisan migrate:status` untuk melihat status.
- `php artisan migrate --pretend` untuk melihat SQL yang akan dijalankan.
- `php artisan migrate` di database lokal sendiri.
- `php artisan migrate --force` di Neon oleh migration owner, setelah backup.
- `php artisan db:seed --class=DemoUserSeeder` di lokal atau branch pribadi.
- `php artisan test` dengan SQLite in-memory.

### Tidak boleh

- `php artisan migrate:fresh` di Neon bersama.
- `php artisan migrate:refresh` di Neon bersama.
- `php artisan migrate:reset` di Neon bersama.
- `php artisan db:wipe` di Neon bersama.
- `php artisan migrate:rollback` tanpa persetujuan dan backup.
- Menjalankan `php artisan test` ke Neon.
- `TRUNCATE`, hapus massal, atau update massal tanpa koordinasi.
- Dua orang menjalankan migrasi ke Neon bersamaan.
- Mengedit migration lama yang sudah pernah dijalankan.
- Menaruh connection string atau password di Git, screenshot, atau dokumen publik.

### Kenapa migrate bersama tidak aman

Menjalankan semua file migration di database kosong itu normal. Yang tidak aman adalah **beberapa orang menjalankan migrate ke satu database yang sama**.

Alasannya bukan karena kode migration-nya pasti salah, tetapi karena:

- Migration mengubah schema, bukan hanya data.
- Dua proses migrate bersamaan bisa saling mengunci tabel atau berebut mencatat migration yang sama.
- Satu orang bisa menjalankan migration yang belum ada di branch orang lain, sehingga kode dan schema tidak cocok.
- `migrate:rollback` oleh satu orang mengubah schema untuk semua orang.
- Migration yang menghapus atau mengubah kolom bisa menghilangkan data.
- `php artisan migrate` tidak menjalankan seeder. Seeder hanya jalan kalau dipanggil terpisah.
- `migrate:fresh --seed` menghapus semua tabel lalu mengisi ulang data demo. Data yang dibuat setelah seeder ikut hilang.

Seeder hanya mengembalikan **data awal**. Seeder tidak mengembalikan:

- order dan item pesanan yang sedang dites;
- cart dan reservasi stok;
- pembayaran dan bukti transfer;
- review dan balasan;
- custom PO dan log status;
- stock movement dan hasil import Shopee;
- file upload seperti foto produk dan bukti transfer;
- session, log, dan data yang dibuat manual di pgAdmin.

Jadi "ada seeder" bukan jaminan aman. Seeder hanya membuat data demo bisa dibuat ulang, bukan membuat migrate bersama menjadi aman.

---

## 4. Alur perubahan schema

1. Developer membuat migration baru di branch sendiri.
2. Jalankan di database lokal atau branch Neon pribadi.
3. Tulis feature test yang relevan.
4. Buat PR. Migration ikut direview.
5. Merge ke `main`.
6. Migration owner membuat backup branch Neon sebelum migrasi.
7. Jalankan `php artisan migrate:status`.
8. Jalankan `php artisan migrate --pretend` dan cek SQL-nya.
9. Jalankan `php artisan migrate --force` di Neon staging.
10. Jalankan `php artisan migrate:status` lagi.
11. Smoke test halaman utama, login, dan fitur yang berubah.
12. Umumkan di grup bahwa database sudah dimigrasi.

Kalau langkah 9 gagal, jangan panic edit database manual. Restore dari backup branch Neon, lalu perbaiki migration di lokal.

---

## 5. Aturan migration

- Satu migration untuk satu perubahan yang jelas.
- Jangan mengedit migration yang sudah dijalankan di staging. Buat migration baru.
- Migration harus backward-compatible sebisa mungkin:
  - tambah kolom nullable atau punya default;
  - jangan rename/hapus kolom dalam satu langkah;
  - pisahkan add dan drop ke deployment berbeda.
- Selalu review foreign key dan index.
- Jangan menambah kolom tanpa update model, seeder, dan test.
- Migration tidak boleh bergantung pada data yang belum ada.
- Data lama harus tetap aman.

---

## 6. Aturan Neon branch

Kalau memakai Neon branch:

```text
main        -> staging, hanya migration owner yang menulis schema
firdaus     -> branch kerja Firdaus
anggota-a   -> branch kerja Anggota A
anggota-b   -> branch kerja Anggota B
```

Aturan:

- Branch pribadi boleh dimigrasi dan di-reset sendiri.
- Branch pribadi dibuat dari `main` yang sudah stabil.
- Setelah migration masuk `main`, branch pribadi boleh dibuat ulang dari `main` atau menjalankan migration yang sama.
- Jangan merge branch database. Yang di-merge adalah kode.
- Connection string tiap branch disimpan di `.env` masing-masing, bukan di Git.
- Hapus branch pribadi yang sudah tidak dipakai supaya tidak bingung.

---

## 7. Aturan data dan seeder

- Neon hanya untuk data demo, bukan data pelanggan asli.
- Seeder harus idempotent. `updateOrCreate` lebih aman daripada `create`.
- Jangan menjalankan seeder di staging tanpa memberi tahu tim.
- Jangan mengandalkan data yang dibuat manual di pgAdmin. Semua data demo harus bisa dibuat ulang dari seeder.
- Import CSV dan perubahan stok massal harus memakai transaction.
- Sebelum demo, buat data demo dari seeder, bukan input manual yang tidak tercatat.

---

## 8. Aturan testing

- Test tetap memakai SQLite in-memory sesuai `phpunit.xml`.
- Jangan mengarahkan `DB_CONNECTION` test ke Neon.
- Test fitur ke database nyata hanya boleh di branch pribadi.
- Jalankan `php artisan test` sebelum membuat PR.
- Jalankan `npm run build` sebelum PR yang mengubah UI.

---

## 9. Backup dan recovery

Sebelum migrasi ke Neon:

1. Buat branch backup dari `main`, misalnya `backup-20261109-1`.
2. Atau jalankan `pg_dump` jika tersedia.
3. Catat migration yang akan dijalankan.
4. Pastikan tidak ada demo atau UAT berjalan.

Kalau terjadi kesalahan:

1. Hentikan aplikasi sementara jika perlu.
2. Jangan menjalankan `migrate:fresh`.
3. Restore branch backup atau gunakan fitur restore Neon.
4. Perbaiki migration di lokal.
5. Ulangi proses dari awal.

---

## 10. Keamanan credential

- `.env` dan `.env.*` tidak masuk Git.
- Connection string Neon hanya di `.env` masing-masing.
- Jangan mengirim connection string lengkap di grup atau screenshot.
- Kalau credential terekspos, reset password role Neon.
- Gunakan `sslmode=require`.
- Jangan memakai role owner untuk aplikasi.
- Jangan menaruh password di dokumen `.md` yang di-commit.

---

## 11. Checklist sebelum migrasi Neon

- [ ] Migration sudah dites di lokal atau branch pribadi.
- [ ] Feature test lulus.
- [ ] PR sudah direview dan merge ke `main`.
- [ ] Backup branch Neon sudah dibuat.
- [ ] Tidak ada proses demo/UAT berjalan.
- [ ] `migrate:status` sudah dicek.
- [ ] `migrate --pretend` sudah dicek.
- [ ] Migration dijalankan oleh satu orang saja.
- [ ] `migrate:status` setelah migrasi sudah dicek.
- [ ] Smoke test login, dashboard, dan fitur terkait sudah dilakukan.
- [ ] Tim sudah diberi tahu.

---

## 12. Insiden 10 November 2026 - tabrakan migration katalog

Migration katalog dari tim produk dijalankan ke Neon setelah migration katalog awal. Akibatnya:

- `products` berubah: `status`, `weight_grams`, `rating_avg`, `rating_count` hilang; muncul `base_price`, `is_active`, `is_preorder`, dan tanggal preorder.
- `product_variants` berubah: `product_id`, `color`, `stock_on_hand`, `stock_reserved` hilang; muncul `product_color_id` dan `stock`.
- Muncul tabel `product_colors` dan `product_color_images`.
- Tabel `migrations` mencatat dua set migration dengan nama sama dan timestamp berbeda.
- Data katalog awal 10 produk/30 varian hilang, diganti 1 produk/2 warna/7 varian.
- `Product::active()` gagal karena kolom `status` tidak ada.

Backup kondisi setelah insiden:

```text
%TEMP%\ttc-neon-after-teammate-migrations.sql
```

Aturan selama rekonsiliasi:

- Schema freeze untuk `products`, `product_variants`, `product_colors`, `product_color_images`, dan `product_images`.
- Hanya pemilik schema produk yang boleh menjalankan migration untuk tabel tersebut.
- Firdaus fokus ke auth, core, dan service yang tidak mengubah schema produk.
- Jangan menjalankan `migrate:fresh`, rollback, atau drop table sampai tim setuju.
- Sebelum adaptasi kode, migration dan model dari tim produk harus di-push ke GitHub.
- Setelah schema kanonik dipilih, update model, seeder, contract, dan test, lalu jalankan satu migration rekonsiliasi.
- Jangan menambah tabel/kolom baru untuk diskon atau produk reject selama schema freeze.

---

## 13. Ringkasan

- Migrate tetap boleh, tetapi terpusat.
- Yang tidak boleh: semua orang migrate ke satu database bersama.
- Yang tidak boleh: `migrate:fresh` di Neon bersama.
- Yang tidak boleh: test ke Neon.
- Yang tidak boleh: credential di Git.
- Untuk kerja harian, gunakan lokal atau branch Neon pribadi.
- Untuk staging, hanya migration owner yang menjalankan migrasi.
