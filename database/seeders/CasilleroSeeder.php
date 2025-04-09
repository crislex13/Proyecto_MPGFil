<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Casillero;
use App\Models\Clientes;

class CasilleroSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Clientes::take(5)->get();

        // Casilleros ocupados
        foreach ($clientes as $index => $cliente) {
            Casillero::create([
                'numero' => 'C-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'ubicacion' => 'Sala A',
                'estado' => 'ocupado',
                'cliente_id' => $cliente->id,
                'fecha_entrega_llave' => now()->subDays(rand(1, 5)),
                'reposicion_llave' => false,
            ]);
        }

        // Casilleros libres
        for ($i = 6; $i <= 10; $i++) {
            Casillero::create([
                'numero' => 'C-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'ubicacion' => 'Sala A',
                'estado' => 'disponible',
            ]);
        }
    }
}