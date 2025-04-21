<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('ingresos_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('usuario_id'); // quien lo ingresó
            $table->integer('cantidad_unidades')->unsigned()->default(0);
            $table->integer('cantidad_paquetes')->unsigned()->default(0)->nullable();
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('precio_paquete', 10, 2)->nullable();
            $table->string('observacion')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos_productos');
    }
};