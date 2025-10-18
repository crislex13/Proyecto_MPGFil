<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lote_productos', function (Blueprint $table) {
            $table->foreignId('ingreso_producto_id')
                ->nullable()
                ->after('producto_id')
                ->constrained('ingresos_productos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lote_productos', function (Blueprint $table) {
            $table->dropForeign(['ingreso_producto_id']);
            $table->dropColumn('ingreso_producto_id');
        });
    }
};
