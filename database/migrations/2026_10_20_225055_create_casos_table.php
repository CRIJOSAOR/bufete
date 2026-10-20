<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // número de expediente
            $table->string('titulo');
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->string('tipo');
            $table->string('juzgado')->nullable();
            $table->string('estado')->default('abierto');
            $table->date('fecha_inicio')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('notas_internas')->nullable(); // confidencial
            $table->text('estrategia')->nullable();     // confidencial
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};