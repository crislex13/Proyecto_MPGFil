<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detalle_ventas_productos', function (Blueprint $table) {
            $table->unsignedBigInteger('lote_producto_id')->nullable()->after('producto_id');
            $table->foreign('lote_producto_id')->references('id')->on('lote_productos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas_productos', function (Blueprint $table) {
            $table->dropForeign(['lote_producto_id']);
            $table->dropColumn('lote_producto_id');
        });
    }
};
