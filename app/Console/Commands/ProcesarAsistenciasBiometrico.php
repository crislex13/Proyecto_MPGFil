<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use App\Models\Personal;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\AsistenciaService;

class ProcesarAsistenciasBiometrico extends Command
{
    protected $signature = 'app:procesar-asistencias-biometrico';
    protected $description = 'Procesa registros desde archivo del biométrico y los guarda como asistencias';

    public function handle()
    {
        $ruta = storage_path('app/registros_biometrico/registros.txt');

        if (!file_exists($ruta)) {
            $this->error('El archivo de registros no existe.');
            return;
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $procesados = 0;

        foreach ($lineas as $linea) {
            [$fechaHora, $ci] = explode(';', $linea);
        
            $horaEntrada = Carbon::parse($fechaHora);
            $cliente = Clientes::where('ci', $ci)->first();
            $personal = Personal::where('ci', $ci)->first();
        
            if ($personal) {
                AsistenciaService::registrarComoPersonal($personal, $horaEntrada);
                $procesados++;
            } elseif ($cliente) {
                AsistenciaService::registrarComoCliente($cliente, $horaEntrada);
                $procesados++;
            }
        }

        $this->info("Registros procesados: $procesados");

        file_put_contents($ruta, '');
    }
}