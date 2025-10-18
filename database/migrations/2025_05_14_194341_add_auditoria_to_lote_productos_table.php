<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lote_productos', function (Blueprint $table) {
            if (!Schema::hasColumn('lote_productos', 'registrado_por')) {
                $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('lote_productos', 'modificado_por')) {
                $table->foreignId('modificado_por')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lote_productos', function (Blueprint $table) {
            $table->dropForeign(['registrado_por']);
            $table->dropColumn('registrado_por');

            $table->dropForeign(['modificado_por']);
            $table->dropColumn('modificado_por');
        });
    }
};