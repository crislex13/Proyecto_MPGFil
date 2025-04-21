<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PermisoPersonal;
use App\Models\Turno;
use App\Models\Asistencia;
use Carbon\Carbon;

class RegistrarPermisosComoAsistencia extends Command
{
    protected $signature = 'asistencias:registrar-permisos';
    protected $description = 'Registra asistencia con estado "permiso" para instructores con permiso activo hoy';

    public function handle()
    {
        $hoy = Carbon::today();

        // Obtener todos los permisos activos para hoy
        $permisos = PermisoPersonal::whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_fin', '>=', $hoy)
            ->where('estado', 'aprobado')
            ->get();

        $totalRegistrados = 0;

        foreach ($permisos as $permiso) {
            $personalId = $permiso->personal_id;

            // Verificar si ya hay asistencia registrada
            $yaRegistrado = Asistencia::where('asistible_type', 'App\\Models\\Personal')
                ->where('asistible_id', $personalId)
                ->whereDate('fecha', $hoy)
                ->exists();

            if (!$yaRegistrado) {
                Asistencia::create([
                    'asistible_id' => $personalId,
                    'asistible_type' => 'App\\Models\\Personal',
                    'tipo_asistencia' => 'personal',
                    'fecha' => $hoy,
                    'estado' => 'permiso',
                    'origen' => 'auto',
                    'usuario_registro_id' => 1, // O un usuario del sistema automático
                    'observacion' => 'Permiso automático: ' . $permiso->motivo,
                ]);

                $this->info("Asistencia registrada como permiso para personal ID: $personalId");
                $totalRegistrados++;
            }
        }

        if ($totalRegistrados > 0) {
            $this->info("✅ Total asistencias registradas automáticamente: $totalRegistrados");
        } else {
            $this->warn("⚠️  No se registraron asistencias con permiso. Ningún permiso activo o ya estaban registrados.");
        }

        return 0;
    }
}