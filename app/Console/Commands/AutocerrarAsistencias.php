<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asistencia;
use App\Services\AsistenciaService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command as SymfonyCommand;


class AutocerrarAsistencias extends Command
{
    protected $signature = 'asistencias:auto-cerrar {--dry-run}';
    protected $description = 'Autocierra asistencias abiertas cuyo fin programado ya pasó';

    public function handle(): int
    {
        if (!config('maxpower.autocierre.habilitado')) {
            $this->info('Autocierre deshabilitado.');
            return SymfonyCommand::SUCCESS;
        }

        $gracia = (int) config('maxpower.autocierre.gracia_min', 5);
        $dry    = (bool) $this->option('dry-run');

        $abiertas = Asistencia::query()
            ->whereNull('hora_salida')
            ->latest('hora_entrada')
            ->get();

        $total = 0; $cerradas = 0;

        foreach ($abiertas as $a) {
            $total++;

            $fin = AsistenciaService::finProgramadoPara($a);

            // Si el plan no tiene hora_fin, usa duración por defecto
            if (!$fin && $a->tipo_asistencia === 'plan') {
                $mins = (int) config('maxpower.plan_duracion_defecto_min', 90);
                $fin  = Carbon::parse($a->hora_entrada)->copy()->addMinutes($mins);
            }

            if (!$fin) continue;

            $momentoCierre = $fin->copy()->addMinutes($gracia);
            if (now()->lt($momentoCierre)) continue;

            if ($dry) {
                $this->line("DRY: cerraría #{$a->id} a {$momentoCierre->toDateTimeString()} ({$a->tipo_asistencia})");
                $cerradas++;
                continue;
            }

            $a->update([
                'hora_salida' => $momentoCierre,
                'origen'      => 'automatico',
                'observacion' => trim(($a->observacion ? $a->observacion.' | ' : '') . 'Autocierre por fin programado'),
            ]);

            Log::info("Autocierre asistencia {$a->id} ({$a->tipo_asistencia}) a {$momentoCierre}");
            $cerradas++;
        }

        $this->info("Procesadas: {$total} | Cerradas: {$cerradas}");
        return SymfonyCommand::SUCCESS;
    }
}
