# Sprint 3 - Payment Contract

**Versi:** 1.0  
**Tanggal:** 16 November 2026  
**Untuk:** Firdaus, Anand, Hafiez  
**Status:** Schema, enum, model, dan fake gateway selesai; service, halaman, dan webhook menyusul  
**Keputusan terkait:** D-05, D-06, D-17, D-32, D-35

Dokumen ini adalah kontrak Slice 5: pembayaran gateway dan transfer manual.

---

## 1. Prinsip

- Sprint 3 memakai `FakePaymentGateway` dan transfer manual.
- Midtrans atau Xendit baru dipasang setelah aplikasi selesai dan akun sandbox siap.
- `PENDING` adalah status pembayaran, bukan status pesanan.
- Status pesanan tetap memakai `OrderStatus`. Label `MENUNGGU_KONFIRMASI` adalah **Dipending**.
- Pembayaran sukses mengonsumsi reservasi dan mengurangi stok fisik tepat satu kali.
- Pembayaran gagal atau kedaluwarsa tidak boleh mengurangi stok fisik.
- Bukti transfer disimpan di disk privat, bukan `public`.

## 2. Schema `payments`

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| order_id | bigint | FK orders, restrict delete |
| provider | string(30) | `fake`, `manual`, `midtrans`, `xendit` |
| method | string(30) | enum PaymentMethod |
| external_id | string(100) | unik, idempotency webhook |
| amount | bigint | nominal yang harus dibayar |
| status | string(20) | enum PaymentStatus |
| proof_path | string | nullable, khusus transfer manual |
| expires_at | timestamp | nullable, batas pembayaran |
| paid_at | timestamp | nullable |
| verified_by | bigint | nullable, FK users, null on delete |
| created_at, updated_at | timestamp | |

Index: `(order_id, created_at)`, `(status, expires_at)`, `external_id` unik.

## 3. Enum `PaymentStatus`

```text
PENDING
PAID
EXPIRED
FAILED
REFUNDED
```

Arti:

- `PENDING`: pembayaran dibuat, belum lunas.
- `PAID`: pembayaran diterima.
- `EXPIRED`: batas waktu habis.
- `FAILED`: provider menolak atau pembayaran gagal.
- `REFUNDED`: dana dikembalikan manual oleh Admin Master.

## 4. Mapping ke order

| PaymentStatus | OrderStatus | Reservasi | Stok fisik |
|---|---|---|---|
| PENDING | MENUNGGU_PEMBAYARAN | active | tetap |
| PAID | DIBAYAR | consumed | berkurang |
| EXPIRED | KEDALUWARSA | expired | tetap |
| FAILED | tidak berubah | tetap active sampai expiry | tetap |
| REFUNDED | DIBATALKAN bila belum dikirim | sesuai aturan | sesuai aturan |

Perubahan order dan stok tetap lewat `OrderStatusService` agar lock dan audit log konsisten.

## 5. Model

`App\Models\Payment`

- relasi `order()`;
- relasi `verifier()` ke `User` lewat `verified_by`;
- cast `method` ke `PaymentMethod`;
- cast `status` ke `PaymentStatus`;
- cast `amount` ke integer, `expires_at` dan `paid_at` ke datetime.

`Order` mendapat relasi `payments()`, `latestPayment()`, `shipments()`, dan `latestShipment()`.

## 6. Gateway contract

```php
interface PaymentGateway
{
    public function create(Order $order, PaymentMethod $method): PaymentInstruction;

    public function parseCallback(array $payload): PaymentEvent;
}
```

Implementasi awal:

- `App\Services\Payment\FakePaymentGateway`
- provider `fake`
- `external_id` deterministik: `FAKE-{order_code}`
- instruksi berisi nominal, metode, batas waktu, dan teks simulasi
- callback fake membaca `external_id`, `status`, dan `amount`

Implementasi yang ditunda:

- `MidtransGateway`
- `XenditGateway`

## 7. Service yang akan dibuat

`App\Services\PaymentService`

Method minimal:

```php
createForOrder(Order $order, PaymentMethod $method): Payment
markPaid(Payment $payment, ?User $actor = null): void
verifyManual(Payment $payment, User $admin): void
rejectManual(Payment $payment, User $admin, string $note): void
expire(Payment $payment): void
```

Aturan:

- `markPaid` idempotent. Pembayaran yang sudah `PAID` tidak memproses stok lagi.
- `external_id` unik.
- Nominal callback harus sama dengan `payments.amount`.
- Perubahan status order memakai `OrderStatusService`.
- Semua aksi admin dicatat di `order_status_logs` dan `integration_logs`.

## 8. Route yang akan dibuat

| Method | URL | Name | Akses |
|---|---|---|---|
| GET | `/pembayaran/{order:order_code}` | `payments.show` | pemilik order |
| POST | `/pembayaran/{order:order_code}/bukti` | `payments.proof` | pemilik order |
| POST | `/pembayaran/{order:order_code}/fake` | `payments.fake.pay` | pemilik order, lokal |
| POST | `/admin/orders/{order}/verifikasi-pembayaran` | `admin.orders.verify-payment` | admin |
| POST | `/webhooks/payment/{provider}` | `webhooks.payment` | tanpa CSRF, signature |

Halaman pembayaran menampilkan:

- nominal dan batas waktu;
- QRIS, VA, e-wallet, atau rekening manual;
- status pembayaran;
- tombol simulasi fake untuk lokal;
- form upload bukti transfer manual.

## 9. Transfer manual

- Provider `manual`, status awal `PENDING`.
- Rekening berasal dari `config/payment.php` atau `.env`, bukan Blade.
- Bukti transfer divalidasi MIME dan ukuran, disimpan di disk privat.
- Admin membuka detail, memeriksa nominal dan bukti, lalu menyetujui atau menolak.
- Approve memanggil `markPaid`, mengisi `verified_by`, dan mencatat log.
- Reject menyimpan alasan, status tetap `PENDING` sampai batas waktu atau diubah admin.

## 10. Test wajib

- Fake gateway membuat instruksi.
- Fake callback `PAID` memproses order dan stok satu kali.
- Callback duplikat tidak memproses stok lagi.
- Callback dengan nominal salah ditolak.
- Callback dengan signature palsu ditolak saat provider asli dipasang.
- Transfer manual: upload, approve, reject.
- Expiry melepas reservasi dan order `KEDALUWARSA`.
- Pembeli tidak bisa membayar order orang lain.
- Admin Biasa tidak bisa refund; refund hanya Admin Master.

## 11. Di luar kontrak

- Integrasi Midtrans/Xendit.
- Refund otomatis.
- Cicilan atau paylater.
- Virtual account bank tertentu.
