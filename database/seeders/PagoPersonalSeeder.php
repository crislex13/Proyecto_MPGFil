<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagoPersonalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pagos_personal')->insert([
            ['personal_id' => 1, 'fecha' => now()->toDateString(), 'monto' => 150.00, 'descripcion' => 'Pago por turno de la mañana', 'pagado' => true],
            ['personal_id' => 2, 'fecha' => now()->toDateString(), 'monto' => 200.00, 'descripcion' => 'Pago por clase personalizada', 'pagado' => true],
        ]);
    }
}
