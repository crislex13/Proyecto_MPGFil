<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lote_productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->date('fecha_ingreso');
            $table->date('fecha_vencimiento')->nullable();

            $table->unsignedInteger('stock_unidades')->default(0);
            $table->unsignedInteger('stock_paquetes')->nullable();

            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('precio_paquete', 10, 2)->nullable();

            $table->boolean('es_perecedero')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_productos');
    }
};