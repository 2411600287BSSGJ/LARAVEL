<?php

namespace App\Policies;

use App\Models\InventoryTransaction;
use App\Models\User;

class InventoryTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, InventoryTransaction $transaction): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, InventoryTransaction $transaction): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, InventoryTransaction $transaction): bool
    {
        return $user->isAdmin();
    }
}
