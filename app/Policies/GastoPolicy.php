<?php

namespace App\Policies;

use App\Models\Gasto;
use App\Models\User;

class GastoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Gasto');
    }

    public function view(User $user, Gasto $gasto): bool
    {
        return $user->can('View:Gasto');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Gasto');
    }

    public function update(User $user, Gasto $gasto): bool
    {
        return $user->can('Update:Gasto');
    }

    public function delete(User $user, Gasto $gasto): bool
    {
        return $user->can('Delete:Gasto');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Gasto');
    }
}
