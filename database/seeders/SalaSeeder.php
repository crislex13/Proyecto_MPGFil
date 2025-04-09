<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('salas')->insert([
            ['nombre' => 'Sala A', 'descripcion' => 'Sala principal para clases grupales', 'estado' => 'activa'],
            ['nombre' => 'Sala B', 'descripcion' => 'Sala secundaria para entrenamientos personalizados', 'estado' => 'activa'],
        ]);
    }
}
