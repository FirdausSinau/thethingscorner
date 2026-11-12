<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('manage-orders', fn (User $user): bool => in_array($user->role, [
            UserRole::Admin,
            UserRole::AdminMaster,
        ], true));
    }
}