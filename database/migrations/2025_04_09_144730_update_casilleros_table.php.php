<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('casilleros', function (Blueprint $table) {
            $table->decimal('costo_mensual', 8, 2)->default(40.00);
            $table->date('fecha_final_llave')->nullable(); // se calculará automáticamente
            $table->unsignedInteger('total_reposiciones')->default(0);
            $table->decimal('monto_reposiciones', 8, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
