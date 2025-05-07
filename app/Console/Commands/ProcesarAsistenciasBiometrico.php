<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use App\Models\Personal;
use App\Services\AsistenciaService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcesarAsistenciasBiometrico extends Command
{
    protected $signature = 'app:procesar-asistencias-biometrico';
    protected $description = 'Procesa registros desde archivo del biométrico y los guarda como asistencias';

    public function handle()
    {
        $ruta = storage_path('app/registros_biometrico/registros.txt');

        if (!File::exists($ruta)) {
            $this->error('⚠️ El archivo de registros no existe.');
            return;
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $procesados = 0;
        $errores = 0;

        foreach ($lineas as $linea) {
            try {
                // Validamos formato
                if (!str_contains($linea, ';')) {
                    $errores++;
                    continue;
                }

                [$fechaHora, $ci] = explode(';', trim($linea));
                $horaEntrada = Carbon::parse($fechaHora);

                // Buscar cliente o personal
                $cliente = Clientes::where('ci', $ci)->first();
                $personal = Personal::where('ci', $ci)->first();

                if ($personal) {
                    AsistenciaService::registrarComoPersonal($personal, $horaEntrada);
                    $procesados++;
                } elseif ($cliente) {
                    AsistenciaService::registrarComoCliente($cliente, $horaEntrada);
                    $procesados++;
                } else {
                    Log::warning("CI no encontrado en registros biométricos: {$ci}");
                    $errores++;
                }
            } catch (\Throwable $e) {
                Log::error("Error procesando línea: {$linea} → {$e->getMessage()}");
                $errores++;
            }
        }

        // Limpiar archivo al final
        file_put_contents($ruta, '');

        $this->info("✅ Registros procesados correctamente: $procesados");
        if ($errores > 0) {
            $this->warn("⚠️ Registros con error: $errores (ver logs para más detalles)");
        }
    }
}
