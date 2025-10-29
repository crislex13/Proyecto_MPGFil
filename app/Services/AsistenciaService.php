<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsistenciaService
{
    /* =========================================================
     * CLIENTE: TOGGLE (si hay abierta → salida; si no → entrada)
     * ========================================================= */
    public static function toggleCliente(Clientes $cliente, Carbon $marca): void
    {
        DB::transaction(function () use ($cliente, $marca) {
            if (self::registrarSalidaCliente($cliente, $marca)) {
                return; // era salida
            }
            self::registrarComoCliente($cliente, $marca); // era entrada
        });
    }

    /* =========================================================
     * CLIENTE: SALIDA
     * ========================================================= */
    public static function registrarSalidaCliente(Clientes $cliente, Carbon $horaSalida): bool
    {
        $fecha = $horaSalida->toDateString();

        /** @var Asistencia|null $abierta */
        $abierta = Asistencia::abiertaDeClienteEnFecha($cliente, $fecha)
            ->orderByDesc('hora_entrada')
            ->lockForUpdate()
            ->first();

        if (!$abierta) {
            return false; // no hay abierta que cerrar
        }

        // Debounce: evita marcar salida “instantánea” por doble marca accidental
        if ($abierta->hora_entrada && $horaSalida->lt(Carbon::parse($abierta->hora_entrada)->addMinutes(5))) {
            return false;
        }

        $abierta->update(['hora_salida' => $horaSalida]);
        return true;
    }

    /* =========================================================
     * CLIENTE: ENTRADA (tu lógica original, intacta)
     * ========================================================= */
    public static function registrarComoCliente(Clientes $cliente, Carbon $horaEntrada): void
    {
        $fecha = $horaEntrada->toDateString();

        // 1) Buscar sesiones adicionales del cliente para ese día
        $sesiones = $cliente->sesionesAdicionales()
            ->whereDate('fecha', $fecha)
            ->get();

        foreach ($sesiones as $sesion) {
            $horaInicio = Carbon::parse($sesion->hora_inicio)->subMinutes(15); // permite marcar desde 15 min antes
            $horaFin = Carbon::parse($sesion->hora_fin);

            if ($horaEntrada->between($horaInicio, $horaFin)) {
                // Ya registró esta sesión?
                $yaRegistrado = Asistencia::where('sesion_adicional_id', $sesion->id)
                    ->where('asistible_id', $cliente->id)
                    ->where('asistible_type', Clientes::class)
                    ->exists();

                if ($yaRegistrado) return;

                // Puntual o atrasado
                $horaExacta = Carbon::parse($sesion->hora_inicio);
                $estado = $horaEntrada->lte($horaExacta) ? 'puntual' : 'atrasado';

                Asistencia::create([
                    'asistible_id'        => $cliente->id,
                    'asistible_type'      => Clientes::class,
                    'tipo_asistencia'     => 'sesion',
                    'sesion_adicional_id' => $sesion->id,
                    'fecha'               => $fecha,
                    'hora_entrada'        => $horaEntrada,
                    'estado'              => $estado,
                    'origen'              => 'biometrico',
                    'usuario_registro_id' => null,
                ]);

                return;
            }
        }

        // 2) Tiene sesión hoy pero fuera de horario → acceso_denegado (una sola vez)
        if ($sesiones->count()) {
            $yaFallido = Asistencia::where('fecha', $fecha)
                ->where('asistible_id', $cliente->id)
                ->where('asistible_type', Clientes::class)
                ->where('tipo_asistencia', 'sesion')
                ->where('estado', 'acceso_denegado')
                ->exists();

            if (!$yaFallido) {
                Asistencia::create([
                    'asistible_id'        => $cliente->id,
                    'asistible_type'      => Clientes::class,
                    'tipo_asistencia'     => 'sesion',
                    'fecha'               => $fecha,
                    'hora_entrada'        => $horaEntrada,
                    'estado'              => 'acceso_denegado',
                    'origen'              => 'biometrico',
                    'usuario_registro_id' => null,
                    'observacion'         => 'Sesión adicional fuera de horario permitido',
                ]);
            }

            return;
        }

        // 3) Sin sesión: valida plan
        [$puedeIngresar, $mensaje] = $cliente->puedeRegistrarAsistenciaHoy();

        if (!$puedeIngresar) {
            Asistencia::create([
                'asistible_id'        => $cliente->id,
                'asistible_type'      => Clientes::class,
                'tipo_asistencia'     => 'plan',
                'fecha'               => $fecha,
                'hora_entrada'        => $horaEntrada,
                'estado'              => 'acceso_denegado',
                'origen'              => 'biometrico',
                'usuario_registro_id' => null,
                'observacion'         => $mensaje,
            ]);
            return;
        }

        // 4) Entrada por plan
        Asistencia::create([
            'asistible_id'        => $cliente->id,
            'asistible_type'      => Clientes::class,
            'tipo_asistencia'     => 'plan',
            'fecha'               => $fecha,
            'hora_entrada'        => $horaEntrada,
            'estado'              => 'puntual', // por definición en plan
            'origen'              => 'biometrico',
            'usuario_registro_id' => null,
        ]);
    }

    /* =========================================================
     * PERSONAL: TOGGLE + SALIDA (opcional, por simetría)
     * ========================================================= */
    public static function togglePersonal(Personal $personal, Carbon $marca): void
    {
        DB::transaction(function () use ($personal, $marca) {
            if (self::registrarSalidaPersonal($personal, $marca)) {
                return;
            }
            self::registrarComoPersonal($personal, $marca);
        });
    }

    public static function registrarSalidaPersonal(Personal $personal, Carbon $horaSalida): bool
    {
        $fecha = $horaSalida->toDateString();

        $abierta = Asistencia::abiertaDePersonalEnFecha($personal, $fecha)
            ->orderByDesc('hora_entrada')
            ->lockForUpdate()
            ->first();

        if (!$abierta) return false;

        if ($abierta->hora_entrada && $horaSalida->lt(Carbon::parse($abierta->hora_entrada)->addMinutes(5))) {
            return false;
        }

        $abierta->update(['hora_salida' => $horaSalida]);
        return true;
    }

    /* =========================================================
     * PERSONAL: ENTRADA (tu lógica actual)
     * ========================================================= */
    public static function registrarComoPersonal(Personal $personal, Carbon $horaEntrada): void
    {
        $fecha = $horaEntrada->toDateString();

        $turno = $personal->turnoHoy();
        if (!$turno) return;

        $horaInicio = Carbon::createFromFormat('H:i:s', $turno->hora_inicio);
        $horaFin    = Carbon::createFromFormat('H:i:s', $turno->hora_fin);
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
                $asistenciaSinSalida->update(['hora_salida' => $horaEntrada]);
            }
            return;
        }

        $yaIngreso = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $personal->id)
            ->where('asistible_type', Personal::class)
            ->whereBetween('hora_entrada', [$horaInicio->copy()->subHour(), $horaFin])
            ->exists();

        if ($yaIngreso) return;

        $estado = $horaEntrada->greaterThan($horaInicio) ? 'atrasado' : 'puntual';

        Asistencia::create([
            'asistible_id'        => $personal->id,
            'asistible_type'      => Personal::class,
            'tipo_asistencia'     => 'personal',
            'fecha'               => $fecha,
            'hora_entrada'        => $horaEntrada,
            'estado'              => $estado,
            'origen'              => 'biometrico',
            'usuario_registro_id' => null,
        ]);
    }
}