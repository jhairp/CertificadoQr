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
        Schema::create('certificados', function (Blueprint $table) {
            $table->id('id_cer');
            $table->string('codigo_cer', 40)->unique(); // código único usado también en el QR
            $table->string('nombre_per_cer', 100);
            $table->string('apellido_per_cer', 100);
            $table->string('carnet_per_cer', 20);
            $table->string('docente_cer', 150);
            $table->string('curso_cer', 150);
            $table->date('fecha_cer');
            $table->string('estado_cer', 20)->default('activo'); // activo | anulado, etc.
            $table->foreignId('id_usu_1')
                ->constrained(table: 'usuarios', column: 'id_usu')
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
        Schema::dropIfExists('certificados');
    }
};
