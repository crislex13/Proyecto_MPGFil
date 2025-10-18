<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('detalle_ventas_productos', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_convertida_desde_paquete')->default(0)->after('lote_producto_id');
        });
    }

    public function down()
    {
        Schema::table('detalle_ventas_productos', function (Blueprint $table) {
            $table->dropColumn('cantidad_convertida_desde_paquete');
        });
    }
};
