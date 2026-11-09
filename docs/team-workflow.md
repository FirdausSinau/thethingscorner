# Team Workflow & Pembagian Tugas — The Things Corner

**Versi:** 0.1  
**Tanggal:** 9 November 2026  
**Tim:** 3 orang  
**Branch:** `main`, `firdaus`, `anggota-a`, `anggota-b` (ganti dengan nama branch asli)

---

## 1. Prinsip pembagian

Jangan membagi hanya berdasarkan “frontend” dan “backend”. Dengan Laravel + Blade, satu fitur biasanya menyentuh route, controller, service, migration, Blade, dan test sekaligus. Kalau dipisah kaku, satu orang akan menunggu orang lain.

Yang lebih sehat:

> **Setiap orang memiliki domain fitur end-to-end, dengan backend core dan schema dipegang satu orang sebagai tech lead.**

Pembagian yang disarankan:

| Peran | Domain | Fokus |
|---|---|---|
| Firdaus | Backend core, auth, integrasi, deployment | Schema, auth/role, payment, shipping, scheduler, security, deploy, code review |
| Anggota A | Buyer storefront | Katalog, cart, checkout, pesanan pembeli, PO pembeli, review pembeli, design system |
| Anggota B | Admin operations & reporting | Dashboard admin, produk, stok, pesanan admin, PO admin, review admin, laporan, Shopee CSV |

Kalau salah satu anggota lebih kuat backend, tukar sebagian tugas. Yang penting **satu area punya satu owner utama**.

---

## 2. Branch strategy

| Branch | Fungsi |
|---|---|
| `main` | Stabil, deployable, hanya menerima merge via PR |
| `firdaus` | Branch kerja Firdaus |
| `anggota-a` | Branch kerja Anggota A |
| `anggota-b` | Branch kerja Anggota B |

Opsional, branch fitur pendek:

```text
firdaus/auth
anggota-a/catalog
anggota-b/admin-products
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
| `app/Http/Controllers/Storefront/` | Anggota A | Buyer |
| `app/Http/Controllers/Admin/` | Anggota B | Admin |
| `app/Http/Requests/` | Pemilik fitur | Form Request per domain |
| `app/Policies/` | Firdaus | Authorization shared |
| `app/Observers/` | Firdaus | Audit/log |
| `app/Http/Middleware/` | Firdaus | Auth/role |
| `resources/views/storefront/` | Anggota A | Halaman pembeli |
| `resources/views/admin/` | Anggota B | Halaman admin |
| `resources/views/components/` | Anggota A | Komponen shared; B memakai |
| `resources/views/layouts/storefront.blade.php` | Anggota A | Layout pembeli |
| `resources/views/layouts/admin.blade.php` | Anggota B | Layout admin |
| `resources/css/app.css` | Anggota A | Design token |
| `resources/js/app.js` | Anggota A | JS shared |
| `routes/web.php` | Firdaus | Hanya include file route |
| `routes/auth.php` | Firdaus | Login/register |
| `routes/storefront.php` | Anggota A | Route pembeli |
| `routes/admin.php` | Anggota B | Route admin |
| `routes/webhooks.php` | Firdaus | Payment/shipping |
| `config/services.php` | Firdaus | Provider config |
| `.env.example` | Firdaus | Jangan commit `.env` |
| `docs/` | Firdaus + semua | Update dokumen terkait |

Tujuannya: mengurangi konflik di `routes/web.php`, layout, dan migration.

---

## 4. Pembagian slice

| Slice | Nama | Owner utama | Support |
|---|---|---|---|
| 0 | Setup project | Firdaus | — |
| 1 | Auth dan role | Firdaus | A/B review |
| 2 | Katalog dan varian | Anggota A (publik), Anggota B (admin) | Firdaus model/migration |
| 3 | Keranjang persisten | Anggota A | Firdaus service review |
| 4 | Checkout dan reservasi | Anggota A | Firdaus service/transaction |
| 5 | Pembayaran | Firdaus | Anggota A UI pembeli |
| 6 | Pengiriman | Firdaus | Anggota B UI admin |
| 7 | Pesanan dan tracking | Anggota A (pembeli), Anggota B (admin) | Firdaus state machine |
| 8 | Custom PO | Anggota A (pembeli), Anggota B (admin) | Firdaus service/status |
| 9 | Review dan retur | Anggota A (pembeli), Anggota B (admin) | Firdaus policy |
| 10 | Stok dan kartu stok | Anggota B | Firdaus service |
| 11 | Shopee CSV | Anggota B (UI) | Firdaus import service |
| 12 | Laporan dan export | Anggota B (UI) | Firdaus query/export |
| 13 | Settings dan integrasi | Firdaus | Anggota B UI |
| 14 | Security, QA, UAT, deploy | Firdaus lead | Semua testing |

---

## 5. Urutan kerja paralel

Contoh urutan:

### Minggu 1

- Firdaus: schema awal, auth/role, layout skeleton, fake provider.
- Anggota A: design system, layout pembeli, katalog publik.
- Anggota B: layout admin, CRUD produk, varian, stok dasar.

### Minggu 2

- Firdaus: payment fake + transfer manual, shipping fake.
- Anggota A: cart, checkout, reservation.
- Anggota B: admin stok, admin pesanan, filter.

### Minggu 3

- Firdaus: payment/shipping sandbox, webhook, scheduler.
- Anggota A: tracking pembeli, PO pembeli, review.
- Anggota B: admin PO, admin review, laporan, Shopee CSV.

### Minggu 4

- Semua: integrasi, testing, UAT, perbaikan, deploy.
- Firdaus: security review, staging, production.
- Anggota B: QA checklist.
- Anggota A: UI audit dan responsive.

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
- Jika butuh komponen UI baru, ajukan ke Anggota A; jangan menduplikasi tombol/card.
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
| Anggota A | UI/UX, Blade, Tailwind, interaksi pembeli | Tinggi di storefront |
| Anggota B | Admin, laporan, data, QA | Tinggi di operasional |

Kalau ada yang lebih kuat di backend, tukar sebagian: misalnya Anggota B mengambil `StockService`, Anggota A mengambil `CartService`, Firdaus tetap memegang schema dan integrasi.

---

## 10. Yang tidak boleh dilakukan

- Semua orang push ke `main`.
- Satu orang mengerjakan seluruh backend sendirian tanpa review.
- Dua orang membuat migration untuk tabel yang sama.
- Dua orang mengedit `routes/web.php` bersamaan.
- Menunggu fitur selesai baru merge.
- Menaruh credential di branch/Git.
- Menganggap “frontend” tidak perlu memahami validasi dan authorization.
