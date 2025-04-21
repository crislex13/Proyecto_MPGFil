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

        $yaRegistrado = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $cliente->id)
            ->where('asistible_type', Clientes::class)
            ->whereTime('hora_entrada', $horaEntrada->format('H:i:s'))
            ->exists();

        if ($yaRegistrado) {
            return;
        }

        [$puedeIngresar, $mensaje] = $cliente->puedeRegistrarAsistenciaHoy();

        if (!$puedeIngresar) {
            return;
        }

        Asistencia::create([
            'asistible_id' => $cliente->id,
            'asistible_type' => Clientes::class,
            'tipo_asistencia' => 'plan',
            'fecha' => $fecha,
            'hora_entrada' => $horaEntrada,
            'estado' => 'puntual',
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