<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caso_tareas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos')->cascadeOnDelete();
            $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('prioridad')->default('normal');
            $table->string('estado')->default('pendiente');
            $table->timestamp('fecha_limite')->nullable()->index();
            $table->timestamp('completada_en')->nullable();
            $table->timestamps();
            $table->index(['caso_id', 'estado']);
            $table->index(['asignado_a', 'estado', 'fecha_limite']);
        });

        Schema::create('caso_audiencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos')->cascadeOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->dateTime('fecha_hora')->index();
            $table->string('lugar')->nullable();
            $table->string('modalidad')->default('presencial');
            $table->string('estado')->default('programada');
            $table->text('resultado')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['responsable_id', 'fecha_hora']);
        });

        Schema::create('caso_eventos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos')->cascadeOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo')->default('nota');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->dateTime('ocurrido_en')->index();
            $table->timestamps();
            $table->index(['caso_id', 'ocurrido_en']);
        });

        Schema::create('caso_documentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos')->cascadeOnDelete();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->string('categoria')->default('otro');
            $table->text('descripcion')->nullable();
            $table->string('ruta');
            $table->text('nombre_original');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('tamano');
            $table->timestamps();
            $table->index(['caso_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caso_documentos');
        Schema::dropIfExists('caso_eventos');
        Schema::dropIfExists('caso_audiencias');
        Schema::dropIfExists('caso_tareas');
    }
};
