<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null && $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->tenant_id === $model->tenant_id && $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->tenant_id === $model->tenant_id && $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->tenant_id === $model->tenant_id
            && $user->isAdmin()
            && $user->id !== $model->id; // cannot delete self
    }
}
