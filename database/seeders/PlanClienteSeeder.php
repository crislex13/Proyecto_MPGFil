<?php

namespace Database\Seeders;

use App\Models\Clientes;
use App\Models\Plan;
use App\Models\Disciplina;
use App\Models\PlanCliente;
use App\Models\PlanDisciplina;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PlanClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Clientes::all();
        $plan = Plan::first();
        $disciplina = Disciplina::first();

        if (!$plan || !$disciplina) {
            echo "⛔ Error: faltan planes o disciplinas.\n";
            return;
        }

        $planDisciplina = PlanDisciplina::where('plan_id', $plan->id)
            ->where('disciplina_id', $disciplina->id)
            ->first();

        if (!$planDisciplina) {
            echo "⛔ Error: no hay precio registrado en plan_disciplinas.\n";
            return;
        }

        foreach ($clientes as $cliente) {
            $fechaInicio = Carbon::now()->subDays(rand(0, 10));
            $fechaFinal = (clone $fechaInicio)->addDays($plan->duracion_dias - 1);

            PlanCliente::create([
                'cliente_id' => $cliente->id,
                'plan_id' => $plan->id,
                'disciplina_id' => $disciplina->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'precio_plan' => $planDisciplina->precio,
                'a_cuenta' => 100,
                'saldo' => max(0, $planDisciplina->precio - 100),
                'total' => $planDisciplina->precio,
                'casillero_monto' => 10,
                'metodo_pago' => 'efectivo',
                'comprobante' => 'simple',
            ]);
        }
    }
}