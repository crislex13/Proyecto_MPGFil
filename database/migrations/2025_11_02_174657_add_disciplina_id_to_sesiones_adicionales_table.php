<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            // 1) Agregar la FK a disciplinas (después de cliente_id para orden)
            if (!Schema::hasColumn('sesiones_adicionales', 'disciplina_id')) {
                $table->foreignId('disciplina_id')
                    ->after('cliente_id')
                    ->constrained('disciplinas')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }

            // 2) Asegurar índices útiles (reportes / filtros)
            $table->index(['disciplina_id', 'instructor_id', 'fecha'], 'sesiones_disc_inst_fecha_idx');

            // 3) (Transición) Si existe tipo_sesion, hacerlo nullable para no romper inserts
            if (Schema::hasColumn('sesiones_adicionales', 'tipo_sesion')) {
                $table->string('tipo_sesion')->nullable()->change();
            }

            // 4) (Opcional) Si NO tienes hora_inicio/hora_fin, créalos
            if (!Schema::hasColumn('sesiones_adicionales', 'hora_inicio')) {
                $table->time('hora_inicio')->nullable()->after('turno_id');
            }
            if (!Schema::hasColumn('sesiones_adicionales', 'hora_fin')) {
                $table->time('hora_fin')->nullable()->after('hora_inicio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            // Quitar índice
            $table->dropIndex('sesiones_disc_inst_fecha_idx');

            // Quitar FK y columna disciplina_id
            if (Schema::hasColumn('sesiones_adicionales', 'disciplina_id')) {
                $table->dropConstrainedForeignId('disciplina_id');
            }

            // Revertir tipo_sesion a NOT NULL si así estaba (opcional)
            if (Schema::hasColumn('sesiones_adicionales', 'tipo_sesion')) {
                $table->string('tipo_sesion')->nullable(false)->change();
            }

            // (Opcional) eliminar horas si las agregaste aquí
            if (Schema::hasColumn('sesiones_adicionales', 'hora_fin')) {
                $table->dropColumn('hora_fin');
            }
            if (Schema::hasColumn('sesiones_adicionales', 'hora_inicio')) {
                $table->dropColumn('hora_inicio');
            }
        });
    }
};
