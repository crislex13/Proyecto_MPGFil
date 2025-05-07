<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use Carbon\Carbon;

class AsistenciaService
{
    public static function registrarComoCliente(Clientes $cliente, Carbon $horaEntrada): void
    {
        $fecha = $horaEntrada->toDateString();

        // 1️⃣ Buscar sesiones adicionales del cliente para ese día
        $sesiones = $cliente->sesionesAdicionales()
            ->whereDate('fecha', $fecha)
            ->get();

        foreach ($sesiones as $sesion) {
            $horaInicio = Carbon::parse($sesion->hora_inicio)->subMinutes(15); // permite marcar desde 15 min antes
            $horaFin = Carbon::parse($sesion->hora_fin);

            if ($horaEntrada->between($horaInicio, $horaFin)) {
                // 🔍 Validamos si ya registró asistencia a esta sesión
                $yaRegistrado = Asistencia::where('sesion_adicional_id', $sesion->id)
                    ->where('asistible_id', $cliente->id)
                    ->where('asistible_type', Clientes::class)
                    ->exists();

                if ($yaRegistrado)
                    return;

                // 🟢 Determinar si fue puntual o atrasado
                $horaExacta = Carbon::parse($sesion->hora_inicio);
                $estado = $horaEntrada->lte($horaExacta) ? 'puntual' : 'atrasado';

                // 📝 Registrar asistencia a la sesión
                Asistencia::create([
                    'asistible_id' => $cliente->id,
                    'asistible_type' => Clientes::class,
                    'tipo_asistencia' => 'sesion',
                    'sesion_adicional_id' => $sesion->id,
                    'fecha' => $fecha,
                    'hora_entrada' => $horaEntrada,
                    'estado' => $estado,
                    'origen' => 'biometrico',
                    'usuario_registro_id' => null,
                ]);

                return;
            }
        }

        // 2️⃣ Si el cliente tiene sesión hoy pero está fuera del horario
        if ($sesiones->count()) {
            // Verificar si ya se registró un intento fallido
            $yaFallido = Asistencia::where('fecha', $fecha)
                ->where('asistible_id', $cliente->id)
                ->where('asistible_type', Clientes::class)
                ->where('tipo_asistencia', 'sesion')
                ->where('estado', 'acceso_denegado')
                ->exists();

            if (!$yaFallido) {
                Asistencia::create([
                    'asistible_id' => $cliente->id,
                    'asistible_type' => Clientes::class,
                    'tipo_asistencia' => 'sesion',
                    'fecha' => $fecha,
                    'hora_entrada' => $horaEntrada,
                    'estado' => 'acceso_denegado',
                    'origen' => 'biometrico',
                    'usuario_registro_id' => null,
                    'observacion' => 'Sesión adicional fuera de horario permitido',
                ]);
            }

            return;
        }

        // 3️⃣ Si no tiene sesión, verificar si puede ingresar por plan
        [$puedeIngresar, $mensaje] = $cliente->puedeRegistrarAsistenciaHoy();

        if (!$puedeIngresar) {
            // ❌ Registrar intento fallido con motivo
            Asistencia::create([
                'asistible_id' => $cliente->id,
                'asistible_type' => Clientes::class,
                'tipo_asistencia' => 'plan',
                'fecha' => $fecha,
                'hora_entrada' => $horaEntrada,
                'estado' => 'acceso_denegado',
                'origen' => 'biometrico',
                'usuario_registro_id' => null,
                'observacion' => $mensaje,
            ]);
            return;
        }

        // 4️⃣ Si todo está bien, registrar asistencia normal por plan
        Asistencia::create([
            'asistible_id' => $cliente->id,
            'asistible_type' => Clientes::class,
            'tipo_asistencia' => 'plan',
            'fecha' => $fecha,
            'hora_entrada' => $horaEntrada,
            'estado' => 'puntual', // Siempre puntual en planes, como se definió
            'origen' => 'biometrico',
            'usuario_registro_id' => null,
        ]);
    }

    public static function registrarComoPersonal(Personal $personal, Carbon $horaEntrada): void
    {
        $fecha = $horaEntrada->toDateString();

        $turno = $personal->turnoHoy();

        if (!$turno) {
            return;
        }

        $horaInicio = Carbon::createFromFormat('H:i:s', $turno->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i:s', $turno->hora_fin);
        $inicioPermitido = $horaInicio->copy()->subHour();

        if ($horaEntrada->lessThan($inicioPermitido) || $horaEntrada->greaterThan($horaFin)) {
            return;
        }

        $asistenciaSinSalida = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $personal->id)
            ->where('asistible_type', Personal::class)
            ->whereNull('hora_salida')
            ->first();

        if ($asistenciaSinSalida) {
            $minutos = $asistenciaSinSalida->hora_entrada
                ? Carbon::parse($asistenciaSinSalida->hora_entrada)->diffInMinutes($horaEntrada)
                : 0;

            if ($minutos >= 15) {
                $asistenciaSinSalida->update([
                    'hora_salida' => $horaEntrada,
                ]);
            }

            return;
        }

        $yaIngreso = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $personal->id)
            ->where('asistible_type', Personal::class)
            ->whereBetween('hora_entrada', [$horaInicio->copy()->subHour(), $horaFin])
            ->exists();

        if ($yaIngreso) {
            return;
        }

        $estado = $horaEntrada->greaterThan($horaInicio) ? 'atrasado' : 'puntual';

        Asistencia::create([
            'asistible_id' => $personal->id,
            'asistible_type' => Personal::class,
            'tipo_asistencia' => 'personal',
            'fecha' => $fecha,
            'hora_entrada' => $horaEntrada,
            'estado' => $estado,
            'origen' => 'biometrico',
            'usuario_registro_id' => null,
        ]);
    }
}