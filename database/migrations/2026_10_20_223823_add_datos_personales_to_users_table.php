<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('cargo')->nullable()->after('email');
        $table->string('telefono', 40)->nullable();
        $table->string('identidad', 30)->nullable()->unique();
        $table->string('numero_colegiado', 30)->nullable();
        $table->string('especialidad')->nullable();
        $table->date('fecha_ingreso')->nullable();
        $table->string('direccion')->nullable();
        $table->string('contacto_emergencia_nombre')->nullable();
        $table->string('contacto_emergencia_telefono', 40)->nullable();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'cargo', 'telefono', 'identidad', 'numero_colegiado', 'especialidad',
            'fecha_ingreso', 'direccion', 'contacto_emergencia_nombre',
            'contacto_emergencia_telefono',
        ]);
    });
}
};
