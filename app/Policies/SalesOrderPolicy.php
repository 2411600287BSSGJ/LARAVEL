<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, SalesOrder $salesOrder): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, SalesOrder $salesOrder): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, SalesOrder $salesOrder): bool
    {
        return $user->isAdmin() || ($salesOrder->created_by === $user->id && $salesOrder->status === 'pending');
    }

    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $user->isAdmin();
    }
}
