<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\ReorderService;

class CheckReorderPoints extends Command
{
    protected $signature = 'fitness:check-goals';
    protected $description = 'Checks goals and generates workout plans';

    public function handle(ReorderService $service)
    {
        $result = $service->checkGoals();
        $this->info($result);
    }
}