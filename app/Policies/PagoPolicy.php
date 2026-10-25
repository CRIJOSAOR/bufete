<?php

namespace App\Policies;

use App\Models\Pago;
use App\Models\User;

class PagoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Pago');
    }

    public function view(User $user, Pago $pago): bool
    {
        return $user->can('View:Pago');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Pago');
    }
}
