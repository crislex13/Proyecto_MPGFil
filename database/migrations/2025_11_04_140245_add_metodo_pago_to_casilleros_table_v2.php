<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('casilleros', function (Blueprint $table) {
            $table->string('metodo_pago', 20)->nullable()->after('costo_mensual');
            $table->string('metodo_pago_reposicion', 20)->nullable()->after('monto_reposiciones');
            $table->index('metodo_pago');
            $table->index('metodo_pago_reposicion');
        });
    }
    public function down(): void
    {
        Schema::table('casilleros', function (Blueprint $table) {
            $table->dropIndex(['metodo_pago']);
            $table->dropIndex(['metodo_pago_reposicion']);
            $table->dropColumn(['metodo_pago', 'metodo_pago_reposicion']);
        });
    }
};