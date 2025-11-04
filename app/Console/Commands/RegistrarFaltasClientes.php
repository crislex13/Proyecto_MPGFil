<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use App\Models\Asistencia;
use App\Models\PlanCliente;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RegistrarFaltasClientes extends Command
{
    protected $signature = 'clientes:registrar-faltas
                            {--fecha= : Fecha a evaluar en formato YYYY-MM-DD (por defecto, ayer)}
                            {--dry-run : Simula sin insertar}';

    protected $description = 'Registra faltas injustificadas (tipo plan) para clientes que debían asistir y no lo hicieron.';

    public function handle(): int
    {
        // 1) Fecha objetivo (por defecto, AYER)
        $fecha = $this->option('fecha')
            ? Carbon::createFromFormat('Y-m-d', $this->option('fecha'))->startOfDay()
            : now()->subDay()->startOfDay();

        $fechaStr = $fecha->toDateString();
        $dry = (bool) $this->option('dry-run');

        $this->info("Evaluando faltas para el día: {$fechaStr}" . ($dry ? ' (DRY-RUN)' : ''));

        $totalFaltas = 0;
        $procesados = 0;

        // 2) Recorremos clientes por cursor para escalar mejor
        Clientes::query()
            ->with([
                // Solo planes vigentes el día objetivo
                'planesCliente' => function (Builder $q) use ($fechaStr) {
                    $q->whereDate('fecha_inicio', '<=', $fechaStr)
                        ->whereDate('fecha_final', '>=', $fechaStr)
                        ->whereIn('estado', ['vigente', 'deuda'])
                        ->with('plan');
                },
                // Permisos del día objetivo (si existe relación)
                'permisos' => function ($q) use ($fechaStr) {
                    $q->where('estado', 'aprobado')
                        ->whereDate('fecha', $fechaStr);
                },
            ])
            ->cursor()
            ->each(function (Clientes $cliente) use (&$totalFaltas, &$procesados, $fecha, $fechaStr, $dry) {
                $procesados++;

                // a) Tiene plan vigente ese día
                /** @var PlanCliente|null $planCliente */
                $planCliente = $cliente->planesCliente
                    ->sortByDesc('fecha_final')
                    ->first();

                if (!$planCliente || !$planCliente->plan) {
                    return; // sin plan vigente → no debía asistir
                }

                // b) Si el plan usa días seleccionados, validar día permitido
                $debeAsistirHoy = true;

                // Detecta si tu plan usa la modalidad de días seleccionados
                $usaDiasSeleccionados = in_array('dias_seleccionados', (array) ($planCliente->plan->tipo_asistencia ?? []), true);

                if ($usaDiasSeleccionados) {
                    $dow = (int) $fecha->dayOfWeek; // 0..6 (Dom..Sáb)
    
                    $diasPermitidos = collect($planCliente->dias_permitidos ?? [])
                        ->map(function ($dia) {
                            return match (strtolower($dia)) {
                                'domingo' => 0,
                                'lunes' => 1,
                                'martes' => 2,
                                'miercoles', 'miércoles' => 3,
                                'jueves' => 4,
                                'viernes' => 5,
                                'sabado', 'sábado' => 6,
                                default => null,
                            };
                        })
                        ->filter(fn($v) => $v !== null)
                        ->unique()
                        ->values()
                        ->toArray();

                    if (!in_array($dow, $diasPermitidos, true)) {
                        $debeAsistirHoy = false; // no correspondía asistir
                    }
                }

                if (!$debeAsistirHoy) {
                    return;
                }

                // c) Si tuvo permiso aprobado ese día, no es falta
                $tienePermiso = method_exists($cliente, 'permisos')
                    ? $cliente->permisos->isNotEmpty()
                    : false;

                if ($tienePermiso) {
                    return;
                }

                // d) Si ya asistió ese día (cualquier tipo para el cliente), no es falta
                $asistio = Asistencia::whereDate('fecha', $fechaStr)
                    ->where('asistible_id', $cliente->id)
                    ->where('asistible_type', Clientes::class)
                    ->whereNull('estado', 'acceso_denegado') // opcional: ignora denegados
                    ->exists();

                if ($asistio) {
                    return;
                }

                // e) Idempotencia por cliente+fecha+tipo_asistencia=falta(plan)
                $existsFalta = Asistencia::whereDate('fecha', $fechaStr)
                    ->where('asistible_id', $cliente->id)
                    ->where('asistible_type', Clientes::class)
                    ->where('tipo_asistencia', 'plan')
                    ->where('estado', 'falta')
                    ->exists();

                if ($existsFalta) {
                    return; // ya registrada para este cliente y fecha
                }

                if ($dry) {
                    $this->line("DRY: faltaría cliente #{$cliente->id} ({$cliente->nombre} {$cliente->apellido_paterno}) el {$fechaStr}");
                    $totalFaltas++;
                    return;
                }

                // f) Registrar falta
                Asistencia::create([
                    'asistible_id' => $cliente->id,
                    'asistible_type' => Clientes::class,
                    'tipo_asistencia' => 'plan',
                    'fecha' => $fechaStr,
                    'estado' => 'falta',
                    'origen' => 'automatico',
                    'observacion' => "Falta injustificada el {$fecha->format('d/m/Y')}. No asistió ni tenía permiso.",
                    'usuario_registro_id' => null,
                ]);

                $totalFaltas++;
            });

        $this->info("✔ Procesados: {$procesados} | Faltas registradas: {$totalFaltas}");
        return self::SUCCESS;
    }
}
