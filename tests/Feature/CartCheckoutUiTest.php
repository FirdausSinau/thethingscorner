<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutUiTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->buyer = User::factory()->create();
        $this->variant = ProductVariant::query()->where('stock', '>', 3)->firstOrFail();
    }

    public function test_cart_page_lists_items(): void
    {
        app(CartService::class)->add($this->buyer, $this->variant, 2);

        $this->actingAs($this->buyer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($this->variant->color->product->name)
            ->assertSee('Stok belum dikunci sampai checkout.');
    }

    public function test_cart_item_can_be_added_updated_and_removed_via_routes(): void
    {
        $this->actingAs($this->buyer)
            ->post(route('cart.store'), [
                'variant_id' => $this->variant->id,
                'qty' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->buyer->id,
            'variant_id' => $this->variant->id,
            'qty' => 1,
        ]);

        $item = $this->buyer->cartItems()->firstOrFail();

        $this->actingAs($this->buyer)
            ->patch(route('cart.update', $item), ['qty' => 2])
            ->assertRedirect();

        $this->assertSame(2, $item->fresh()->qty);

        $this->actingAs($this->buyer)
            ->delete(route('cart.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_checkout_page_renders_default_address_and_payment_methods(): void
    {
        app(CartService::class)->add($this->buyer, $this->variant, 1);

        Address::create([
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

        $this->actingAs($this->buyer)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Checkout')
            ->assertSee('Transfer manual')
            ->assertSee('Budi Santoso');
    }

    public function test_checkout_store_creates_order_and_shows_order_page(): void
    {
        app(CartService::class)->add($this->buyer, $this->variant, 1);

        $response = $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'recipient' => 'Budi Santoso',
            'phone' => '081234567890',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Contoh No. 1',
            'payment_method' => 'manual_transfer',
        ]);

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('orders.show', ['order' => $order->order_code]));

        $this->actingAs($this->buyer)
            ->get(route('orders.show', ['order' => $order->order_code]))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Menunggu pembayaran');
    }

    public function test_guest_cannot_open_cart_or_checkout(): void
    {
        $this->get(route('cart.index'))->assertRedirect(route('login'));
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }
}
