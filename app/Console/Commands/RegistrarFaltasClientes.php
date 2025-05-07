<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use App\Models\Asistencia;
use App\Models\PlanCliente;
use Illuminate\Support\Carbon;

class RegistrarFaltasClientes extends Command
{
    protected $signature = 'clientes:registrar-faltas';

    protected $description = 'Registra faltas injustificadas de clientes que no asistieron en días permitidos';

    public function handle()
    {
        $hoy = now()->subDay(); // Siempre verificamos el DÍA ANTERIOR
        if (
            Asistencia::whereDate('fecha', $hoy)
                ->where('estado', 'falta')
                ->where('tipo_asistencia', 'plan')
                ->exists()
        ) {
            $this->warn("⚠️ Ya se registraron faltas de clientes para el día {$hoy->toDateString()}.");
            return;
        }
        $clientes = Clientes::with([
            'planesCliente' => function ($q) use ($hoy) {
                $q->whereDate('fecha_inicio', '<=', $hoy)
                    ->whereDate('fecha_final', '>=', $hoy)
                    ->whereIn('estado', ['vigente', 'deuda'])
                    ->with('plan');
            }
        ])->get();

        $totalFaltas = 0;

        foreach ($clientes as $cliente) {
            $planCliente = $cliente->planesCliente->first();
            if (!$planCliente || !$planCliente->plan)
                continue;

            $plan = $planCliente->plan;

            // Validar si el día anterior está permitido según tipo de asistencia
            if (in_array('dias_seleccionados', (array) $plan->tipo_asistencia)) {
                $diaSemana = $hoy->dayOfWeek;

                $diasPermitidos = collect($planCliente->dias_permitidos ?? [])->map(fn($dia) => match ($dia) {
                    'domingo' => 0,
                    'lunes' => 1,
                    'martes' => 2,
                    'miercoles' => 3,
                    'jueves' => 4,
                    'viernes' => 5,
                    'sabado' => 6,
                    default => null,
                })->filter()->unique()->toArray();

                if (!in_array($diaSemana, $diasPermitidos))
                    continue; // No debía asistir
            }

            // Validar que no haya tenido permiso
            $tienePermiso = $cliente->permisos()
                ->where('estado', 'aprobado')
                ->whereDate('fecha', $hoy->toDateString())
                ->exists();

            if ($tienePermiso)
                continue;

            // Validar que no haya asistido ese día
            $asistio = Asistencia::whereDate('fecha', $hoy)
                ->where('asistible_id', $cliente->id)
                ->where('asistible_type', Clientes::class)
                ->exists();

            if (!$asistio) {
                Asistencia::create([
                    'asistible_id' => $cliente->id,
                    'asistible_type' => Clientes::class,
                    'tipo_asistencia' => 'plan',
                    'fecha' => $hoy->toDateString(),
                    'estado' => 'falta',
                    'origen' => 'automatico',
                    'observacion' => "Falta injustificada el {$hoy->format('d/m/Y')}. No asistió ni tenía permiso.",
                    'usuario_registro_id' => null,
                ]);

                $totalFaltas++;
            }
        }

        $this->info("✔ Se registraron $totalFaltas faltas injustificadas.");
    }
}