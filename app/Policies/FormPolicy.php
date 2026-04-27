<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, Form $form): bool
    {
        return $user->tenant_id === $form->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->isAdmin();
    }

    public function update(User $user, Form $form): bool
    {
        return $user->tenant_id === $form->tenant_id && $user->isAdmin();
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->tenant_id === $form->tenant_id && $user->isAdmin();
    }

    public function publish(User $user, Form $form): bool
    {
        return $user->tenant_id === $form->tenant_id && $user->isAdmin();
    }

    public function clone(User $user, Form $form): bool
    {
        return $user->tenant_id === $form->tenant_id && $user->isAdmin();
    }
}
