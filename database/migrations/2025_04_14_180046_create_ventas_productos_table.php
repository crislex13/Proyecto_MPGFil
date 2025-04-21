<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('ventas_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id'); // Para identificar quién vendió
            $table->enum('metodo_pago', ['efectivo', 'qr'])->default('efectivo');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_productos');
    }
};