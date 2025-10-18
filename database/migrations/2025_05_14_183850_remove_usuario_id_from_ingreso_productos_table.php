<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingresos_productos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::table('ingresos_productos', function (Blueprint $table) {
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }
};