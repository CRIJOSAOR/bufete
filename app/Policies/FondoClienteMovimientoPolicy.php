<?php

namespace App\Policies;

use App\Models\FondoClienteMovimiento;
use App\Models\User;

class FondoClienteMovimientoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:FondoClienteMovimiento');
    }

    public function view(User $user, FondoClienteMovimiento $movimiento): bool
    {
        return $user->can('View:FondoClienteMovimiento');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:FondoClienteMovimiento');
    }
}
