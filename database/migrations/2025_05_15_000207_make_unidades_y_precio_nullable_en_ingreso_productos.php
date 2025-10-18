<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingresos_productos', function (Blueprint $table) {
            $table->integer('cantidad_unidades')->unsigned()->nullable()->default(null)->change();
            $table->decimal('precio_unitario', 10, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ingresos_productos', function (Blueprint $table) {
            $table->integer('cantidad_unidades')->unsigned()->default(0)->change();
            $table->decimal('precio_unitario', 10, 2)->default(0.00)->change();
        });
    }
};