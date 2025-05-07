<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Personal;
use App\Models\Asistencia;
use Carbon\Carbon;

class RegistrarFaltasPersonal extends Command
{
    protected $signature = 'asistencias:registrar-faltas';
    protected $description = 'Registrar faltas de personal que no marcaron asistencia ni tienen permiso';

    public function handle()
    {
        // 📅 Fecha actual (solo día)
        $hoy = Carbon::today();

        // 🛡️ Verificación para evitar duplicación de faltas
        if (
            Asistencia::whereDate('fecha', $hoy)
                ->where('estado', 'falta')
                ->where('tipo_asistencia', 'personal')
                ->exists()
        ) {
            $this->warn("⚠️ Ya se registraron faltas de personal hoy ({$hoy->toDateString()}).");
            return;
        }

        // 📌 Obtener el día de la semana actual (ej: lunes, martes...)
        $diaSemana = $hoy->locale('es')->isoFormat('dddd');

        // 🔍 Buscar personal con turno activo para este día de la semana
        $personales = Personal::whereHas('turnos', function ($query) use ($diaSemana) {
            $query->where('dia', $diaSemana)
                ->where('estado', 'activo');
        })->get();

        $this->info("👥 Se encontraron {$personales->count()} instructores con turno activo para hoy ({$diaSemana}).");

        foreach ($personales as $personal) {
            // ✅ Verificar si tiene un permiso aprobado para hoy
            $tienePermiso = $personal->tienePermisoHoy();

            if ($tienePermiso) {
                $this->line("✔ {$personal->nombre_completo}: Tiene permiso aprobado hoy.");
                continue;
            }

            // ✅ Verificar si ya tiene asistencia registrada
            $tieneAsistencia = Asistencia::whereDate('fecha', $hoy)
                ->where('asistible_id', $personal->id)
                ->where('asistible_type', Personal::class)
                ->exists();

            if ($tieneAsistencia) {
                $this->line("✔ {$personal->nombre_completo}: Ya tiene asistencia registrada.");
                continue;
            }

            // ❌ No tiene permiso ni asistencia → se registra como falta
            Asistencia::create([
                'asistible_id' => $personal->id,
                'asistible_type' => Personal::class,
                'tipo_asistencia' => 'personal',
                'fecha' => $hoy,
                'estado' => 'falta',
                'origen' => 'automatico',
                'observacion' => "Falta injustificada correspondiente al día {$hoy->format('d/m/Y')}. No asistió ni tenía permiso.",
                'usuario_registro_id' => null,
            ]);

            $this->warn("❌ {$personal->nombre_completo}: Falta registrada.");
        }

        $this->info('✅ Proceso de registro de faltas finalizado correctamente.');
    }
}