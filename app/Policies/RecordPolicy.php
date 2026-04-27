<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view records in their tenant
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Record $record): bool
    {
        // User can view if record belongs to their tenant
        return $user->tenant_id === $record->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create records
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Record $record): bool
    {
        if ($user->tenant_id !== $record->tenant_id) {
            return false;
        }
        return $user->isManagerOrAbove() || $user->id === $record->submitted_by;
    }

    public function delete(User $user, Record $record): bool
    {
        if ($user->tenant_id !== $record->tenant_id) {
            return false;
        }
        return $user->isManagerOrAbove();
    }

    public function restore(User $user, Record $record): bool
    {
        return $user->tenant_id === $record->tenant_id && $user->isAdmin();
    }

    public function forceDelete(User $user, Record $record): bool
    {
        return $user->tenant_id === $record->tenant_id && $user->isAdmin();
    }

    public function assign(User $user, Record $record): bool
    {
        return $user->tenant_id === $record->tenant_id && $user->isManagerOrAbove();
    }

    public function requestApproval(User $user, Record $record): bool
    {
        return $user->tenant_id === $record->tenant_id && $user->isManagerOrAbove();
    }

    public function comment(User $user, Record $record): bool
    {
        return $user->tenant_id === $record->tenant_id;
    }
}
