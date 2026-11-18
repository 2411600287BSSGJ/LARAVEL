<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() && $purchaseOrder->status === 'pending';
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() && $purchaseOrder->status === 'pending';
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin();
    }
}
