<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Caso;
use Illuminate\Auth\Access\HandlesAuthorization;

class CasoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Caso');
    }

    public function view(AuthUser $authUser, Caso $caso): bool
    {
        return $authUser->can('View:Caso');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Caso');
    }

    public function update(AuthUser $authUser, Caso $caso): bool
    {
        return $authUser->can('Update:Caso');
    }

    public function delete(AuthUser $authUser, Caso $caso): bool
    {
        return $authUser->can('Delete:Caso');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Caso');
    }

    public function restore(AuthUser $authUser, Caso $caso): bool
    {
        return $authUser->can('Restore:Caso');
    }

    public function forceDelete(AuthUser $authUser, Caso $caso): bool
    {
        return $authUser->can('ForceDelete:Caso');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Caso');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Caso');
    }

    public function replicate(AuthUser $authUser, Caso $caso): bool
    {
        return $authUser->can('Replicate:Caso');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Caso');
    }

}