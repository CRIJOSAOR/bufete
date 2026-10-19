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
    Schema::create('clientes', function (Blueprint $table) {
        $table->id();
        $table->string('tipo')->default('natural'); // natural o juridica
        $table->string('nombre');
        $table->string('documento')->nullable()->unique(); // identidad o RTN
        $table->string('telefono')->nullable();
        $table->string('correo')->nullable();
        $table->string('direccion')->nullable();
        $table->text('notas')->nullable();
        $table->text('notas_privadas')->nullable(); // confidencial
        $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
        $table->softDeletes();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
