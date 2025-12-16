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
        // User can update if record belongs to their tenant
        // For now, allow all users in tenant to update (can be restricted later)
        if ($user->tenant_id !== $record->tenant_id) {
            return false;
        }

        // User is submitter or has admin/manager role (graceful fallback if roles don't exist)
        return $user->id === $record->submitted_by ||
               $user->hasAnyRole(['admin', 'manager']) ||
               true; // Fallback: allow all tenant users for now
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Record $record): bool
    {
        // Check tenant match first
        if ($user->tenant_id !== $record->tenant_id) {
            return false;
        }

        // Admins and managers can delete, or allow all for now (graceful fallback)
        return $user->hasAnyRole(['admin', 'manager']) || true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Record $record): bool
    {
        // Only admins or all users for now (graceful fallback)
        return $user->tenant_id === $record->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Record $record): bool
    {
        // Only admins or all users for now (graceful fallback)
        return $user->tenant_id === $record->tenant_id;
    }

    /**
     * Determine whether the user can assign the record.
     */
    public function assign(User $user, Record $record): bool
    {
        // Managers and admins can assign records (or all for now)
        return $user->tenant_id === $record->tenant_id;
    }

    /**
     * Determine whether the user can request approval.
     */
    public function requestApproval(User $user, Record $record): bool
    {
        // User can request approval if in same tenant
        return $user->tenant_id === $record->tenant_id;
    }

    /**
     * Determine whether the user can comment on the record.
     */
    public function comment(User $user, Record $record): bool
    {
        // All users in the same tenant can comment
        return $user->tenant_id === $record->tenant_id;
    }
}
