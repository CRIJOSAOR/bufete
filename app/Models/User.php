<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'activo', 'cargo', 'telefono', 'identidad',
    'numero_colegiado', 'especialidad', 'fecha_ingreso', 'direccion',
    'contacto_emergencia_nombre', 'contacto_emergencia_telefono',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->activo;
    }

    public function puedeVerConfidencial(): bool
    {
        return $this->isPermissionAdministrator() || $this->can('ver_confidencial');
    }

    public function isPermissionAdministrator(): bool
    {
        return $this->hasRole(config('filament-shield.super_admin.name', 'super_admin'));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'fecha_ingreso' => 'date',
        ];
    }
}