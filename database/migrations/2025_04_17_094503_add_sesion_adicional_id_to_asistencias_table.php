<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->foreignId('sesion_adicional_id')
                ->nullable()
                ->after('tipo_asistencia')
                ->constrained('sesiones_adicionales');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign(['sesion_adicional_id']);
            $table->dropColumn('sesion_adicional_id');
        });
    }
};