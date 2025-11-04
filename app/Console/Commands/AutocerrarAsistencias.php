<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asistencia;
use App\Services\AsistenciaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class AutocerrarAsistencias extends Command
{
    protected $signature = 'asistencias:auto-cerrar 
                            {--dry-run : Simula sin guardar}
                            {--tipo= : Filtra por tipo_asistencia (plan|sesion|personal)}
                            {--max-minutos= : No cerrar si hora_entrada es muy antigua (seguridad)}';

    protected $description = 'Autocierra asistencias abiertas cuyo fin programado ya pasó';

    public function handle(): int
    {
        $habilitado = config('maxpower.autocierre.habilitado', true);
        if (!$habilitado) {
            $this->info('Autocierre deshabilitado.');
            return SymfonyCommand::SUCCESS;
        }

        $graciaMin = (int) (config('maxpower.autocierre.gracia_min') ?? 5);
        $duracionDef = (int) (config('maxpower.plan_duracion_defecto_min') ?? 90);
        $dry = (bool) $this->option('dry-run');
        $tipoFiltro = $this->option('tipo');
        $maxMinutos = $this->option('max-minutos') ? (int) $this->option('max-minutos') : null;

        $ahora = CarbonImmutable::now(); // usa timezone de app

        $q = Asistencia::query()
            ->whereNull('hora_salida')
            ->when($tipoFiltro, fn($qq) => $qq->where('tipo_asistencia', $tipoFiltro))
            ->latest('hora_entrada');

        if ($maxMinutos !== null) {
            $q->where('hora_entrada', '>=', $ahora->subMinutes($maxMinutos));
        }

        $total = 0;
        $cerradas = 0;
        $simuladas = 0;
        $errores = 0;

        // Stream-friendly
        foreach ($q->cursor() as $a) {
            $total++;

            try {
                // Determinar fin programado
                $fin = AsistenciaService::finProgramadoPara($a);

                if (!$fin && $a->tipo_asistencia === 'plan') {
                    // Fallback por duración
                    $entrada = $a->hora_entrada instanceof \Carbon\CarbonInterface ? $a->hora_entrada : CarbonImmutable::parse($a->hora_entrada);
                    $fin = $entrada->copy()->addMinutes($duracionDef);
                }

                if (!$fin) {
                    continue; // nada que cerrar
                }

                $momentoCierre = (clone $fin)->addMinutes($graciaMin);
                if ($ahora->lt($momentoCierre)) {
                    continue; // aún no toca
                }

                if ($dry) {
                    $this->line("DRY: cerraría #{$a->id} a {$momentoCierre->toDateTimeString()} ({$a->tipo_asistencia})");
                    $simuladas++;
                    continue;
                }

                // Cierre atómico: solo si sigue sin hora_salida
                $actualizados = Asistencia::where('id', $a->id)
                    ->whereNull('hora_salida')
                    ->update([
                        'hora_salida' => $momentoCierre,
                        'origen' => $a->origen ?: 'automatico',
                        'observacion' => self::concatObs($a->observacion, 'Autocierre por fin programado'),
                        'updated_at' => now(),
                    ]);

                if ($actualizados > 0) {
                    Log::info("Autocierre asistencia {$a->id} ({$a->tipo_asistencia}) a {$momentoCierre}");
                    $cerradas++;
                } else {
                    // Otro proceso la cerró en el interín
                    $this->line("Saltada #{$a->id}: ya no está abierta.");
                }

            } catch (\Throwable $e) {
                $errores++;
                Log::warning("Error autocerrando asistencia {$a->id}: {$e->getMessage()}");
                // Seguimos con las demás
            }
        }

        $msg = "Procesadas: {$total} | Cerradas: {$cerradas}";
        if ($dry)
            $msg .= " | Simuladas: {$simuladas}";
        if ($errores > 0)
            $msg .= " | Errores: {$errores}";
        $this->info($msg);

        return SymfonyCommand::SUCCESS;
    }

    private static function concatObs(?string $obs, string $nuevo): string
    {
        $obs = trim((string) $obs);
        // Evita duplicar la leyenda si ya existe
        if ($obs !== '' && str_contains($obs, $nuevo)) {
            return $obs;
        }
        return $obs === '' ? $nuevo : "{$obs} | {$nuevo}";
    }
}