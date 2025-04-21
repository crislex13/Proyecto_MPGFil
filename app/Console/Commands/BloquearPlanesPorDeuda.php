<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlanCliente;
use Illuminate\Support\Carbon;

class BloquearPlanesPorDeuda extends Command
{
    protected $signature = 'bloquear:planes-deuda';
    protected $description = 'Bloquea automáticamente los planes con deuda después de 5 días desde su inicio';

    public function handle()
    {
        $planes = PlanCliente::where('estado', 'vigente')
            ->where('saldo', '>', 0)
            ->get();

        $hoy = Carbon::today();

        foreach ($planes as $plan) {
            $fechaInicio = Carbon::parse($plan->fecha_inicio);
            $fechaLimite = $fechaInicio->copy()->addDays(5);

            if ($hoy->greaterThanOrEqualTo($fechaLimite)) {
                $plan->estado = 'bloqueado';
                $plan->save();

                $this->info("Plan ID {$plan->id} bloqueado por deuda.");
            }
        }

        $this->info('Proceso de bloqueo por deuda completado.');
    }
}