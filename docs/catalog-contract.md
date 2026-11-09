# Catalog Contract - Slice 2

**Versi:** 1.0  
**Tanggal:** 9 November 2026  
**Untuk:** Firdaus, Anggota A, Anggota B  
**Status:** Schema dan seeder selesai, halaman publik dan admin belum

Dokumen ini adalah kontrak kerja Slice 2. A dan B tidak perlu menebak nama tabel, kolom, relasi, route, atau bentuk data.

---

## 1. Yang sudah selesai

- Migration `categories`, `products`, `product_variants`, `product_images`.
- Model `Category`, `Product`, `ProductVariant`, `ProductImage`.
- Enum `ProductStatus`.
- Seeder `DemoCatalogSeeder`.
- 10 produk, 7 kategori, 30 varian dari `docs/data`.
- Test seeder, scope, dan stok tersedia.

Yang belum:

- route katalog publik;
- controller katalog publik;
- CRUD admin produk dan varian;
- upload dan optimasi foto;
- halaman detail produk.

---

## 2. Schema

### categories

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| name | string | contoh: Mules |
| slug | string | unik, contoh: mules |
| created_at, updated_at | timestamp | |

### products

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| category_id | bigint | FK ke categories, restrict delete |
| name | string | |
| slug | string | unik |
| description | text | nullable |
| status | string(20) | `active` atau `inactive` |
| weight_grams | integer | berat untuk ongkir |
| rating_avg | decimal(3,2) | default 0 |
| rating_count | integer | default 0 |
| created_at, updated_at | timestamp | |

### product_variants

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| product_id | bigint | FK ke products, restrict delete |
| sku | string | unik |
| size | string(20) | contoh: 38 |
| color | string(50) | contoh: Black |
| price | decimal(12,2) | Rupiah |
| stock_on_hand | integer | stok fisik |
| stock_reserved | integer | ditahan saat checkout |
| created_at, updated_at | timestamp | |

Constraint:

- unik `(product_id, size, color)`;
- `stock_on_hand >= 0`;
- `stock_reserved >= 0`.

### product_images

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| product_id | bigint | FK ke products, cascade delete |
| path | string | path relatif di disk `public` |
| alt_text | string | nullable |
| sort_order | integer | urutan foto |
| created_at, updated_at | timestamp | |

Catatan: tabel ini adalah tambahan implementasi untuk galeri produk. Belum ada di ERD v0.1, akan dimasukkan ke revisi ERD.

---

## 3. Model dan enum

### Category

```php
$category->products; // HasMany Product
```

### Product

```php
$product->category;      // BelongsTo Category
$product->variants;      // HasMany ProductVariant
$product->images;        // HasMany ProductImage, urut sort_order
$product->primaryImage;  // HasOne ProductImage pertama
$product->status;        // ProductStatus enum
$product->price_from;    // harga varian termurah, butuh variants
$product->total_stock;   // total stok tersedia, butuh variants
```

Scope:

```php
Product::active()->get();
Product::inCategory('mules')->get();
Product::search('mules')->get();
```

### ProductVariant

```php
$variant->product;         // BelongsTo Product
$variant->stock_available; // stock_on_hand - stock_reserved
$variant->is_in_stock;     // stock_available > 0
ProductVariant::available()->get();
```

### ProductImage

```php
$image->product; // BelongsTo Product
$image->url;     // URL public disk
```

### ProductStatus

```php
ProductStatus::Active;   // 'active'
ProductStatus::Inactive; // 'inactive'
```

Hanya produk `active` yang boleh tampil di katalog publik.

---

## 4. Data demo

Seeder: `Database\Seeders\DemoCatalogSeeder`

Sumber:

- `docs/data/products.csv`
- `docs/data/product_variants.csv`

Jumlah:

- 7 kategori: Mules, Heels, Boots, Flatshoes, Sneakers, Docmart, Flatform
- 10 produk
- 30 varian

Seeder idempotent. Boleh dijalankan ulang tanpa menggandakan data.

Perintah:

```powershell
php artisan db:seed --class=DemoCatalogSeeder
```

Di Neon, hanya migration owner yang menjalankan seeder.

---

## 5. Route contract

### Publik, milik Anggota A

| Method | URL | Name | Handler |
|---|---|---|---|
| GET | `/katalog` | `catalog.index` | `Storefront\CatalogController@index` |
| GET | `/katalog/{product:slug}` | `catalog.show` | `Storefront\CatalogController@show` |

Filter query yang disarankan:

| Query | Fungsi |
|---|---|
| `q` | pencarian nama/deskripsi |
| `category` | slug kategori |
| `sort` | `newest`, `price_asc`, `price_desc` |
| `page` | pagination |

### Admin, milik Anggota B

| Method | URL | Name |
|---|---|---|
| GET | `/admin/products` | `admin.products.index` |
| GET | `/admin/products/create` | `admin.products.create` |
| POST | `/admin/products` | `admin.products.store` |
| GET | `/admin/products/{product}/edit` | `admin.products.edit` |
| PATCH | `/admin/products/{product}` | `admin.products.update` |
| PATCH | `/admin/products/{product}/status` | `admin.products.status` |
| POST | `/admin/products/{product}/images` | `admin.products.images.store` |
| DELETE | `/admin/products/{product}/images/{image}` | `admin.products.images.destroy` |

Semua route admin memakai middleware:

```php
['auth', 'role:admin,admin_master']
```

Varian boleh dikelola sebagai array di form produk:

```text
variants[0][id]
variants[0][sku]
variants[0][size]
variants[0][color]
variants[0][price]
variants[0][stock_on_hand]
```

`stock_reserved` tidak boleh diedit dari form admin. Nilai itu hanya berubah lewat checkout dan reservasi.

---

## 6. Data untuk Blade

### Katalog index

Controller mengirim:

```php
[
    'products' => $products,   // paginator, eager load category, variants, primaryImage
    'categories' => $categories, // semua kategori
    'filters' => $filters,     // q, category, sort
]
```

Setiap produk minimal menyediakan:

- name
- slug
- category.name
- price_from
- total_stock
- primaryImage?->url
- status

### Detail produk

Controller mengirim:

```php
[
    'product' => $product, // eager load category, variants, images
]
```

Varian minimal menampilkan:

- size
- color
- price
- stock_available
- is_in_stock

Stok habis:

- tampilkan label "Stok habis";
- tombol tambah ke cart nanti dinonaktifkan;
- jangan menyembunyikan varian yang habis, supaya pembeli tahu ukuran mana yang kosong.

---

## 7. Aturan harga dan stok

- Harga selalu berasal dari `product_variants.price`, bukan dari request.
- Harga produk di kartu katalog memakai `price_from`.
- Stok tersedia = `stock_on_hand - stock_reserved`.
- Katalog publik hanya menampilkan produk `active`.
- Varian dengan stok tersedia 0 tetap tampil di detail, tetapi tidak bisa dipilih.
- Jangan mengubah `stock_reserved` dari form admin.
- Perubahan `stock_on_hand` nanti lewat `StockService` dan kartu stok. Untuk Slice 2, admin boleh mengisi stok awal.

---

## 8. Foto produk

- Disimpan di disk `public`.
- Contoh path: `products/mules-ayu/01.webp`.
- Akses URL lewat accessor `$image->url`.
- Validasi upload: `image`, `mimes:jpg,jpeg,png,webp`, maksimal 2 MB.
- Optimasi: resize maksimal 1200px, konversi WebP, quality 80.
- Urutan foto memakai `sort_order`.
- Foto pertama adalah foto utama.

Penting untuk database Neon bersama:

- Database Neon dipakai bersama, tetapi file upload tersimpan di laptop masing-masing.
- Foto yang diupload B tidak otomatis terlihat oleh A.
- Untuk demo Slice 2, UI wajib punya fallback bila foto tidak ada.
- Jangan mengandalkan file upload lokal sebagai data demo bersama.
- Setelah Slice 2, kita putuskan apakah perlu shared storage.

---

## 9. Authorization

- Pembeli boleh melihat katalog publik.
- Admin Biasa dan Admin Master boleh mengelola produk.
- Admin Master dan Admin Biasa tidak boleh menghapus produk yang sudah dipakai transaksi. Gunakan status `inactive`.
- Validasi dan otorisasi tetap dicek di server.

---

## 10. Definition of done Slice 2

- [ ] 10 produk demo tampil di katalog publik.
- [ ] Pencarian nama/deskripsi bekerja.
- [ ] Filter kategori bekerja.
- [ ] Pagination bekerja.
- [ ] Detail produk menampilkan varian ukuran/warna.
- [ ] Stok habis terlihat dan tidak bisa dipilih.
- [ ] Admin bisa membuat dan mengedit produk.
- [ ] Admin bisa mengelola varian.
- [ ] Admin bisa upload foto dan foto tampil.
- [ ] Authorization diuji.
- [ ] Feature test katalog dan admin lulus.
- [ ] UI responsif di mobile dan desktop.
- [ ] Demo singkat bersama tim.

---

## 11. Batas pekerjaan

Firdaus:

- migration, model, relasi, seeder, enum;
- review schema dan query;
- migration ke Neon.

Anggota A:

- route publik;
- controller katalog publik;
- Blade katalog dan detail;
- filter, search, pagination.

Anggota B:

- route admin;
- controller dan FormRequest admin;
- Blade admin produk dan varian;
- upload foto dan optimasi.

Jangan membuat migration baru untuk tabel yang sama. Ajukan perubahan schema ke Firdaus.
