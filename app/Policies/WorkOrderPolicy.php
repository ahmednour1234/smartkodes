<?php

namespace App\Policies;

use App\Models\WorkOrder;
use App\Models\User;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->tenant_id === $workOrder->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->isManagerOrAbove();
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->tenant_id === $workOrder->tenant_id && $user->isManagerOrAbove();
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->tenant_id === $workOrder->tenant_id && $user->isAdmin();
    }

    public function assign(User $user, WorkOrder $workOrder): bool
    {
        return $user->tenant_id === $workOrder->tenant_id && $user->isManagerOrAbove();
    }
}
