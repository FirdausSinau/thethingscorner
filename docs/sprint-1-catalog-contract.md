# Sprint 1 - Catalog Contract

**Versi:** 2.2  
**Tanggal:** 11 November 2026  
**Untuk:** Firdaus, Anand, Hafiez  
**Status:** Diimplementasikan dan direkonsiliasi

Dokumen ini menggantikan kontrak versi 1.0. Schema katalog sekarang mengikuti implementasi dari Hafiez, dengan penyesuaian storage lokal dan route publik oleh Firdaus.

---

## 1. Schema aktual

### categories

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| name | string | |
| slug | string | belum unik di database, validasi di aplikasi |
| created_at, updated_at | timestamp | |

### products

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| category_id | bigint | FK categories, restrict delete |
| name | string | |
| slug | string | unik |
| description | text | nullable |
| base_price | bigint | Rupiah |
| weight_grams | integer | default 0, untuk ongkir |
| is_active | boolean | default true |
| is_preorder | boolean | default false |
| preorder_close_date | date | nullable |
| estimated_ready_date | date | nullable |
| rating_avg | decimal(3,2) | default 0 |
| rating_count | integer | default 0 |
| created_at, updated_at | timestamp | |

### product_colors

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| product_id | bigint | FK products, cascade delete |
| name | string | unik per produk |
| hex_code | string(7) | nullable |
| created_at, updated_at | timestamp | |

### product_color_images

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| product_color_id | bigint | FK product_colors, cascade delete |
| path | string | path di disk `public` |
| sort_order | smallint | default 0 |
| created_at, updated_at | timestamp | |

### product_variants

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| product_color_id | bigint | FK product_colors, cascade delete |
| size | string(10) | unik per warna |
| sku | string | unik |
| stock | integer | default 0 |
| stock_reserved | integer | default 0, untuk reservasi checkout |
| price | bigint | nullable, null berarti pakai `base_price` |
| created_at, updated_at | timestamp | |

### product_images

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| product_id | bigint | FK products, cascade delete |
| path | string | path di disk `public` |
| sort_order | integer | default 0 |
| created_at, updated_at | timestamp | |

---

## 2. Model

Namespace: `App\Models\Product`

```php
Product::listed()->paginate(12);
$product->category;
$product->colors;
$product->colors->images;
$product->colors->variants;
$product->images;
$product->variants;      // hasManyThrough ProductColor
$product->thumbnail_url; // product image atau color image pertama
```

```php
$color->product;
$color->images;
$color->variants;
```

```php
$variant->color; // ProductColor
```

Hanya produk `is_active = true` yang tampil di katalog publik.

---

## 3. Route

### Publik

| Method | URL | Name | Handler |
|---|---|---|---|
| GET | `/produk` | `catalog.index` | `CatalogController@index` |
| GET | `/produk/{product:slug}` | `products.show` | `ProductController@show` |

Katalog publik mendukung:

- `q` untuk pencarian nama/deskripsi;
- `category` untuk filter slug kategori;
- pagination 12 produk.

Detail produk juga publik. Login baru diperlukan saat cart, checkout, PO, dan review.

### Admin

| Method | URL | Name |
|---|---|---|
| GET | `/admin/products` | `admin.products.index` |
| GET | `/admin/products/create` | `admin.products.create` |
| POST | `/admin/products` | `admin.products.store` |
| GET | `/admin/products/{product}/edit` | `admin.products.edit` |
| PUT/PATCH | `/admin/products/{product}` | `admin.products.update` |
| PATCH | `/admin/products/{product}/deactivate` | `admin.products.deactivate` |
| POST | `/admin/products/{product}/colors` | `admin.products.colors.store` |
| DELETE | `/admin/colors/{color}` | `admin.colors.destroy` |
| POST | `/admin/colors/{color}/images` | `admin.colors.images.store` |
| DELETE | `/admin/images/{image}` | `admin.images.destroy` |
| POST | `/admin/colors/{color}/variants` | `admin.colors.variants.store` |
| PUT | `/admin/colors/{color}/variants` | `admin.colors.variants.update` |
| DELETE | `/admin/variants/{variant}` | `admin.variants.destroy` |
| POST | `/admin/products/{product}/images` | `admin.products.images.store` |
| DELETE | `/admin/product-images/{productImage}` | `admin.product-images.destroy` |

Semua route admin memakai `auth` dan `role:admin,admin_master`.

---

## 4. Storage gambar

Kode memakai disk yang bisa diatur lewat `PRODUCT_IMAGES_DISK`.

```dotenv
# lokal
PRODUCT_IMAGES_DISK=public

# Neon Object Storage
PRODUCT_IMAGES_DISK=s3
```

Aturan:

- Path contoh: `products/6/gallery/xxxx.png`.
- URL diakses lewat accessor `$image->url`.
- `public`: file dicek di `storage/app/public`; kalau tidak ada, accessor mengembalikan `null` dan UI menampilkan "Foto belum ada".
- `s3`: accessor langsung memakai URL S3/Neon. File tidak dicek per request supaya katalog tidak lambat.
- Upload dan hapus mengikuti disk yang sedang aktif.
- Untuk S3/Neon, isi `AWS_ENDPOINT`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, dan `AWS_USE_PATH_STYLE_ENDPOINT=true` di `.env`.
- Jalankan `php artisan config:clear` setelah mengubah `.env`.
- Jangan menaruh credential di Git.

Data lama di Neon berisi path gambar yang sebelumnya disimpan di S3/Neon. Saat `PRODUCT_IMAGES_DISK=s3` dan credential benar, path lama bisa langsung dipakai. Jika memakai `public`, file harus disalin ke `storage/app/public`.

### Troubleshooting S3/Neon

- `InvalidAccessKeyId`: access key salah, tidak cocok, atau ada spasi/quote saat menyalin. Perbaiki `AWS_ACCESS_KEY_ID` dan `AWS_SECRET_ACCESS_KEY`.
- `NoSuchKey`: koneksi dan bucket benar, tetapi object tidak ada di path tersebut. Upload ulang lewat admin atau cek key di bucket.
- `HTTP 000`: endpoint tidak bisa dihubungi. Cek `AWS_ENDPOINT` dan koneksi internet.
- `url()` hanya membuat URL. Selalu cek `exists()` atau buka URL di browser untuk memastikan file benar-benar ada.
- Setelah mengubah `.env`, jalankan `php artisan config:clear`.

---

## 5. Seeder

`Database\Seeders\CatalogSeeder`

- Membuat kategori Boots.
- Membuat produk `Theon Docmart`.
- Membuat warna Hitam dan Putih.
- Membuat 7 varian ukuran.
- Mencoba menyalin foto dari `storage/app/private/Theon Dockmart.png` ke disk gambar yang dikonfigurasi. Jika file tidak ada, dilewati.

Keputusan tim:

- `Theon Docmart` dibuat lewat seeder sebagai data demo.
- `Cassie Docmart` dibuat manual lewat admin.
- Produk berikutnya dibuat lewat admin, bukan seeder.

Seeder tidak perlu diperluas untuk seluruh katalog. Seeder hanya untuk data demo awal.

---

## 6. Aturan stok dan harga

- Harga tampil memakai `base_price`; harga varian dipakai jika tidak null.
- Stok fisik per varian ada di `product_variants.stock`.
- Stok tersedia = `stock - stock_reserved`.
- Total stok produk = jumlah stok tersedia semua varian.
- Varian stok 0 tetap tampil di detail, tetapi ditandai habis.
- Berat produk untuk ongkir memakai `products.weight_grams`.

---

## 7. Test

Sudah ada:

- katalog publik tampil;
- pencarian produk;
- detail produk publik;
- buyer tidak bisa membuka admin produk;
- admin bisa membuka admin produk;
- admin bisa membuat produk.

Perintah:

```powershell
php artisan test
npm run build
```

Status terakhir: total suite 63 test lulus, 179 assertions, termasuk test upload gambar ke disk `public` dan `s3`.

---

## 8. Yang belum

- Sinkronisasi file gambar lama dari S3 ke storage lokal.
- Form preorder di admin (kolom sudah ada, UI belum).
- Halaman cart, checkout, dan reservasi.
- Integrasi ongkir memakai `weight_grams`.

Sudah diputuskan:

- Brand resmi: `thethingscorner` (huruf kecil, satu kata).
- Field `weight_grams`, `rating_avg`, `rating_count`, dan `stock_reserved` sudah ditambahkan.
- Produk baru ke depan dibuat lewat admin, bukan seeder.

---

## 9. Pembagian kerja setelah rekonsiliasi

- Firdaus: review schema, storage, test, dan integrasi.
- Anand: review UI katalog dan detail, tambah filter/sort jika perlu.
- Hafiez: review admin produk, varian, warna, dan upload foto.
- Perubahan schema produk harus dikoordinasikan dengan Hafiez sebagai pemilik domain produk.

