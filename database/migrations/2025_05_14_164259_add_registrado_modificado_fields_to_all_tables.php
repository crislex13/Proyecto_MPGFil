<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tablas = [
            'asistencias',
            'casilleros',
            'categorias',
            'clientes',
            'configuraciones',
            'conversiones_paquetes',
            'detalle_ventas_productos',
            'disciplinas',
            'ingresos_productos',
            'pagos_personal',
            'permisos_clientes',
            'permisos_personal',
            'personals',
            'planes',
            'planes_clientes',
            'plan_disciplinas',
            'productos',
            'salas',
            'sesiones_adicionales',
            'turnos',
            'ventas_productos',
        ];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (!Schema::hasColumn($tabla, 'registrado_por')) {
                    $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
                }

                if (!Schema::hasColumn($tabla, 'modificado_por')) {
                    $table->foreignId('modificado_por')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        $tablas = [
            'asistencias',
            'casilleros',
            'categorias',
            'clientes',
            'configuraciones',
            'conversiones_paquetes',
            'detalle_ventas_productos',
            'disciplinas',
            'ingresos_productos',
            'pagos_personal',
            'permisos_clientes',
            'permisos_personal',
            'personals',
            'planes',
            'planes_clientes',
            'plan_disciplinas',
            'productos',
            'salas',
            'sesiones_adicionales',
            'turnos',
            'ventas_productos',
        ];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['registrado_por']);
                $table->dropColumn('registrado_por');
                $table->dropForeign(['modificado_por']);
                $table->dropColumn('modificado_por');
            });
        }
    }
};