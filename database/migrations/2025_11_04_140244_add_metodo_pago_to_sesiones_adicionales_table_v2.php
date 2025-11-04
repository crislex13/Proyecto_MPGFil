<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            $table->string('metodo_pago', 20)->nullable()->after('precio');
            $table->index('metodo_pago');
        });
    }
    public function down(): void {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            $table->dropIndex(['metodo_pago']);
            $table->dropColumn('metodo_pago');
        });
    }
};