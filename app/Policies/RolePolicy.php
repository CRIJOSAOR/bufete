<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(User $authUser): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function view(User $authUser, Role $role): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function create(User $authUser): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function update(User $authUser, Role $role): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function delete(User $authUser, Role $role): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function restore(User $authUser, Role $role): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function forceDelete(User $authUser, Role $role): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function replicate(User $authUser, Role $role): bool
    {
        return $authUser->isPermissionAdministrator();
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->isPermissionAdministrator();
    }

}