<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeInventoryAdmin extends Command
{
    protected $signature = 'inventory:make-admin {email : Email address of an existing user}';

    protected $description = 'Grant the administrator role to an existing inventory user';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No active user has that email address.');

            return self::FAILURE;
        }

        $user->update(['role' => 'admin']);
        $this->info("Administrator role granted to {$user->email}.");

        return self::SUCCESS;
    }
}
