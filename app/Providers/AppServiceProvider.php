<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\InventoryTransactionObserver;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\InventoryTransactionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Auto-create the MySQL database if it does not exist
        $this->ensureDatabaseExists();

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(InventoryTransaction::class, InventoryTransactionPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    protected function ensureDatabaseExists(): void
    {
        // Only run for MySQL connections, not SQLite (used in testing)
        if (config('database.default') !== 'mysql') {
            return;
        }

        try {
            $database = config('database.connections.mysql.database');
            $host     = config('database.connections.mysql.host');
            $port     = config('database.connections.mysql.port');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Connect without specifying a database to create it if missing
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$database}`
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
        } catch (\Throwable) {
            // Silently fail — MySQL may not be running in some environments
        }
    }
}
