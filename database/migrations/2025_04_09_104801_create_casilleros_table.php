<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('casilleros', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('ubicacion')->nullable();

            $table->enum('estado', ['disponible', 'ocupado', 'mantenimiento'])->default('disponible');
            $table->foreignId('cliente_id')->nullable()->constrained()->onDelete('set null');

            $table->date('fecha_entrega_llave')->nullable();
            $table->date('fecha_retorno_llave')->nullable();
            $table->decimal('reposicion_llave', 8, 2)->default(0.00)->change();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casilleros');
    }
};