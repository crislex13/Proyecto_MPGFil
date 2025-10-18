<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detalle_ventas_productos', function (Blueprint $table) {
            $table->foreignId('lote_origen_id')->nullable()->after('lote_producto_id')->constrained('lote_productos');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas_productos', function (Blueprint $table) {
            $table->dropForeign(['lote_origen_id']);
            $table->dropColumn('lote_origen_id');
        });
    }
};
