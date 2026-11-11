# Sprint 2 - Cart Contract

**Versi:** 1.0  
**Tanggal:** 11 November 2026  
**Untuk:** Firdaus, Anand, Hafiez  
**Status:** Backend dan UI cart selesai

Dokumen ini adalah kontrak Slice 3: keranjang persisten tanpa mengunci stok.

---

## 1. Schema

### cart_items

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| user_id | bigint | FK users, cascade delete |
| variant_id | bigint | FK product_variants, cascade delete |
| qty | integer | minimal 1 |
| created_at, updated_at | timestamp | |

Constraint:

- unik `(user_id, variant_id)`;
- `qty > 0`;
- index `(user_id, updated_at)`.

Cart tidak punya tabel header. Satu user punya banyak baris `cart_items`.

---

## 2. Model

`App\Models\CartItem`

```php
$item->user;
$item->variant;
$item->variant->color;
$item->variant->color->product;
$item->unit_price;  // harga efektif varian
$item->line_total;  // unit_price * qty
```

`App\Models\Product\ProductVariant`:

```php
$variant->stock_available;  // stock - stock_reserved
$variant->effective_price;  // price ?? product.base_price
```

---

## 3. CartService

`App\Services\CartService`

```php
$cart = app(CartService::class);

$cart->add($user, $variant, 2);
$cart->update($user, $item, 3);
$cart->remove($user, $item);
$cart->clear($user);

$cart->items($user);    // Collection<CartItem>
$cart->summary($user);  // items, subtotal, count
```

Aturan:

- `add` menambah qty jika item sudah ada.
- Qty tidak boleh melebihi `stock_available`.
- Produk nonaktif tidak bisa masuk cart.
- Cart tidak mengubah `stock` maupun `stock_reserved`.
- Harga selalu dihitung dari database, bukan dari request.
- Item milik user lain menghasilkan 404.
- `update` minimal qty 1. Untuk menghapus, pakai `remove`.

---

## 4. Route yang perlu dibuat Anand

Semua memakai middleware `auth`.

| Method | URL | Name | Handler |
|---|---|---|---|
| GET | `/keranjang` | `cart.index` | `CartController@index` |
| POST | `/keranjang` | `cart.store` | `CartController@store` |
| PATCH | `/keranjang/{cartItem}` | `cart.update` | `CartController@update` |
| DELETE | `/keranjang/{cartItem}` | `cart.destroy` | `CartController@destroy` |
| DELETE | `/keranjang` | `cart.clear` | `CartController@clear` |

Validasi `store`:

```php
'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
'qty'        => ['required', 'integer', 'min:1'],
```

Validasi `update`:

```php
'qty' => ['required', 'integer', 'min:1'],
```

Controller hanya memanggil `CartService`, bukan query manual.

---

## 5. Data untuk Blade

`CartController@index` mengirim:

```php
[
    'items' => $summary['items'],
    'subtotal' => $summary['subtotal'],
    'count' => $summary['count'],
]
```

Setiap item minimal menampilkan:

- nama produk;
- warna;
- ukuran;
- SKU;
- harga satuan;
- qty;
- line total;
- tombol ubah qty;
- tombol hapus.

Empty state:

- "Keranjang masih kosong";
- tombol ke katalog.

Catatan UI:

- tampilkan "Stok belum dikunci sampai checkout";
- jangan menampilkan tombol checkout jika cart kosong;
- tombol checkout diarahkan ke Slice 4.

---

## 6. Tombol tambah ke cart

Di halaman detail produk, tombol "Tambah ke keranjang" saat ini masih placeholder.

Anand perlu:

- form POST ke `cart.store`;
- kirim `variant_id` dari ukuran yang dipilih;
- kirim `qty` minimal 1;
- tampilkan error validasi;
- redirect kembali dengan status sukses;
- jika belum login, arahkan ke login.

---

## 7. Test yang sudah ada

`tests/Feature/CartServiceTest.php`

- tambah item tanpa mengubah stok;
- tambah varian yang sama menambah qty;
- qty melebihi stok ditolak;
- update qty melebihi stok ditolak;
- hapus item;
- produk nonaktif ditolak;
- summary memakai harga efektif;
- stok yang direservasi mengurangi stok tersedia;
- user tidak bisa mengubah cart user lain.

Status service: 9 test lulus, 16 assertions.  
Test UI cart/checkout: 5 test lulus, 22 assertions.

---

## 8. Yang belum

- checkout dan order sudah masuk Slice 4;
- merge cart setelah login tidak diperlukan karena cart hanya untuk user login.

