# Team Workflow & Pembagian Tugas — The Things Corner

**Versi:** 0.3  
**Tanggal:** 9 November 2026  
**Tim:** 3 orang  
**Branch:** `main`, `firdaus`, `Anand`, `hafiez` (ganti dengan nama branch asli)

**Setup laptop:** lihat [`setup-local.md`](setup-local.md) sebelum pull pertama.  
**Pembagian kerja:** lihat [`work-plan.md`](work-plan.md).  
**Database bersama:** lihat [`database-rules.md`](database-rules.md) sebelum migrate ke Neon.

---

## 1. Prinsip pembagian

Jangan membagi hanya berdasarkan “frontend” dan “backend”. Dengan Laravel + Blade, satu fitur biasanya menyentuh route, controller, service, migration, Blade, dan test sekaligus. Kalau dipisah kaku, satu orang akan menunggu orang lain.

Yang lebih sehat:

> **Setiap orang memiliki domain fitur end-to-end, dengan backend core dan schema dipegang satu orang sebagai tech lead.**

Pembagian yang disarankan:

| Peran | Domain | Fokus |
|---|---|---|
| Firdaus | Backend core, auth, integrasi, deployment | Schema, auth/role, payment, shipping, scheduler, security, deploy, code review |
| Anand | Buyer storefront | Katalog, cart, checkout, pesanan pembeli, PO pembeli, review pembeli, design system |
| Hafiez | Admin operations & reporting | Dashboard admin, produk, stok, pesanan admin, PO admin, review admin, laporan, Shopee CSV |

Kalau salah satu anggota lebih kuat backend, tukar sebagian tugas. Yang penting **satu area punya satu owner utama**.

### 1.1 Per domain atau per slice?

Jawaban singkat:

> **Domain untuk ownership, slice untuk urutan delivery.**

Jangan membagi kepemilikan tetap per slice. Alasannya:

- Satu slice biasanya menyentuh migration, model, route, controller, Blade, dan test sekaligus.
- Slice berikutnya bergantung pada slice sebelumnya. Kalau pemiliknya berganti-ganti, semua orang harus memahami ulang kode yang sama.
- Konflik paling sering terjadi di migration, layout, dan service bersama, bukan di pembagian fitur.
- Domain yang stabil membuat seseorang makin cepat karena sudah mengenal model dan alur kerjanya.

Yang dipakai di project ini:

| Lapisan | Pemilik | Sifat |
|---|---|---|
| Schema, model, migration, seeder | Firdaus | Satu pintu, harus direview |
| Backend core dan service bersama | Firdaus | `CartService`, `CheckoutService`, `StockService`, payment, shipping |
| Buyer storefront | Anand | Katalog publik, cart, checkout UI, pesanan pembeli, PO pembeli, review pembeli |
| Admin operations | Hafiez | Dashboard admin, produk, stok, pesanan admin, PO admin, review admin, laporan, Shopee CSV |
| Integrasi eksternal | Firdaus | Payment, shipping, scheduler, webhook, deployment |
| QA dan UAT | Semua | Firdaus lead security, Hafiez lead operasional, Anand lead UI |

Slice tetap dipakai sebagai **unit pekerjaan dan PR**. Contoh: Anand mengerjakan Slice 3 (cart), sementara Hafiez mengerjakan admin produk dari Slice 2. Keduanya PR terpisah, tapi domainnya tidak berubah.

Aturan handoff:

- Kalau butuh tabel/kolom baru, ajukan ke Firdaus. Jangan buat migration sendiri untuk tabel yang sama.
- Kalau butuh method service baru, taruh di service domain dan minta review Firdaus.
- Kalau butuh komponen UI baru, Anand yang membuat; Hafiez memakai.
- Kalau butuh route admin, taruh di `routes/admin.php`; route pembeli di `routes/storefront.php`.
- Jangan menunggu satu slice selesai total baru merge. Pecah PR per subtile yang sudah bisa dites.

Kalau ada anggota yang ingin belajar backend, jangan langsung diberi payment/checkout. Mulai dari domain yang terisolasi:

1. Admin product CRUD dan validasi.
2. `StockService` + `stock_movements` + transaksi.
3. `ShopeeImportService` (CSV, validasi, idempotency).
4. Query laporan dan export.

Firdaus tetap memegang schema dan review transaksi. Penjelasan arsitektur backend ada di [`../docs/backend-overview.md`](../docs/backend-overview.md).

---

## 2. Branch strategy

| Branch | Fungsi |
|---|---|
| `main` | Stabil, deployable, hanya menerima merge via PR |
| `firdaus` | Branch kerja Firdaus |
| `Anand` | Branch kerja Anand |
| `hafiez` | Branch kerja Hafiez |

Opsional, branch fitur pendek:

```text
firdaus/auth
Anand/catalog
hafiez/admin-products
```

Aturan:

- Jangan push langsung ke `main`.
- Setiap PR minimal direview satu orang.
- Firdaus menjadi merger utama untuk `main`.
- Pull `main` setiap hari.
- Selesaikan konflik kecil sebelum menumpuk.
- Hapus branch fitur setelah merge; branch pribadi tetap ada.

Alur harian:

```bash
git checkout main
git pull origin main
git checkout firdaus
git merge main
# kerja
git add .
git commit -m "feat: ..."
git push origin firdaus
```

Buka PR ke `main` saat slice sudah bisa didemokan.

---

## 3. Kepemilikan folder

| Folder/file | Owner utama | Catatan |
|---|---|---|
| `database/migrations/` | Firdaus | Perubahan schema lewat issue/PR ke Firdaus |
| `app/Models/` | Firdaus | Relasi dan cast |
| `app/Services/` | Firdaus | Service shared; anggota bisa tambah method di file domainnya dengan review |
| `app/Http/Controllers/Storefront/` | Anand | Buyer |
| `app/Http/Controllers/Admin/` | Hafiez | Admin |
| `app/Http/Requests/` | Pemilik fitur | Form Request per domain |
| `app/Policies/` | Firdaus | Authorization shared |
| `app/Observers/` | Firdaus | Audit/log |
| `app/Http/Middleware/` | Firdaus | Auth/role |
| `resources/views/storefront/` | Anand | Halaman pembeli |
| `resources/views/admin/` | Hafiez | Halaman admin |
| `resources/views/components/` | Anand | Komponen shared; Hafiez memakai |
| `resources/views/layouts/storefront.blade.php` | Anand | Layout pembeli |
| `resources/views/layouts/admin.blade.php` | Hafiez | Layout admin |
| `resources/css/app.css` | Anand | Design token |
| `resources/js/app.js` | Anand | JS shared |
| `routes/web.php` | Firdaus | Hanya include file route |
| `routes/auth.php` | Firdaus | Login/register |
| `routes/storefront.php` | Anand | Route pembeli |
| `routes/admin.php` | Hafiez | Route admin |
| `routes/webhooks.php` | Firdaus | Payment/shipping |
| `config/services.php` | Firdaus | Provider config |
| `.env.example` | Firdaus | Jangan commit `.env` |
| `docs/` | Firdaus + semua | Dokumentasi teknis dan produk |
| `project-management/` | Firdaus + semua | Setup, pembagian kerja, data request, dokumen client |

Tujuannya: mengurangi konflik di `routes/web.php`, layout, dan migration.

---

## 4. Pembagian slice

| Slice | Nama | Owner utama | Support |
|---|---|---|---|
| 0 | Setup project | Firdaus | — |
| 1 | Auth dan role | Firdaus | Anand/Hafiez review |
| 2 | Katalog dan varian | Anand (publik), Hafiez (admin) | Firdaus model/migration |
| 3 | Keranjang persisten | Anand | Firdaus service review |
| 4 | Checkout dan reservasi | Anand | Firdaus service/transaction |
| 5 | Pembayaran | Firdaus | Anand UI pembeli |
| 6 | Pengiriman | Firdaus | Hafiez UI admin |
| 7 | Pesanan dan tracking | Anand (pembeli), Hafiez (admin) | Firdaus state machine |
| 8 | Custom PO | Anand (pembeli), Hafiez (admin) | Firdaus service/status |
| 9 | Review dan retur | Anand (pembeli), Hafiez (admin) | Firdaus policy |
| 10 | Stok dan kartu stok | Hafiez | Firdaus service |
| 11 | Shopee CSV | Hafiez (UI) | Firdaus import service |
| 12 | Laporan dan export | Hafiez (UI) | Firdaus query/export |
| 13 | Settings dan integrasi | Firdaus | Hafiez UI |
| 14 | Security, QA, UAT, deploy | Firdaus lead | Semua testing |

Cara membaca tabel ini: kolom owner menunjukkan **domain yang bertanggung jawab**, bukan orang yang mengerjakan seluruh slice sendirian. Slice 4, misalnya, tetap milik domain buyer (Anand), tetapi transaction dan row lock di `CheckoutService` ditulis/direview bersama Firdaus.

Pembagian detail per orang, urutan batch, dan checklist integrasi ada di [`work-plan.md`](work-plan.md).

---

## 4.1 Backend learning track

Untuk anggota yang ingin masuk backend, urutan yang paling aman:

| Tahap | Yang dikerjakan | Yang dipelajari |
|---|---|---|
| 1 | Admin product CRUD + FormRequest + feature test | request lifecycle, validasi, authorization |
| 2 | `StockService` + `stock_movements` | transaksi database, audit, mencegah stok negatif |
| 3 | `ShopeeImportService` | parsing CSV, validasi baris, idempotency |
| 4 | Query laporan dan export | query agregasi, index, performa |

Payment dan checkout ditunda sampai tahap 1-2 stabil karena menyentuh uang dan stok.

---

## 5. Urutan kerja

Urutan lengkap per bagian dan per batch ada di [`work-plan.md`](work-plan.md). Ringkasnya:

| Batch | Slice | Fokus |
|---|---|---|
| 0 | 0, 1 | Setup dan auth, sudah selesai |
| 1 | 2 | Katalog publik dan admin produk |
| 2 | 3, 4 | Cart dan checkout |
| 3 | 5, 6 | Pembayaran dan pengiriman |
| 4 | 7 | Pesanan dan tracking |
| 5 | 8, 9 | PO dan review |
| 6 | 10, 11, 12 | Stok, Shopee CSV, laporan |
| 7 | 13 | Settings dan integrasi |
| 8 | 14 | QA, UAT, deploy |

Aturan:

- Maksimal 2 slice aktif bersamaan.
- Firdaus publish schema dan kontrak route sebelum Anand/Hafiez mulai.
- Setiap batch diakhiri integrasi, test, dan demo.
- Jangan mulai batch berikutnya kalau dependency batch sebelumnya belum stabil.

---

## 6. Aturan PR

Setiap PR harus:

- fokus pada satu slice/subtile;
- menyertakan deskripsi singkat;
- menyebut issue;
- lulus `php artisan test`;
- lulus `npm run build`;
- tidak membawa `.env`, vendor, node_modules, build, atau file sensitif;
- menyertakan screenshot untuk perubahan UI;
- direview minimal satu orang.

Contoh judul PR:

```text
feat: add persistent cart
feat: add admin product CRUD
feat: add manual transfer verification
```

Reviewer fokus pada:

- authorization;
- validasi input;
- aturan bisnis di tempat yang benar;
- query N+1;
- error state;
- data sensitif.

---

## 7. Aturan konflik

- Jangan dua orang mengedit file yang sama dalam waktu lama.
- Jika butuh field/tabel baru, buat issue ke Firdaus; jangan buat migration sendiri untuk tabel yang sama.
- Jika butuh route baru, tambahkan di file route domain masing-masing, bukan di `web.php`.
- Jika butuh komponen UI baru, ajukan ke Anand; jangan menduplikasi tombol/card.
- Jika butuh service method baru, taruh di service domain; Firdaus review.
- Merge `main` ke branch sendiri setiap hari.
- Selesaikan konflik di branch sendiri, bukan di `main`.

---

## 8. Definition of Done

- Fitur bisa didemokan.
- Authorization diuji.
- Validasi input diuji.
- Feature test relevan lulus.
- UI responsif 360/768/1280.
- Tidak ada `dd()` atau debug.
- Commit jelas.
- PR direview.
- Dokumentasi diperbarui bila perlu.

---

## 9. Pembagian informal

| Orang | Kekuatan yang ideal | Beban |
|---|---|---|
| Firdaus | Backend, integrasi, arsitektur, Git | Tinggi, tapi sebagai tech lead |
| Anand | UI/UX, Blade, Tailwind, interaksi pembeli | Tinggi di storefront |
| Hafiez | Admin, laporan, data, QA | Tinggi di operasional |

Kalau ada yang lebih kuat di backend, tukar sebagian: misalnya Hafiez mengambil `StockService`, Shopee import, dan query laporan; Anand mengambil `CartService`. Firdaus tetap memegang schema, transaction review, dan integrasi eksternal.

Anggota yang baru belajar backend sebaiknya tidak memulai dari payment atau checkout. Mulai dari CRUD admin, lalu stok, lalu import CSV. Detail alur backend ada di [`../docs/backend-overview.md`](../docs/backend-overview.md).

---

## 10. Yang tidak boleh dilakukan

- Semua orang push ke `main`.
- Satu orang mengerjakan seluruh backend sendirian tanpa review.
- Dua orang membuat migration untuk tabel yang sama.
- Dua orang mengedit `routes/web.php` bersamaan.
- Menunggu fitur selesai baru merge.
- Menaruh credential di branch/Git.
- Menganggap “frontend” tidak perlu memahami validasi dan authorization.


