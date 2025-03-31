<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('ci')->unique();
            $table->string('telefono')->nullable;
            $table->string('direccion')->nullable();
            $table->date('fecha_de_nacimiento');
            $table->string('correo')->unique();
            $table->string('cargo');
            $table->string('horario');
            $table->decimal('salario', 10, 2);
            $table->date('fecha_contratacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
