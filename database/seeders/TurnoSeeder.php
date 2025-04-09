<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TurnoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('turnos')->insert([
            ['nombre' => 'Mañana', 'hora_inicio' => '06:00:00', 'hora_fin' => '12:00:00'],
            ['nombre' => 'Tarde', 'hora_inicio' => '12:00:00', 'hora_fin' => '18:00:00'],
            ['nombre' => 'Noche', 'hora_inicio' => '18:00:00', 'hora_fin' => '22:00:00'],
        ]);
    }
}
