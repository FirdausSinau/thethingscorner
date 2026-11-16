<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\PaymentGateway;
use App\Services\Shipping\FakeShippingProvider;
use App\Services\Shipping\ShippingProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function (): PaymentGateway {
            $driver = (string) config('services.payment.driver', 'fake');

            return match ($driver) {
                'fake' => new FakePaymentGateway,
                default => throw new InvalidArgumentException("Payment driver [{$driver}] belum didukung. Sprint 3 memakai fake."),
            };
        });

        $this->app->bind(ShippingProvider::class, function (): ShippingProvider {
            $driver = (string) config('services.shipping.driver', 'fake');

            return match ($driver) {
                'fake' => new FakeShippingProvider,
                default => throw new InvalidArgumentException("Shipping driver [{$driver}] belum didukung. Sprint 3 memakai fake."),
            };
        });
    }

    public function boot(): void
    {
        Gate::define('manage-orders', fn (User $user): bool => in_array($user->role, [
            UserRole::Admin,
            UserRole::AdminMaster,
        ], true));
    }
}
