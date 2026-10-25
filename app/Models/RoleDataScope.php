<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleDataScope extends Model
{
    protected $fillable = [
        'role_id',
        'model_type',
        'scope',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
