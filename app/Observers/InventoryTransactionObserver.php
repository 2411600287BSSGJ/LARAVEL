<?php

namespace App\Observers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\ReorderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class InventoryTransactionObserver
{
    /**
     * Handle the InventoryTransaction "created" event.
     */
    public function created(InventoryTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $product = Product::lockForUpdate()->find($transaction->product_id);
            if (!$product) {
                return;
            }

            $type = strtolower($transaction->type);
            $qty = (int) $transaction->quantity;

            if (in_array($type, ['stock_in', 'in', 'inbound', 'purchase'])) {
                $product->quantity += abs($qty);
            } elseif (in_array($type, ['stock_out', 'out', 'outbound', 'sale'])) {
                $requested = abs($qty);
                if ($product->quantity < $requested) {
                    throw new \InvalidArgumentException(
                        "Insufficient stock for product [{$product->name}]. Current stock: {$product->quantity}, requested deduction: {$requested}."
                    );
                }
                $product->quantity -= $requested;
            } elseif ($type === 'adjustment') {
                $newQty = $product->quantity + $qty;
                if ($newQty < 0) {
                    throw new \InvalidArgumentException(
                        "Adjustment would result in negative stock for product [{$product->name}]. Current stock: {$product->quantity}, adjustment: {$qty}."
                    );
                }
                $product->quantity = $newQty;
            }

            $product->save();

            // Trigger low stock notification and automated reorder checking
            if ($product->quantity <= $product->reorder_level) {
                $this->triggerLowStockNotification($product);

                try {
                    app(ReorderService::class)->checkReorderPoint($product);
                } catch (\Throwable $e) {
                    Log::warning("Reorder service check failed: {$e->getMessage()}");
                }
            }

            // Sync to inventory_transactions table if present
            try {
                if (Schema::hasTable('inventory_transactions')) {
                    DB::table('inventory_transactions')->updateOrInsert(
                        ['id' => $transaction->id],
                        [
                            'product_id' => $transaction->product_id,
                            'user_id' => $transaction->user_id,
                            'purchase_order_id' => $transaction->purchase_order_id,
                            'type' => $transaction->type,
                            'quantity' => $transaction->quantity,
                            'unit_price' => $transaction->unit_price,
                            'reference_number' => $transaction->reference_number ?? $transaction->reference,
                            'reference' => $transaction->reference ?? $transaction->reference_number,
                            'notes' => $transaction->notes,
                            'created_at' => $transaction->created_at ?? now(),
                            'updated_at' => $transaction->updated_at ?? now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Ignore sync errors in test environments
            }
        });

        // Trigger email notification when staff encodes a stock in or stock out
        $this->triggerStaffMovementNotification($transaction);
    }

    /**
     * Handle the InventoryTransaction "deleted" event.
     */
    public function deleted(InventoryTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $product = Product::lockForUpdate()->find($transaction->product_id);
            if (!$product) {
                return;
            }

            $type = strtolower($transaction->type);
            $qty = (int) $transaction->quantity;

            if (in_array($type, ['stock_in', 'in', 'inbound', 'purchase'])) {
                $product->quantity = max(0, $product->quantity - abs($qty));
            } elseif (in_array($type, ['stock_out', 'out', 'outbound', 'sale'])) {
                $product->quantity += abs($qty);
            } elseif ($type === 'adjustment') {
                $product->quantity = max(0, $product->quantity - $qty);
            }

            $product->save();

            try {
                if (Schema::hasTable('inventory_transactions')) {
                    DB::table('inventory_transactions')->where('id', $transaction->id)->delete();
                }
            } catch (\Throwable $e) {
                // Ignore sync deletion errors
            }
        });
    }

    /**
     * Send low stock notification avoiding duplicate alerts.
     */
    protected function triggerLowStockNotification(Product $product): void
    {
        try {
            $recipients = User::all();
            if ($recipients->isEmpty()) {
                return;
            }

            foreach ($recipients as $recipient) {
                $recentNotification = $recipient->unreadNotifications()
                    ->where('data->product_id', $product->id)
                    ->where('created_at', '>=', now()->subHours(2))
                    ->exists();

                if (!$recentNotification) {
                    $recipient->notify(new LowStockNotification($product));
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to dispatch low stock notification: {$e->getMessage()}");
        }
    }

    /**
     * Send email notification to all staff and admin users when staff encodes a stock in or stock out.
     */
    protected function triggerStaffMovementNotification(InventoryTransaction $transaction): void
    {
        try {
            $type = strtolower($transaction->type ?? '');
            $isStockMovement = in_array($type, ['stock_in', 'in', 'inbound', 'purchase', 'stock_out', 'out', 'outbound', 'sale']);
            if (!$isStockMovement) {
                return;
            }

            // Find the encoding user
            $staffUser = $transaction->user ?? User::find($transaction->user_id) ?? auth()->user();
            if (!$staffUser || $staffUser->role !== 'staff') {
                return;
            }

            $product = $transaction->product ?? Product::find($transaction->product_id);
            if (!$product) {
                return;
            }

            // Get all staff and admin recipients with valid emails
            $recipients = User::whereIn('role', ['admin', 'staff'])
                ->whereNotNull('email')
                ->get();

            // Ensure the encoding staff member is included
            if (!$recipients->contains('id', $staffUser->id)) {
                $recipients->push($staffUser);
            }

            foreach ($recipients as $recipient) {
                if (empty($recipient->email)) {
                    continue;
                }
                try {
                    \Illuminate\Support\Facades\Mail::to($recipient->email, $recipient->name)
                        ->send(new \App\Mail\StaffStockMovementMail($transaction, $product, $staffUser, $recipient));
                } catch (\Throwable $mailEx) {
                    Log::warning("Could not send stock movement email to {$recipient->email}: {$mailEx->getMessage()}");
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to trigger staff stock movement notification: {$e->getMessage()}");
        }
    }
}
