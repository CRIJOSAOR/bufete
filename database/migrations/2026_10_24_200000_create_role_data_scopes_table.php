<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_data_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('model_type');
            $table->string('scope');
            $table->timestamps();
            $table->unique(['role_id', 'model_type', 'scope']);
        });

        if (! Schema::hasTable('roles')) {
            return;
        }

        foreach (DB::table('roles')->get(['id', 'name', 'guard_name']) as $role) {
            foreach ([App\Models\Cliente::class, App\Models\Caso::class] as $modelClass) {
                $scopes = match (true) {
                    $role->name === 'abogado' && $modelClass === App\Models\Cliente::class => [
                        'created_by_me',
                        'assigned_to_me',
                    ],
                    $role->name === 'abogado' => ['assigned_to_me'],
                    $role->name === config('filament-shield.super_admin.name', 'super_admin')
                    || $role->name === 'secretaria' => ['all'],
                    default => [],
                };

                foreach ($scopes as $scope) {
                    DB::table('role_data_scopes')->insert([
                        'role_id' => $role->id,
                        'model_type' => $modelClass,
                        'scope' => $scope,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($role->name !== 'abogado') {
                continue;
            }

            foreach (['ver_confidencial', 'ser_asignable_a_casos'] as $permissionName) {
                if (! config("filament-shield.custom_permissions.{$permissionName}")) {
                    continue;
                }

                $permissionId = DB::table('permissions')
                    ->where('name', $permissionName)
                    ->where('guard_name', $role->guard_name)
                    ->value('id');

                if (! $permissionId) {
                    $permissionId = DB::table('permissions')->insertGetId([
                        'name' => $permissionName,
                        'guard_name' => $role->guard_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $role->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_data_scopes');
    }
};
