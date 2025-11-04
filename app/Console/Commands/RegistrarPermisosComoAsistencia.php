<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PermisoPersonal;
use App\Models\Personal;
use App\Models\Asistencia;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class RegistrarPermisosComoAsistencia extends Command
{
    protected $signature = 'asistencias:registrar-permisos
                            {--fecha= : Fecha a procesar (YYYY-MM-DD). Por defecto: hoy}
                            {--dry-run : Simula sin insertar}';

    protected $description = 'Registra asistencia con estado "permiso" para instructores con permiso aprobado en la fecha indicada.';

    public function handle(): int
    {
        // 1) Fecha objetivo
        $fecha = $this->option('fecha')
            ? Carbon::createFromFormat('Y-m-d', $this->option('fecha'))->startOfDay()
            : Carbon::today();

        $fechaStr = $fecha->toDateString();
        $dry = (bool) $this->option('dry-run');

        $this->info("Procesando permisos como asistencia para: {$fechaStr}" . ($dry ? ' (DRY-RUN)' : ''));

        // 2) Permisos aprobados que cubran la fecha
        $permisos = PermisoPersonal::query()
            ->whereDate('fecha_inicio', '<=', $fechaStr)
            ->whereDate('fecha_fin', '>=', $fechaStr)
            ->where('estado', 'aprobado')
            ->get();

        if ($permisos->isEmpty()) {
            $this->warn('No hay permisos aprobados para esa fecha.');
            return SymfonyCommand::SUCCESS;
        }

        // 3) Evitar duplicados por persona (si tienen >1 permiso ese día)
        $personalIds = $permisos->pluck('personal_id')->unique()->values();

        $registradas = 0;

        foreach ($personalIds as $personalId) {
            // Si no existe el personal (baja, etc.), salta silenciosamente
            $personal = Personal::find($personalId);
            if (!$personal) {
                $this->line("Saltando personal_id={$personalId} (no existe).");
                continue;
            }

            // 4) Idempotencia: si YA tiene cualquier asistencia/permiso/falta ese día, no insertar
            $yaTieneAlgo = Asistencia::query()
                ->where('asistible_type', Personal::class)
                ->where('asistible_id', $personal->id)
                ->whereDate('fecha', $fechaStr)
                ->exists();

            if ($yaTieneAlgo) {
                $this->line("✔ {$personal->nombre_completo}: ya tiene registro de asistencia en {$fechaStr}.");
                continue;
            }

            if ($dry) {
                $this->warn("DRY: Insertaría asistencia=permiso para personal #{$personal->id} ({$fechaStr}).");
                $registradas++;
                continue;
            }

            Asistencia::create([
                'asistible_id'        => $personal->id,
                'asistible_type'      => Personal::class,
                'tipo_asistencia'     => 'personal',
                'fecha'               => $fechaStr,
                'hora_entrada'        => null,                 // opcional; puede quedar null
                'estado'              => 'permiso',
                'origen'              => 'automatico',
                'usuario_registro_id' => null,                 // no hardcodear IDs
                'observacion'         => 'Registro automático por permiso aprobado.',
            ]);

            $this->info("➕ Permiso registrado como asistencia para: {$personal->nombre_completo}");
            $registradas++;
        }

        $this->info("✅ Total insertadas: {$registradas}");
        return SymfonyCommand::SUCCESS;
    }
}
