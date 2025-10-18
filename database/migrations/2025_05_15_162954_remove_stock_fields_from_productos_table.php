<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock_unidades');
            $table->dropColumn('stock_paquetes');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedInteger('stock_unidades')->default(0);
            $table->unsignedInteger('stock_paquetes')->nullable();
        });
    }
};
