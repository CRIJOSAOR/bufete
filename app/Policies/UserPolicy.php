<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('View:User');
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function update(AuthUser $authUser, User $user): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function delete(AuthUser $authUser, User $user): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function restore(AuthUser $authUser, User $user): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function forceDelete(AuthUser $authUser, User $user): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function replicate(AuthUser $authUser, User $user): bool
    {
        return $this->isAdministrator($authUser);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->isAdministrator($authUser);
    }

    private function isAdministrator(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isPermissionAdministrator();
    }

}