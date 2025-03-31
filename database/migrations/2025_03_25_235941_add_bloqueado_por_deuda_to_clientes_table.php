<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('bloqueado_por_deuda')->default(false)->after('estado');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('clientes', function (Blueprint $table) {
        $table->dropColumn('bloqueado_por_deuda');
    });
}
};
