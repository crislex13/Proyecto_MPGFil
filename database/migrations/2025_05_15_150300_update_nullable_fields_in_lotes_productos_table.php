<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lote_productos', function (Blueprint $table) {
            $table->integer('stock_unidades')->unsigned()->nullable()->change();
            $table->integer('stock_paquetes')->unsigned()->nullable()->change();
            $table->decimal('precio_unitario', 10, 2)->nullable()->change();
            $table->decimal('precio_paquete', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lote_productos', function (Blueprint $table) {
            $table->integer('stock_unidades')->unsigned()->default(0)->change();
            $table->integer('stock_paquetes')->unsigned()->nullable()->change(); // ya era nullable
            $table->decimal('precio_unitario', 10, 2)->default(0)->change();
            $table->decimal('precio_paquete', 10, 2)->nullable()->change(); // ya era nullable
        });
    }
};
