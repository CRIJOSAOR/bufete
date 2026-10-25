<?php

namespace App\Policies;

use App\Models\Honorario;
use App\Models\User;

class HonorarioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Honorario');
    }

    public function view(User $user, Honorario $honorario): bool
    {
        return $user->can('View:Honorario');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Honorario');
    }

    public function update(User $user, Honorario $honorario): bool
    {
        return $user->can('Update:Honorario');
    }

    public function delete(User $user, Honorario $honorario): bool
    {
        return $user->can('Delete:Honorario');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Honorario');
    }
}
