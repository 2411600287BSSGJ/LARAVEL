<?php

namespace App\Console\Commands;

use App\Services\ReorderService;
use Illuminate\Console\Command;

class CheckReorderPoints extends Command
{
    protected $signature = 'inventory:check-reorder-points';

    protected $description = 'Create purchase orders for products below their reorder levels';

    public function handle(ReorderService $service): int
    {
        $created = $service->checkAllReorderPoints();
        $this->info("Created {$created} purchase order(s).");

        return self::SUCCESS;
    }
}
