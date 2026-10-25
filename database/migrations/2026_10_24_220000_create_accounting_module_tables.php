<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honorarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('caso_id')->nullable()->constrained('casos')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('concepto');
            $table->string('modalidad')->default('fijo');
            $table->decimal('monto_acordado', 14, 2);
            $table->char('moneda', 3);
            $table->date('fecha_vencimiento')->nullable()->index();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['cliente_id', 'moneda']);
            $table->index(['caso_id', 'fecha_vencimiento']);
        });

        Schema::create('pagos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('honorario_id')->constrained('honorarios')->restrictOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('monto', 14, 2);
            $table->dateTime('fecha_pago')->index();
            $table->string('metodo');
            $table->string('referencia')->nullable();
            $table->string('comprobante_ruta')->nullable();
            $table->text('comprobante_original')->nullable();
            $table->string('comprobante_mime', 150)->nullable();
            $table->unsignedBigInteger('comprobante_tamano')->nullable();
            $table->timestamps();
            $table->index(['honorario_id', 'fecha_pago']);
        });

        Schema::create('gastos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('caso_id')->nullable()->constrained('casos')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('concepto');
            $table->string('categoria');
            $table->decimal('monto', 14, 2);
            $table->char('moneda', 3);
            $table->date('fecha')->index();
            $table->string('proveedor')->nullable();
            $table->string('pagado_por')->nullable();
            $table->boolean('reembolsable')->default(false);
            $table->boolean('reembolsado')->default(false);
            $table->string('comprobante_ruta')->nullable();
            $table->text('comprobante_original')->nullable();
            $table->string('comprobante_mime', 150)->nullable();
            $table->unsignedBigInteger('comprobante_tamano')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['caso_id', 'fecha']);
            $table->index(['cliente_id', 'fecha']);
            $table->index(['categoria', 'fecha']);
        });

        Schema::create('fondos_cliente_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('caso_id')->nullable()->constrained('casos')->nullOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo');
            $table->decimal('monto', 14, 2);
            $table->char('moneda', 3);
            $table->dateTime('fecha')->index();
            $table->string('concepto');
            $table->string('referencia')->nullable();
            $table->string('comprobante_ruta')->nullable();
            $table->text('comprobante_original')->nullable();
            $table->string('comprobante_mime', 150)->nullable();
            $table->unsignedBigInteger('comprobante_tamano')->nullable();
            $table->timestamps();
            $table->index(['cliente_id', 'moneda', 'fecha']);
            $table->index(['caso_id', 'fecha']);
        });

        $permissions = [];
        foreach ([
            'Honorario' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'],
            'Pago' => ['ViewAny', 'View', 'Create'],
            'Gasto' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'],
            'FondoClienteMovimiento' => ['ViewAny', 'View', 'Create'],
        ] as $subject => $actions) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'name' => $action.':'.$subject,
                    'guard_name' => 'web',
                ];
            }
        }

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert($permission, $permission);
        }

        DB::table('permissions')->updateOrInsert(
            ['name' => 'consultar_reportes_contables', 'guard_name' => 'web'],
            ['name' => 'consultar_reportes_contables', 'guard_name' => 'web'],
        );

        $superAdminId = DB::table('roles')
            ->where('name', config('filament-shield.super_admin.name', 'super_admin'))
            ->where('guard_name', 'web')
            ->value('id');

        if ($superAdminId) {
            $permissionIds = DB::table('permissions')
                ->where('guard_name', 'web')
                ->where(function ($query): void {
                    $query->whereIn('name', [
                        'ViewAny:Honorario', 'View:Honorario', 'Create:Honorario', 'Update:Honorario', 'Delete:Honorario', 'DeleteAny:Honorario',
                        'ViewAny:Pago', 'View:Pago', 'Create:Pago',
                        'ViewAny:Gasto', 'View:Gasto', 'Create:Gasto', 'Update:Gasto', 'Delete:Gasto', 'DeleteAny:Gasto',
                        'ViewAny:FondoClienteMovimiento', 'View:FondoClienteMovimiento', 'Create:FondoClienteMovimiento',
                        'consultar_reportes_contables',
                    ]);
                })
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $superAdminId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'ViewAny:Honorario', 'View:Honorario', 'Create:Honorario', 'Update:Honorario', 'Delete:Honorario', 'DeleteAny:Honorario',
                'ViewAny:Pago', 'View:Pago', 'Create:Pago',
                'ViewAny:Gasto', 'View:Gasto', 'Create:Gasto', 'Update:Gasto', 'Delete:Gasto', 'DeleteAny:Gasto',
                'ViewAny:FondoClienteMovimiento', 'View:FondoClienteMovimiento', 'Create:FondoClienteMovimiento',
                'consultar_reportes_contables',
            ])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::dropIfExists('fondos_cliente_movimientos');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('honorarios');
    }
};
