<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SesionAdicional;
use App\Models\Asistencia;
use Illuminate\Support\Carbon;

class RegistrarFaltasSesionesAdicionales extends Command
{
    protected $signature = 'sesiones:registrar-faltas';
    protected $description = 'Registra faltas en sesiones adicionales no asistidas por los clientes';

    public function handle()
    {
        $ayer = now()->subDay(); // Carbon instance

        // Prevenir registros duplicados
        if (
            Asistencia::whereDate('fecha', $ayer)
                ->where('estado', 'falta')
                ->where('tipo_asistencia', 'sesion')
                ->exists()
        ) {
            $this->warn("⚠️ Ya se registraron faltas de sesiones adicionales para el día {$ayer->toDateString()}.");
            return;
        }
        $faltas = 0;

        $sesiones = SesionAdicional::whereDate('fecha', $ayer)->get();

        foreach ($sesiones as $sesion) {
            $yaAsistio = Asistencia::where('sesion_adicional_id', $sesion->id)
                ->where('asistible_type', \App\Models\Clientes::class)
                ->exists();

            if (!$yaAsistio) {
                Asistencia::create([
                    'asistible_id' => $sesion->cliente_id,
                    'asistible_type' => \App\Models\Clientes::class,
                    'tipo_asistencia' => 'sesion',
                    'fecha' => $ayer,
                    'estado' => 'falta',
                    'origen' => 'automatico',
                    'observacion' => 'Falta a sesión adicional no justificada.',
                    'sesion_adicional_id' => $sesion->id,
                    'usuario_registro_id' => null,
                ]);
                $faltas++;
            }
        }

        $this->info("✔ Se registraron $faltas faltas en sesiones adicionales.");
    }
}
