<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->time('hora_inicio')->nullable()->after('ingresos_ilimitados');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->enum('tipo_asistencia', ['libre', 'dias_seleccionados', 'dias_consecutivos'])
                ->default('libre')
                ->after('hora_fin');
        });

        Schema::table('planes_clientes', function (Blueprint $table) {
            $table->json('dias_permitidos')->nullable()->after('fecha_final');
        });
    }

    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fin', 'tipo_asistencia']);
        });

        Schema::table('planes_clientes', function (Blueprint $table) {
            $table->dropColumn('dias_permitidos');
        });
    }
};
