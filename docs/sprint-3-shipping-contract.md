# Sprint 3 - Shipping Contract

**Versi:** 1.0  
**Tanggal:** 16 November 2026  
**Untuk:** Firdaus, Anand, Hafiez  
**Status:** Schema, enum, model, fake provider, dan `ShippingService` selesai; checkout dan halaman tracking menyusul  
**Keputusan terkait:** D-35

Dokumen ini adalah kontrak Slice 6: tarif ongkir, pembuatan pengiriman, resi, dan tracking.

---

## 1. Prinsip

- Sprint 3 memakai `FakeShippingProvider` dan tarif flat sebagai fallback.
- Biteship baru dipasang setelah aplikasi selesai dan akun sandbox siap.
- Ongkir dihitung di server dari berat pesanan, alamat tujuan, dan layanan kurir.
- Nominal dari browser tidak pernah dipercaya. Server menghitung ulang sebelum order dibuat.
- Jika provider gagal, checkout tetap bisa memakai tarif flat.
- Pembuatan pengiriman dan input resi manual harus tetap bisa dilakukan admin tanpa provider.
- Pengiriman tidak mengubah stok. Stok hanya berubah lewat pembayaran dan pembatalan.

## 2. Schema `shipments`

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| order_id | bigint | FK orders, restrict delete |
| provider | string(30) | `fake`, `manual`, `biteship` |
| courier | string(30) | contoh `JNE`, `SiCepat`, `Flat` |
| service | string(50) | contoh `REG`, `YES` |
| provider_reference | string(100) | nullable, ID order di provider |
| tracking_number | string(100) | nullable, unik jika terisi |
| cost | unsigned int | ongkir yang ditagihkan |
| weight_grams | unsigned int | berat yang dipakai saat tarif dihitung |
| status | string(20) | enum ShipmentStatus |
| shipped_at | timestamp | nullable, diisi saat kurir menjemput |
| delivered_at | timestamp | nullable, diisi saat diterima |
| notes | text | nullable, catatan admin |
| created_at, updated_at | timestamp | |

Index: `(order_id, created_at)`, `(status, created_at)`, `provider_reference`, `tracking_number` unik.

Satu order boleh punya lebih dari satu shipment untuk kasus kirim ulang, tetapi hanya satu shipment aktif yang dipakai untuk tracking utama.

## 3. Enum `ShipmentStatus`

```text
PENDING
PICKUP
IN_TRANSIT
DELIVERED
RETURNED
```

Arti:

- `PENDING`: shipment dibuat, belum dijemput kurir.
- `PICKUP`: kurir sudah menjemput paket.
- `IN_TRANSIT`: paket dalam perjalanan.
- `DELIVERED`: paket diterima pembeli.
- `RETURNED`: paket dikembalikan ke toko.

Transisi:

```text
PENDING    -> PICKUP, IN_TRANSIT, RETURNED
PICKUP     -> IN_TRANSIT, RETURNED
IN_TRANSIT -> DELIVERED, RETURNED
DELIVERED  -> final
RETURNED   -> final
```

`PENDING -> IN_TRANSIT` dan `PICKUP -> IN_TRANSIT` diizinkan karena webhook provider kadang melewatkan satu tahap. `DELIVERED` dan `RETURNED` tidak bisa diubah lagi lewat alur normal.

## 4. DTO dan interface

```php
interface ShippingProvider
{
    public function name(): string;

    /** @return array<int, ShippingRate> */
    public function rates(ShippingRateRequest $request): array;

    public function createShipment(ShipmentRequest $request): ShipmentResult;

    public function parseTrackingCallback(array $payload): TrackingEvent;
}
```

DTO:

- `ShippingRateRequest`: kota tujuan, kode pos tujuan, berat gram, kurir opsional.
- `ShippingRate`: kurir, layanan, deskripsi, biaya, estimasi hari, flag fallback.
- `ShipmentRequest`: order, kurir, layanan, berat gram, biaya.
- `ShipmentResult`: provider, referensi provider, nomor resi, status awal, payload mentah.
- `TrackingEvent`: nomor resi, status, deskripsi, waktu kejadian, payload mentah.

Implementasi awal:

- `App\Services\Shipping\FakeShippingProvider`
- provider `fake`
- tarif deterministik dari config; kota tujuan diabaikan karena ini adapter lokal
- `provider_reference`: `FAKE-SHIP-{order_code}`
- `tracking_number`: `FAKE-{COURIER}-{order_code}`
- status awal `PENDING`

Implementasi yang ditunda:

- `BiteshipProvider`

## 5. Perhitungan tarif

Berat yang dipakai:

```text
billable_kg = max(1, ceil(total_weight_grams / 1000))
cost = base_cost + per_kg * (billable_kg - 1)
```

Aturan:

- Berat total dihitung dari `order_items.qty * product_variants.weight_grams`.
- Jika berat tidak tersedia, default 1000 gram per item dan dicatat di log.
- Tarif flat dipakai jika provider gagal, kosong, atau tidak mengembalikan layanan untuk kurir yang dipilih.
- Tarif flat berasal dari `config/shipping.php`, bukan Blade.
- Pilihan kurir disimpan di order saat checkout supaya total tidak berubah setelahnya.

## 6. Service yang akan dibuat

`App\Services\ShippingService`

Method minimal:

```php
rates(OrderDraft $draft): array
createForOrder(Order $order, string $courier, string $service, int $weightGrams, int $cost): Shipment
updateTracking(Shipment $shipment, TrackingEvent $event): void
```

Aturan:

- `createForOrder` idempotent per order dan layanan. Request ganda tidak membuat dua shipment aktif.
- `updateTracking` memakai `ShipmentStatus::canTransitionTo`.
- `DELIVERED` mengisi `delivered_at`; `PICKUP` dan `IN_TRANSIT` mengisi `shipped_at` bila masih kosong.
- Perubahan status order ke `DIKIRIM` tetap lewat `OrderStatusService`.
- Webhook tracking dicatat di `integration_logs`.
- Webhook duplikat tidak mengubah status dua kali.

## 7. Route yang akan dibuat

| Method | URL | Name | Akses |
|---|---|---|---|
| GET | `/checkout/ongkir` | `checkout.shipping-rates` | pembeli login |
| POST | `/admin/orders/{order}/shipments` | `admin.shipments.store` | admin |
| PATCH | `/admin/shipments/{shipment}` | `admin.shipments.update` | admin |
| POST | `/webhooks/shipping/{provider}` | `webhooks.shipping` | tanpa CSRF, signature |

Checkout menampilkan:

- daftar kurir dan layanan;
- biaya dan estimasi tiba;
- penanda tarif flat jika provider gagal;
- total order yang sudah termasuk ongkir.

Admin menampilkan:

- form buat shipment;
- input resi manual;
- status pengiriman;
- tombol pickup dan cetak label saat Biteship aktif.

Pembeli menampilkan:

- kurir, layanan, dan nomor resi;
- status pengiriman;
- riwayat tracking teks.

## 8. Fallback manual

- Admin boleh membuat shipment dengan provider `manual` tanpa memanggil provider.
- Admin mengisi kurir, layanan, biaya, dan nomor resi sendiri.
- Resi manual tetap memakai tabel dan status yang sama.
- Fallback flat tidak boleh menahan checkout. Order tetap dibuat dengan ongkir flat.

## 9. Test wajib

- Fake provider mengembalikan tarif deterministik.
- Berat 1 gram dan 1000 gram menghasilkan biaya yang sama; 1001 gram masuk 2 kg.
- Provider tanpa layanan untuk kurir yang diminta memicu fallback flat.
- Checkout tetap bisa saat provider melempar exception.
- Admin membuat shipment dan menyimpan resi.
- Webhook tracking valid mengubah status.
- Webhook tracking palsu atau status tidak dikenal ditolak.
- Webhook duplikat tidak mengubah status dua kali.
- Ongkir masuk ke `orders.shipping_cost` dan `orders.total`.
- Pengiriman tidak mengubah stok.

## 10. Di luar kontrak

- Integrasi Biteship asli.
- Perhitungan tarif berdasarkan volume.
- Asuransi pengiriman.
- Pickup terjadwal otomatis.
- Retur setelah `DELIVERED`.
