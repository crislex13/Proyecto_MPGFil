<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Personal;
use App\Models\Asistencia;
use Carbon\Carbon;

class RegistrarFaltasPersonal extends Command
{
    protected $signature = 'personal:registrar-faltas
                            {--fecha= : Fecha a evaluar (YYYY-MM-DD). Por defecto: ayer}
                            {--dry-run : Simula sin insertar}';

    protected $description = 'Registra faltas del personal que tenía turno activo, no asistió y no tenía permiso.';

    public function handle(): int
    {
        // 1) Fecha objetivo (por defecto AYER)
        $fecha = $this->option('fecha')
            ? Carbon::createFromFormat('Y-m-d', $this->option('fecha'))->startOfDay()
            : now()->subDay()->startOfDay();

        $fechaStr = $fecha->toDateString();
        $dry = (bool) $this->option('dry-run');

        $this->info("Evaluando faltas del personal para: {$fechaStr}" . ($dry ? ' (DRY-RUN)' : ''));

        // 2) Día de la semana como entero 1..7 (coincide con tu modelo Turno)
        $dow = $fecha->isoWeekday(); // 1=Lunes … 7=Domingo

        // 3) Personal con turno ACTIVO ese día
        $personales = Personal::whereHas('turnos', function ($q) use ($dow) {
                $q->where('dia', $dow)->where('estado', 'activo');
            })
            ->with(['permisos' => function ($q) use ($fechaStr) {
                $q->where('estado', 'aprobado')->whereDate('fecha', $fechaStr);
            }])
            ->cursor();

        $procesados = 0;
        $faltas = 0;

        foreach ($personales as $p) {
            $procesados++;

            // 4) Si tiene permiso aprobado ese día → no es falta
            $tienePermiso = method_exists($p, 'permisos') && $p->permisos->isNotEmpty();
            if ($tienePermiso) {
                $this->line("✔ {$p->nombre_completo}: permiso aprobado ({$fechaStr}).");
                continue;
            }

            // 5) Si ya tiene alguna asistencia ese día (puntual/atrasado/permiso/salida) → no es falta
            $tieneAsistencia = Asistencia::whereDate('fecha', $fechaStr)
                ->where('asistible_id', $p->id)
                ->where('asistible_type', Personal::class)
                ->exists();

            if ($tieneAsistencia) {
                $this->line("✔ {$p->nombre_completo}: ya tiene asistencia en {$fechaStr}.");
                continue;
            }

            // 6) Idempotencia: ¿ya existe falta registrada para este personal y fecha?
            $existeFalta = Asistencia::whereDate('fecha', $fechaStr)
                ->where('asistible_id', $p->id)
                ->where('asistible_type', Personal::class)
                ->where('tipo_asistencia', 'personal')
                ->where('estado', 'falta')
                ->exists();

            if ($existeFalta) {
                $this->line("• {$p->nombre_completo}: falta ya registrada previamente.");
                continue;
            }

            if ($dry) {
                $this->warn("DRY: Registrar falta a {$p->nombre_completo} ({$fechaStr}).");
                $faltas++;
                continue;
            }

            // 7) Registrar falta
            Asistencia::create([
                'asistible_id'        => $p->id,
                'asistible_type'      => Personal::class,
                'tipo_asistencia'     => 'personal',
                'fecha'               => $fechaStr,
                'estado'              => 'falta',
                'origen'              => 'automatico',
                'observacion'         => "Falta injustificada el {$fecha->format('d/m/Y')}. No asistió ni tenía permiso.",
                'usuario_registro_id' => null,
            ]);

            $this->warn("❌ {$p->nombre_completo}: falta registrada.");
            $faltas++;
        }

        $this->info("✅ Procesados: {$procesados} | Faltas registradas: {$faltas}");
        return self::SUCCESS;
    }
}
