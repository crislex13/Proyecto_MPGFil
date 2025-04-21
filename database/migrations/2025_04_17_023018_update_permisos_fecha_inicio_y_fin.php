<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permisos_personal', function (Blueprint $table) {
            // Si quieres mantener la columna original también, comenta esta línea:
            if (Schema::hasColumn('permisos_personal', 'fecha')) {
                $table->dropColumn('fecha');
            }

            $table->date('fecha_inicio')->after('personal_id');
            $table->date('fecha_fin')->after('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('permisos', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);

            // Reponemos el campo original si se revierte
            $table->date('fecha')->after('personal_id');
        });
    }
};