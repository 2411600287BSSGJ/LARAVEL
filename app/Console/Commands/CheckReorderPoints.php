<?php

namespace App\Console\Commands;

use App\Services\ReorderService;
use Illuminate\Console\Command;

class CheckReorderPoints extends Command
{
    protected $signature = 'inventory:check-reorder {--all : Check all products for replenishment}';

    protected $aliases = ['inventory:check-reorder-points'];

    protected $description = 'Automatically detect low stock and generate purchase orders for products below reorder levels';

    public function handle(ReorderService $service): int
    {
        $this->info("Scanning inventory against dynamic reorder thresholds...");
        $orders = $service->generatePurchaseOrders();
        $created = count($orders);

        if ($created > 0) {
            $this->info("Generated {$created} purchase order(s):");
            foreach ($orders as $po) {
                $this->line(" - Reference: {$po->reference} | Product: {$po->product?->name} | Qty: {$po->quantity}");
            }
        } else {
            $this->comment("All inventory levels are optimal. Zero purchase orders generated.");
        }

        return self::SUCCESS;
    }
}
