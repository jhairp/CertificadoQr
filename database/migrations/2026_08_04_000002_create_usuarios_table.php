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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usu');
            $table->string('nombre_usu', 100);
            $table->string('correo_usu', 150)->unique();
            $table->string('password_usu');
            $table->boolean('estado_usu')->default(true); // true = activo, false = inactivo
            $table->foreignId('id_rol_1')
                ->constrained(table: 'roles', column: 'id_rol')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
