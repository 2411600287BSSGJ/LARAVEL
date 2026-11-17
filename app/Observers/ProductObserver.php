<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;

class ProductObserver
{
    public function created(Product $product): void
    {
        if ($this->isLowStock($product)) {
            $this->notifyUsers($product);
        }
    }

    public function updated(Product $product): void
    {
        if (! $product->wasChanged(['quantity', 'reorder_level'])) {
            return;
        }

        $wasLowStock = (int) $product->getOriginal('quantity') <= (int) $product->getOriginal('reorder_level');

        if ($this->isLowStock($product) && ! $wasLowStock) {
            $this->notifyUsers($product);
        }
    }

    private function isLowStock(Product $product): bool
    {
        return $product->quantity <= $product->reorder_level;
    }

    private function notifyUsers(Product $product): void
    {
        User::query()->each(
            fn (User $user) => $user->notify(new LowStockNotification($product))
        );
    }
}
