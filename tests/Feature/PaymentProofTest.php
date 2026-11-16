<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private ProductVariant $variant;

    private Address $address;

    private PaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->buyer = User::factory()->create();
        $this->variant = ProductVariant::query()->where('stock', '>', 3)->firstOrFail();
        $this->payments = app(PaymentService::class);

        $this->address = Address::create([
            'user_id' => $this->buyer->id,
            'label' => 'Rumah',
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Contoh No. 1',
            'is_default' => true,
        ]);
    }

    private function placeOrder(PaymentMethod $method = PaymentMethod::ManualTransfer): Order
    {
        app(CartService::class)->add($this->buyer, $this->variant, 1);

        return app(CheckoutService::class)->placeOrder($this->buyer, $this->address, $method);
    }

    public function test_manual_proof_is_stored_on_private_disk(): void
    {
        Storage::fake('local');

        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $path = $this->payments->attachProof(
            $payment,
            UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        );

        $this->assertSame($path, $payment->fresh()->proof_path);
        Storage::disk('local')->assertExists($path);
        $this->assertDatabaseHas('integration_logs', [
            'event' => 'payment.proof_uploaded',
            'status' => 'processed',
        ]);
    }

    public function test_replacing_proof_deletes_old_file(): void
    {
        Storage::fake('local');

        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $oldPath = $this->payments->attachProof(
            $payment,
            UploadedFile::fake()->create('bukti-lama.pdf', 100, 'application/pdf'),
        );

        $newPath = $this->payments->attachProof(
            $payment->fresh(),
            UploadedFile::fake()->create('bukti-baru.pdf', 120, 'application/pdf'),
        );

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
    }

    public function test_proof_is_rejected_for_fake_payment(): void
    {
        Storage::fake('local');

        $order = $this->placeOrder(PaymentMethod::Qris);
        $payment = $this->payments->createForOrder($order, PaymentMethod::Qris);

        try {
            $this->payments->attachProof(
                $payment,
                UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            );

            $this->fail('Bukti untuk pembayaran fake seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('proof', $e->errors());
        }

        $this->assertNull($payment->fresh()->proof_path);
    }

    public function test_proof_with_invalid_mime_is_rejected(): void
    {
        Storage::fake('local');

        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $this->expectException(ValidationException::class);

        $this->payments->attachProof(
            $payment,
            UploadedFile::fake()->create('bukti.txt', 10, 'text/plain'),
        );
    }

    public function test_proof_larger_than_limit_is_rejected(): void
    {
        Storage::fake('local');
        config(['payment.proof.max_kb' => 2048]);

        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);

        $this->expectException(ValidationException::class);

        $this->payments->attachProof(
            $payment,
            UploadedFile::fake()->create('bukti-besar.pdf', 3000, 'application/pdf'),
        );
    }

    public function test_paid_payment_rejects_new_proof(): void
    {
        Storage::fake('local');

        $order = $this->placeOrder();
        $payment = $this->payments->createForOrder($order, PaymentMethod::ManualTransfer);
        $this->payments->markPaid($payment);

        $this->expectException(ValidationException::class);

        $this->payments->attachProof(
            $payment->fresh(),
            UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        );
    }
}
