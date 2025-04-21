<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planes_clientes', function (Blueprint $table) {
            $table->dropColumn('casillero_monto');
        });
    }

    public function down(): void
    {
        Schema::table('planes_clientes', function (Blueprint $table) {
            $table->decimal('casillero_monto', 10, 2)->default(0.00);
        });
    }
};
