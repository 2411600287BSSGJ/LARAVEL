<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <-- ADDED
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // FIX PAGINATION ICONS
        Paginator::useBootstrapFive();

        // REGISTER POLICIES
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}