<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlanCliente;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class BloquearPlanesPorDeuda extends Command
{
    protected $signature = 'planes:bloquear-por-deuda
        {--dry-run : Simula sin guardar}
        {--dias= : Días límite (por defecto config)}
        {--habiles : Usa días hábiles (sin sábados/ domingos)}
        {--desde= : Fecha de corte YYYY-MM-DD (opcional)}
    ';

    protected $description = 'Bloquea planes con deuda vencida (días hábiles u ordinarios).';

    public function handle(): int
    {
        $diasCfg = (int) (config('maxpower.bloqueo_deuda.dias') ?? 5);
        $habilesCfg = (bool) (config('maxpower.bloqueo_deuda.habiles') ?? true);

        $dry = (bool) $this->option('dry-run');
        $dias = $this->option('dias') ? (int) $this->option('dias') : $diasCfg;
        $habiles = $this->option('habiles') ? true : $habilesCfg;

        $hoy = $this->option('desde')
            ? Carbon::parse($this->option('desde'))->startOfDay()
            : Carbon::today();

        $calcLimite = function (Carbon $inicio) use ($dias, $habiles): Carbon {
            if (!$habiles)
                return $inicio->copy()->addDays($dias);
            $f = $inicio->copy();
            $rest = $dias;
            while ($rest > 0) {
                $f = $f->addDay();
                if (!$f->isWeekend())
                    $rest--;
            }
            return $f;
        };

        $procesados = $bloqueados = $simulados = $errores = 0;

        // cota aproximada para no traer todo
        $cota = $habiles ? $hoy->copy()->subDays($dias + 2) : $hoy->copy()->subDays($dias);

        PlanCliente::query()
            ->where('estado', 'vigente')
            ->where('saldo', '>', 0)
            ->whereDate('fecha_inicio', '<=', $cota)
            ->orderBy('id')
            ->chunkById(500, function ($planes) use (&$procesados, &$bloqueados, &$simulados, &$errores, $hoy, $calcLimite, $dry) {
                foreach ($planes as $plan) {
                    $procesados++;
                    try {
                        if (!$plan->fecha_inicio)
                            continue;
                        $inicio = $plan->fecha_inicio instanceof \Carbon\CarbonInterface
                            ? $plan->fecha_inicio
                            : Carbon::parse($plan->fecha_inicio);
                        $limite = $calcLimite($inicio);
                        if ($hoy->lt($limite))
                            continue;

                        if ($dry) {
                            $simulados++;
                            $this->line("DRY: bloquearía plan #{$plan->id} (saldo {$plan->saldo}) — límite {$limite->toDateString()}");
                            continue;
                        }

                        $updated = PlanCliente::where('id', $plan->id)
                            ->where('estado', 'vigente')
                            ->where('saldo', '>', 0)
                            ->update([
                                'estado' => 'bloqueado',
                                'updated_at' => now(),
                                'observacion' => self::mergeObs($plan->observacion, 'Bloqueado por deuda (auto)'),
                            ]);

                        if ($updated) {
                            $bloqueados++;
                            Log::info("PlanCliente {$plan->id} bloqueado por deuda (saldo={$plan->saldo})");
                        }
                    } catch (\Throwable $e) {
                        $errores++;
                        Log::warning("Error bloqueando plan {$plan->id}: {$e->getMessage()}");
                    }
                }
            });

        $msg = "Procesados: {$procesados} | Bloqueados: {$bloqueados}";
        if ($dry)
            $msg .= " | Simulados: {$simulados}";
        if ($errores)
            $msg .= " | Errores: {$errores}";
        $this->info($msg);

        return SymfonyCommand::SUCCESS;
    }

    private static function mergeObs(?string $obs, string $nuevo): string
    {
        $obs = trim((string) $obs);
        return $obs === '' ? $nuevo : ($obs . ' | ' . $nuevo);
    }
}
