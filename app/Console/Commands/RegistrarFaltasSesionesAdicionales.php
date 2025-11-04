<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SesionAdicional;
use App\Models\Asistencia;
use App\Models\Clientes;
use Carbon\Carbon;

class RegistrarFaltasSesionesAdicionales extends Command
{
    protected $signature = 'sesiones:registrar-faltas
                            {--fecha= : Fecha a evaluar (YYYY-MM-DD). Por defecto: ayer}
                            {--dry-run : Simula sin insertar}';

    protected $description = 'Registra faltas en sesiones adicionales no asistidas por los clientes.';

    public function handle(): int
    {
        // 1) Fecha objetivo (por defecto AYER)
        $fecha = $this->option('fecha')
            ? Carbon::createFromFormat('Y-m-d', $this->option('fecha'))->startOfDay()
            : now()->subDay()->startOfDay();

        $fechaStr = $fecha->toDateString();
        $dry = (bool) $this->option('dry-run');

        $this->info("Evaluando faltas de sesiones adicionales para: {$fechaStr}" . ($dry ? ' (DRY-RUN)' : ''));

        // 2) Traer sesiones programadas para esa fecha
        $sesiones = SesionAdicional::query()
            ->whereDate('fecha', $fechaStr)
            ->get();

        $procesadas = 0;
        $faltas = 0;

        foreach ($sesiones as $s) {
            $procesadas++;

            // 3) ¿Existe asistencia registrada para esa sesión y cliente?
            $existeAsistencia = Asistencia::query()
                ->where('sesion_adicional_id', $s->id)
                ->where('asistible_type', Clientes::class)
                ->where('asistible_id', $s->cliente_id)
                ->whereDate('fecha', $fechaStr)
                ->exists();

            if ($existeAsistencia) {
                // Ya marcó (puntual/atrasado/salida/permiso/etc.)
                continue;
            }

            // 4) Idempotencia: ¿ya hay una FALTA cargada para esa sesión/cliente/fecha?
            $existeFalta = Asistencia::query()
                ->where('sesion_adicional_id', $s->id)
                ->where('asistible_type', Clientes::class)
                ->where('asistible_id', $s->cliente_id)
                ->where('tipo_asistencia', 'sesion')
                ->where('estado', 'falta')
                ->whereDate('fecha', $fechaStr)
                ->exists();

            if ($existeFalta) {
                // Falta ya registrada previamente
                continue;
            }

            if ($dry) {
                $this->warn("DRY: Registrar falta a cliente #{$s->cliente_id} por sesión #{$s->id} ({$fechaStr}).");
                $faltas++;
                continue;
            }

            // 5) Registrar la falta
            Asistencia::create([
                'asistible_id' => $s->cliente_id,
                'asistible_type' => Clientes::class,
                'tipo_asistencia' => 'sesion',
                'sesion_adicional_id' => $s->id,
                'fecha' => $fechaStr,
                'estado' => 'falta',
                'origen' => 'automatico',
                'observacion' => "Falta a sesión adicional no justificada el {$fecha->format('d/m/Y')}.",
                'usuario_registro_id' => null,
            ]);

            $faltas++;
        }

        $this->info("✅ Sesiones evaluadas: {$sesiones->count()} | Faltas registradas: {$faltas}");
        return self::SUCCESS;
    }
}
