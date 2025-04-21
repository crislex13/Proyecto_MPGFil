<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{

    public function up(): void
    {
        Schema::create('conversiones_paquetes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad_convertida')->default(1); // Cuántos paquetes se convirtieron
            $table->timestamp('fecha_conversion')->default(now()); // Fecha y hora de conversión
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversiones_paquetes');
    }
};
