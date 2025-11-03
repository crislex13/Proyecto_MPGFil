<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            if (!Schema::hasColumn('sesiones_adicionales', 'disciplina_id')) {
                $table->unsignedBigInteger('disciplina_id')->nullable()->after('cliente_id');
            }
        });

        // Backfill desde tipo_sesion -> disciplina_id
        $sesiones = DB::table('sesiones_adicionales')->select('id', 'tipo_sesion')->get();
        foreach ($sesiones as $s) {
            $nombre = trim((string) $s->tipo_sesion);
            if ($nombre === '')
                continue;

            $disc = DB::table('disciplinas')->where('nombre', $nombre)->first();
            if (!$disc) {
                $disciplinaId = DB::table('disciplinas')->insertGetId([
                    'nombre' => $nombre,
                    'descripcion' => $nombre . ' (autoimportada)',
                    'observaciones' => '',          // <- evitar NULL
                    'registrado_por' => 1,           // o auth()->id() si corres desde app
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $disciplinaId = $disc->id;
            }

            DB::table('sesiones_adicionales')
                ->where('id', $s->id)
                ->update(['disciplina_id' => $disciplinaId]);
        }

        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            $table->index(['disciplina_id', 'instructor_id', 'fecha'], 'sesiones_disc_inst_fecha_idx');
            $table->foreign('disciplina_id')
                ->references('id')->on('disciplinas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // (Opcional) Volver NOT NULL si ya no hay nulos (requiere doctrine/dbal)
        // if (DB::table('sesiones_adicionales')->whereNull('disciplina_id')->count() === 0) {
        //     $tableName = 'sesiones_adicionales';
        //     Schema::table($tableName, function (Blueprint $table) {
        //         $table->unsignedBigInteger('disciplina_id')->nullable(false)->change();
        //     });
        // }
    }

    public function down(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            if (Schema::hasColumn('sesiones_adicionales', 'disciplina_id')) {
                $table->dropForeign(['disciplina_id']);
                $table->dropIndex('sesiones_disc_inst_fecha_idx');
                $table->dropColumn('disciplina_id');
            }
        });
    }
};
