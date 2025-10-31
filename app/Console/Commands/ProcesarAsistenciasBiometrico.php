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
    protected $signature = 'app:procesar-asistencias-biometrico
                            {--dry-run : Procesa sin grabar asistencias}
                            {--path= : Ruta alternativa del archivo registros.txt}';

    protected $description = 'Procesa registros desde archivo del biométrico y los guarda como asistencias';

    public function handle(): int
    {
        $ruta = $this->option('path') ?: storage_path('app/registros_biometrico/registros.txt');

        if (!File::exists($ruta)) {
            $this->error('⚠️ El archivo de registros no existe: ' . $ruta);
            return self::FAILURE;
        }

        $fp = fopen($ruta, 'c+');
        if (!$fp) {
            $this->error('⚠️ No se pudo abrir el archivo: ' . $ruta);
            return self::FAILURE;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            $this->warn('⚠️ No se pudo obtener lock del archivo. Intenta de nuevo.');
            return self::FAILURE;
        }

        try {
            // Leemos todo el contenido bajo lock
            $contenido  = stream_get_contents($fp);
            $lineas     = preg_split("/\r\n|\n|\r/", $contenido ?? '');
            $procesados = 0;
            $errores    = 0;
            $hashVistos = [];

            foreach ($lineas as $i => $linea) {
                $linea = trim($linea);
                if ($linea === '') continue;

                try {
                    // Formato esperado: "YYYY-MM-DD HH:MM:SS;CI"
                    if (substr_count($linea, ';') < 1) {
                        $errores++;
                        Log::warning("Biométrico: línea inválida [{$i}] → {$linea}");
                        continue;
                    }

                    [$fechaHoraRaw, $ciRaw] = explode(';', $linea, 2);
                    $ci        = trim($ciRaw);
                    $fechaHora = trim($fechaHoraRaw);

                    // Idempotencia por corrida
                    $hash = md5($fechaHora . '|' . $ci);
                    if (isset($hashVistos[$hash])) {
                        continue;
                    }
                    $hashVistos[$hash] = true;

                    // Parseo fecha/hora (timezone de la app)
                    try {
                        $momento = Carbon::parse($fechaHora);
                    } catch (\Throwable $e) {
                        $errores++;
                        Log::warning("Biométrico: fecha/hora inválida [{$i}] → {$linea}");
                        continue;
                    }

                    // Resolver sujeto
                    $personal = Personal::where('ci', $ci)->first();
                    $cliente  = Clientes::where('ci', $ci)->first();

                    // Modo simulación
                    if ($this->option('dry-run')) {
                        if ($personal) {
                            $this->line("DRY-RUN: Personal {$personal->id} {$personal->nombre} {$momento}");
                        } elseif ($cliente) {
                            $this->line("DRY-RUN: Cliente {$cliente->id} {$cliente->nombre} {$momento}");
                        } else {
                            $this->line("DRY-RUN: CI no encontrado: {$ci}");
                        }
                        $procesados++;
                        continue;
                    }

                    // Prioridad: Personal > Cliente
                    if ($personal) {
                        [$ok, $msg] = AsistenciaService::togglePersonal($personal, $momento, 'biometrico');
                        if (!$ok) Log::info("Biométrico: aviso personal CI {$ci} → {$msg}");
                        $procesados++;
                        continue;
                    }

                    if ($cliente) {
                        [$ok, $msg] = AsistenciaService::toggleCliente($cliente, $momento, 'biometrico');
                        if (!$ok) Log::info("Biométrico: aviso cliente CI {$ci} → {$msg}");
                        $procesados++;
                        continue;
                    }

                    $errores++;
                    Log::warning("Biométrico: CI no encontrado [{$i}] → {$ci}");
                } catch (\Throwable $e) {
                    $errores++;
                    Log::error("Biométrico: error procesando línea [{$i}] → {$linea} :: {$e->getMessage()}");
                }
            }

            // Limpiar archivo procesado
            rewind($fp);
            ftruncate($fp, 0);
            fflush($fp);

            // Guardar respaldo
            if (!empty($contenido)) {
                $dirBackup = storage_path('app/registros_biometrico');
                if (!File::exists($dirBackup)) {
                    File::makeDirectory($dirBackup, 0755, true);
                }
                $backup = $dirBackup . '/procesados_' . now()->format('Ymd_His') . '.log';
                File::put($backup, $contenido);
            }

            $this->info("✅ Procesados: {$procesados}");
            if ($errores > 0) {
                $this->warn("⚠️ Con errores: {$errores} (revisa storage/logs/laravel.log)");
            }

            return self::SUCCESS;
        } finally {
            // Siempre liberar lock y cerrar
            try { flock($fp, LOCK_UN); } catch (\Throwable $e) {}
            try { fclose($fp); } catch (\Throwable $e) {}
        }
    }
}
