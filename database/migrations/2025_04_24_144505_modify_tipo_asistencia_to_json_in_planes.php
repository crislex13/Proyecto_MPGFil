<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Eliminar la columna ENUM original
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn('tipo_asistencia');
        });

        // Crear nuevamente la columna como JSON
        Schema::table('planes', function (Blueprint $table) {
            $table->json('tipo_asistencia')->nullable()->after('ingresos_ilimitados');
        });

        // Agregamos valor por defecto a todos los registros
        DB::table('planes')->update(['tipo_asistencia' => json_encode(['libre'])]);
    }

    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn('tipo_asistencia');
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->enum('tipo_asistencia', ['libre', 'dias_especificos', 'dias_consecutivos'])
                ->default('libre')
                ->after('ingresos_ilimitados');
        });
    }
};