<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Product\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerOnlyPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);

        $this->variant = ProductVariant::query()->where('stock', '>', 2)->firstOrFail();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    public function test_admin_cannot_add_items_to_cart(): void
    {
        $this->expectException(AuthorizationException::class);

        app(CartService::class)->add($this->userWithRole(UserRole::Admin), $this->variant, 1);
    }

    public function test_admin_master_cannot_checkout_even_with_a_cart_item(): void
    {
        $admin = $this->userWithRole(UserRole::AdminMaster);

        CartItem::create([
            'user_id' => $admin->id,
            'variant_id' => $this->variant->id,
            'qty' => 1,
        ]);

        $address = Address::create([
            'user_id' => $admin->id,
            'label' => 'Kantor',
            'recipient' => 'Admin Master',
            'phone' => '081200000000',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Contoh No. 2',
            'is_default' => true,
        ]);

        $this->expectException(AuthorizationException::class);

        app(CheckoutService::class)->placeOrder($admin, $address, PaymentMethod::ManualTransfer);
    }

    public function test_buyer_can_still_add_items_to_cart(): void
    {
        $buyer = $this->userWithRole(UserRole::Buyer);

        $item = app(CartService::class)->add($buyer, $this->variant, 1);

        $this->assertSame($buyer->id, $item->user_id);
    }
}
