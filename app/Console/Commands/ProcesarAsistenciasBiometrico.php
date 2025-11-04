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
        $origenPath = $this->option('path') ?: storage_path('app/registros_biometrico/registros.txt');

        if (!File::exists($origenPath)) {
            $this->error('⚠️ El archivo de registros no existe: ' . $origenPath);
            return self::FAILURE;
        }

        // 1) Abre con lock exclusivo para "rotar" el archivo y dejarlo vacío de forma atómica.
        $fp = fopen($origenPath, 'c+');
        if (!$fp) {
            $this->error('⚠️ No se pudo abrir el archivo: ' . $origenPath);
            return self::FAILURE;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            $this->warn('⚠️ No se pudo obtener lock del archivo. Intenta de nuevo.');
            return self::FAILURE;
        }

        // 2) Genera backup destino (si está vacío, igual crea uno por trazabilidad)
        $backupDir = dirname($origenPath);
        $backupPath = $backupDir . '/procesados_' . now()->format('Ymd_His') . '.log';

        try {
            // Asegura puntero al inicio ANTES de copiar
            rewind($fp);

            // Copia el contenido del archivo origen al backup (sin cargar todo a memoria)
            $dest = fopen($backupPath, 'w');
            if (!$dest) {
                $this->error('⚠️ No se pudo crear backup: ' . $backupPath);
                return self::FAILURE;
            }
            stream_copy_to_stream($fp, $dest);
            fflush($dest);
            fclose($dest);

            // 3) Limpia el archivo original (quedará listo para próximas marcas)
            rewind($fp);
            ftruncate($fp, 0);
            fflush($fp);
        } finally {
            // Libera lock del origen
            try {
                flock($fp, LOCK_UN);
            } catch (\Throwable $e) {
            }
            try {
                fclose($fp);
            } catch (\Throwable $e) {
            }
        }

        // 4) Procesa el BACKUP línea por línea (ya sin lock).
        $procesados = 0;
        $errores = 0;
        $hashVistos = [];

        // Si el backup quedó vacío, no hay nada que procesar
        if (filesize($backupPath) === 0) {
            $this->info('🟢 No había registros nuevos. Nada que procesar.');
            return self::SUCCESS;
        }

        $fh = fopen($backupPath, 'r');
        if (!$fh) {
            $this->error('⚠️ No se pudo abrir el backup para lectura: ' . $backupPath);
            return self::FAILURE;
        }

        $lineaN = 0;
        while (($linea = fgets($fh)) !== false) {
            $lineaN++;

            // Normaliza encoding y BOM
            $linea = $this->normalizeLine($linea);

            if ($linea === '') {
                continue;
            }

            try {
                // Formato esperado: "YYYY-MM-DD HH:MM:SS;CI"
                // Aceptamos espacios alrededor de ';'
                if (substr_count($linea, ';') < 1) {
                    $errores++;
                    Log::warning("Biométrico: línea inválida [{$lineaN}] → {$linea}");
                    continue;
                }

                [$fechaHoraRaw, $ciRaw] = explode(';', $linea, 2);
                $ci = trim($ciRaw);
                $fechaHora = trim($fechaHoraRaw);

                // Idempotencia por corrida
                $hash = md5($fechaHora . '|' . $ci);
                if (isset($hashVistos[$hash])) {
                    continue;
                }
                $hashVistos[$hash] = true;

                // Parseo fecha/hora (timezone de la app)
                $momento = $this->parseDateTime($fechaHora);
                if (!$momento) {
                    $errores++;
                    Log::warning("Biométrico: fecha/hora inválida [{$lineaN}] → {$linea}");
                    continue;
                }

                // Resolver sujeto (prioridad: Personal > Cliente)
                $personal = Personal::where('ci', $ci)->first();
                $cliente = Clientes::where('ci', $ci)->first();

                // Modo simulación
                if ($this->option('dry-run')) {
                    if ($personal) {
                        $this->line("DRY-RUN: Personal {$personal->id} {$personal->nombre} {$momento->toDateTimeString()}");
                    } elseif ($cliente) {
                        $this->line("DRY-RUN: Cliente {$cliente->id} {$cliente->nombre} {$momento->toDateTimeString()}");
                    } else {
                        $this->line("DRY-RUN: CI no encontrado: {$ci}");
                    }
                    $procesados++;
                    continue;
                }

                if ($personal) {
                    [$ok, $msg] = AsistenciaService::togglePersonal($personal, $momento, 'biometrico');
                    if (!$ok)
                        Log::info("Biométrico: aviso personal CI {$ci} → {$msg}");
                    $procesados++;
                    continue;
                }

                if ($cliente) {
                    [$ok, $msg] = AsistenciaService::toggleCliente($cliente, $momento, 'biometrico');
                    if (!$ok)
                        Log::info("Biométrico: aviso cliente CI {$ci} → {$msg}");
                    $procesados++;
                    continue;
                }

                $errores++;
                Log::warning("Biométrico: CI no encontrado [{$lineaN}] → {$ci}");
            } catch (\Throwable $e) {
                $errores++;
                Log::error("Biométrico: error procesando línea [{$lineaN}] → {$linea} :: {$e->getMessage()}");
            }
        }

        fclose($fh);

        $this->info("✅ Procesados: {$procesados}");
        if ($errores > 0) {
            $this->warn("⚠️ Con errores: {$errores} (revisa storage/logs/laravel.log)");
        }

        return self::SUCCESS;
    }

    /**
     * Normaliza una línea: quita BOM, recorta espacios, asegura UTF-8 y regresa cadena limpia.
     */
    private function normalizeLine(string $line): string
    {
        // Elimina BOM si existe
        if (strpos($line, "\xEF\xBB\xBF") === 0) {
            $line = substr($line, 3);
        }

        // Intenta forzar a UTF-8 si viniera en ANSI/Latin
        if (!mb_check_encoding($line, 'UTF-8')) {
            $line = mb_convert_encoding($line, 'UTF-8', 'auto');
        }

        // Recorta y normaliza separadores
        $line = trim($line);

        return $line;
    }

    /**
     * Parsea un datetime robusto. Espera "YYYY-MM-DD HH:MM:SS" pero tolera "YYYY/MM/DD HH:MM:SS".
     * Retorna Carbon o null si es inválido.
     */
    private function parseDateTime(string $raw): ?Carbon
    {
        $raw = trim($raw);

        // Reemplaza "/" por "-" si viniera así
        $candidate = str_replace('/', '-', $raw);

        try {
            // Intenta parseo flexible
            return Carbon::parse($candidate);
        } catch (\Throwable $e) {
            // Intento estricto "Y-m-d H:i:s"
            try {
                $dt = Carbon::createFromFormat('Y-m-d H:i:s', $candidate);
                return $dt ?: null;
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }
}
