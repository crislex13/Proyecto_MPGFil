<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use App\Models\PlanCliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class AsistenciaService
{
    /* ======================
     *  Helpers generales
     * ======================*/

    protected static function abiertaDeCliente(Clientes $c): ?Asistencia
    {
        return Asistencia::where('asistible_id', $c->id)
            ->where('asistible_type', Clientes::class)
            ->whereNull('hora_salida')
            ->latest('hora_entrada')
            ->first();
    }

    protected static function abiertaDePersonal(Personal $p): ?Asistencia
    {
        return Asistencia::where('asistible_id', $p->id)
            ->where('asistible_type', Personal::class)
            ->whereNull('hora_salida')
            ->latest('hora_entrada')
            ->first();
    }

    protected static function registrarDenegadoUnaVez(
        string $tipo,           // 'plan' | 'sesion' | 'personal'
        int $asistibleId,
        string $asistibleType,
        string $fecha,
        Carbon $momento,
        string $origen,
        ?string $observacion = null
    ): void {
        $ya = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $asistibleId)
            ->where('asistible_type', $asistibleType)
            ->where('tipo_asistencia', $tipo)
            ->where('estado', 'acceso_denegado')
            ->exists();

        if (!$ya) {
            Asistencia::create([
                'asistible_id'        => $asistibleId,
                'asistible_type'      => $asistibleType,
                'tipo_asistencia'     => $tipo,
                'fecha'               => $fecha,
                'hora_entrada'        => $momento,
                'estado'              => 'acceso_denegado',
                'origen'              => $origen,
                'usuario_registro_id' => auth()->id(),
                'observacion'         => $observacion,
            ]);
        }
    }

    /* =========================
     *  Ventanas de PLAN (cliente)
     * =========================*/

    public static function planVigenteHoy(?Clientes $cliente, Carbon $momento): ?PlanCliente
    {
        if (!$cliente) return null;

        if (method_exists($cliente, 'planActivoDelDia')) {
            return $cliente->planActivoDelDia(); // ya con ->plan()
        }

        return $cliente->planesCliente()
            ->whereDate('fecha_inicio', '<=', $momento->toDateString())
            ->whereDate('fecha_final', '>=', $momento->toDateString())
            ->latest('fecha_final')
            ->with('plan')
            ->first();
    }

    /** Días permitidos como ints 1..7 (Lun..Dom) */
    public static function diasPermitidosPlan(PlanCliente $pc): array
    {
        $src = $pc->dias_permitidos ?? [];
        $map = ['lunes'=>1, 'martes'=>2, 'miercoles'=>3, 'jueves'=>4, 'viernes'=>5, 'sabado'=>6, 'domingo'=>7];
        $out = [];
        foreach ((array) $src as $d) {
            $out[] = is_numeric($d) ? (int)$d : ($map[strtolower($d)] ?? null);
        }
        return array_values(array_unique(array_filter($out)));
    }

    private static function toTimeCarbon($v): ?Carbon
    {
        if (!$v) return null;
        if ($v instanceof \DateTimeInterface) return Carbon::createFromTimeString($v->format('H:i:s'));
        return Carbon::createFromTimeString((string)$v);
    }

    public static function ventanasPlanParaDia(Clientes $cliente, Carbon $momento): array
    {
        $pc = self::planVigenteHoy($cliente, $momento);
        if (!$pc) return [];

        // Día permitido
        $dow = (int)$momento->dayOfWeekIso; // 1..7
        $permitidos = self::diasPermitidosPlan($pc);
        if (!in_array($dow, $permitidos, true)) {
            return [];
        }

        $ventanas = [];

        // a) Horario global del Plan (si restringe)
        if (optional($pc->plan)->tieneRestriccionHoraria()) {
            $hi = self::toTimeCarbon(data_get($pc->plan, 'hora_inicio'));
            $hf = self::toTimeCarbon(data_get($pc->plan, 'hora_fin'));
            if ($hi && $hf) {
                $ventanas[] = ['inicio'=>$hi, 'fin'=>$hf, 'origen'=>'plan'];
            }
        }

        // b) Horarios específicos (si existen relaciones)
        $colecciones = [];
        if (method_exists($pc, 'horarios')) {
            $pc->loadMissing('horarios');
            $colecciones[] = $pc->getRelation('horarios') ?? collect();
        }
        if ($pc->plan && method_exists($pc->plan, 'horarios')) {
            $pc->plan->loadMissing('horarios');
            $colecciones[] = $pc->plan->getRelation('horarios') ?? collect();
        }

        foreach ($colecciones as $col) {
            foreach ($col as $h) {
                if ((int)data_get($h, 'dia_semana') === $dow) {
                    $hi = self::toTimeCarbon(data_get($h, 'hora_inicio'));
                    $hf = self::toTimeCarbon(data_get($h, 'hora_fin'));
                    if ($hi && $hf) {
                        $ventanas[] = ['inicio'=>$hi, 'fin'=>$hf, 'origen'=>'horarios'];
                    }
                }
            }
        }

        return $ventanas;
    }

    /* ==============================
     *  Ventanas de SESIONES (cliente)
     * ==============================*/

    public static function ventanasSesionesDeHoy(Clientes $cliente, Carbon $momento): array
    {
        $fecha = $momento->toDateString();
        $sesiones = $cliente->sesionesAdicionales()
            ->whereDate('fecha', $fecha)
            ->get();

        $out = [];
        foreach ($sesiones as $s) {
            $hi = data_get($s, 'hora_inicio');
            $hf = data_get($s, 'hora_fin');
            if ($hi && $hf) {
                $out[] = [
                    'inicio' => Carbon::parse($hi),
                    'fin'    => Carbon::parse($hf),
                    'origen' => 'sesion',
                    'sesion' => $s,
                ];
            }
        }
        return $out;
    }

    public static function contieneMomento(array $v, Carbon $m, int $tolAntes = 0, int $tolDespues = 0): bool
    {
        $ini = $v['inicio']->copy()->subMinutes($tolAntes);
        $fin = $v['fin']->copy()->addMinutes($tolDespues);
        return $m->between($ini, $fin);
    }

    /* ===========================
     *  TOGGLE CLIENTE (entrada/salida)
     * ===========================*/

    /**
     * Lógica unificada solicitada:
     * - Salida si hay asistencia abierta (y pasó min_salida_min)
     * - Sesión (puntual/atrasado), única por sesión
     * - Si hay sesión hoy pero fuera de horario => acceso_denegado una vez
     * - Si no hay sesión válida: usa puedeRegistrarAsistenciaHoy()
     *   * si false => acceso_denegado con motivo
     *   * si true  => registra plan (siempre 'puntual'), evita duplicado en el día
     */
    public static function toggleCliente(Clientes $cliente, Carbon $m, string $origen = 'biometrico'): array
    {
        $fecha          = $m->toDateString();
        $debounceMin    = (int) (Config::get('maxpower.asistencia_debounce_min', 5));   // anti-rebote general
        $minSalidaMin   = (int) (Config::get('maxpower.cliente_min_salida_min', 15));   // mínimo para cerrar asistencia

        $tolSesA        = (int) (Config::get('maxpower.sesion_tolerancia_antes', 15));  // como tu v1
        $tolSesD        = (int) (Config::get('maxpower.sesion_tolerancia_despues', 0));
        $tolPlanA       = (int) (Config::get('maxpower.plan_tolerancia_antes', 0));     // planes sin tolerancia por defecto
        $tolPlanD       = (int) (Config::get('maxpower.plan_tolerancia_despues', 0));

        // 0) Salida si hay abierta (con anti-rebote y min_salida)
        if ($abierta = self::abiertaDeCliente($cliente)) {
            $mins = Carbon::parse($abierta->hora_entrada)->diffInMinutes($m);
            if ($mins < $debounceMin) {
                return [false, "Marca ignorada (anti-rebote {$debounceMin} min)."];
            }
            if ($mins < $minSalidaMin) {
                return [false, "Aún no. Mínimo {$minSalidaMin} min para marcar salida."];
            }
            $abierta->update(['hora_salida' => $m]);
            return [true, 'Salida registrada.'];
        }

        // 1) Sesiones de hoy: si cae en ventana => registrar, única por sesión
        $sesionesHoy = self::ventanasSesionesDeHoy($cliente, $m);
        foreach ($sesionesHoy as $v) {
            if (self::contieneMomento($v, $m, $tolSesA, $tolSesD)) {
                $sesion = $v['sesion'];

                $ya = Asistencia::where('sesion_adicional_id', $sesion->id)
                    ->where('asistible_id', $cliente->id)
                    ->where('asistible_type', Clientes::class)
                    ->exists();
                if ($ya) {
                    return [false, 'Sesión ya registrada.'];
                }

                $estado = $m->lte($v['inicio']) ? 'puntual' : 'atrasado';

                Asistencia::create([
                    'asistible_id'        => $cliente->id,
                    'asistible_type'      => Clientes::class,
                    'tipo_asistencia'     => 'sesion',
                    'sesion_adicional_id' => $sesion->id,
                    'fecha'               => $fecha,
                    'hora_entrada'        => $m,
                    'estado'              => $estado,
                    'origen'              => $origen,
                    'usuario_registro_id' => auth()->id(),
                ]);

                return [true, 'Entrada a sesión registrada.'];
            }
        }

        // 2) Si hay sesión hoy pero fuera de horario => denegado (una vez)
        if (!empty($sesionesHoy)) {
            self::registrarDenegadoUnaVez(
                'sesion',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Sesión adicional fuera de horario permitido'
            );
            return [false, 'Sesión fuera de horario.'];
        }

        // 3) Sin sesión válida: validar plan via puedeRegistrarAsistenciaHoy()
        [$puede, $mensaje] = $cliente->puedeRegistrarAsistenciaHoy();

        if (!$puede) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                $mensaje ?: 'No autorizado por plan'
            );
            return [false, $mensaje ?: 'Acceso denegado (plan).'];
        }

        // 3.a) Validar ventanas del plan (día/horario) como en v2
        $ventanasPlan = self::ventanasPlanParaDia($cliente, $m);
        if (empty($ventanasPlan)) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Plan sin horario hoy o día no permitido'
            );
            return [false, 'Plan sin horario hoy o día no permitido.'];
        }
        $ok = null;
        foreach ($ventanasPlan as $v) {
            if (self::contieneMomento($v, $m, $tolPlanA, $tolPlanD)) { $ok = $v; break; }
        }
        if (!$ok) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Fuera de ventana del plan'
            );
            return [false, 'Fuera de horario del plan.'];
        }

        // 3.b) Evitar duplicado por plan el mismo día
        $yaPlanHoy = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $cliente->id)
            ->where('asistible_type', Clientes::class)
            ->where('tipo_asistencia', 'plan')
            ->exists();
        if ($yaPlanHoy) {
            return [false, 'Asistencia por plan ya registrada hoy.'];
        }

        // 4) Registrar por plan: **siempre puntual** (requisito v1)
        Asistencia::create([
            'asistible_id'        => $cliente->id,
            'asistible_type'      => Clientes::class,
            'tipo_asistencia'     => 'plan',
            'fecha'               => $fecha,
            'hora_entrada'        => $m,
            'estado'              => 'puntual',
            'origen'              => $origen,
            'usuario_registro_id' => auth()->id(),
        ]);

        return [true, 'Entrada por plan registrada.'];
    }

    /* =================
     *  TOGGLE PERSONAL
     * =================*/

    public static function togglePersonal(Personal $p, Carbon $m, string $origen = 'biometrico'): array
    {
        $fecha        = $m->toDateString();
        $debounceMin  = (int) (Config::get('maxpower.asistencia_debounce_min', 5));
        $minSalidaMin = (int) (Config::get('maxpower.personal_min_salida_min', 15));
        $tolAntes     = (int) (Config::get('maxpower.personal_tolerancia_antes', 60));
        $tolDesp      = (int) (Config::get('maxpower.personal_tolerancia_despues', 0));

        // ¿Salida?
        if ($abierta = self::abiertaDePersonal($p)) {
            $mins = Carbon::parse($abierta->hora_entrada)->diffInMinutes($m);
            if ($mins < $debounceMin) {
                return [false, "Marca ignorada (anti-rebote {$debounceMin} min)."];
            }
            if ($mins < $minSalidaMin) {
                return [false, "Aún no. Mínimo {$minSalidaMin} min para salida."];
            }
            $abierta->update(['hora_salida' => $m]);
            return [true, 'Salida registrada.'];
        }

        // Entrada dentro de turno del día
        $dowName = $m->locale('es')->isoFormat('dddd'); // "lunes", etc.
        $turnos = $p->turnos()->where('dia', $dowName)->where('estado', 'activo')->get();

        foreach ($turnos as $t) {
            $ini = Carbon::createFromTimeString($t->hora_inicio)->subMinutes($tolAntes);
            $fin = Carbon::createFromTimeString($t->hora_fin)->addMinutes($tolDesp);

            if ($m->between($ini, $fin)) {
                $estado = $m->lte(Carbon::createFromTimeString($t->hora_inicio)) ? 'puntual' : 'atrasado';

                Asistencia::create([
                    'asistible_id'        => $p->id,
                    'asistible_type'      => Personal::class,
                    'tipo_asistencia'     => 'personal',
                    'fecha'               => $fecha,
                    'hora_entrada'        => $m,
                    'estado'              => $estado,
                    'origen'              => $origen,
                    'usuario_registro_id' => auth()->id(),
                ]);

                return [true, 'Entrada registrada.'];
            }
        }

        // Fuera de turno
        self::registrarDenegadoUnaVez(
            'personal',
            $p->id,
            Personal::class,
            $fecha,
            $m,
            $origen,
            'Fuera de turno asignado.'
        );
        return [false, 'Fuera de turno.'];
    }

    /* ==========================================
     *  Fin programado (para UX de “tiempo restante”)
     * ==========================================*/
    public static function finProgramadoPara(Asistencia $a): ?Carbon
    {
        if (!$a->hora_entrada) return null;

        if ($a->tipo_asistencia === 'sesion' && $a->sesionAdicional?->hora_fin) {
            $gracia = (int) Config::get('maxpower.sesion_tolerancia_despues', 0);
            return Carbon::parse($a->sesionAdicional->hora_fin)->addMinutes($gracia);
        }

        if ($a->tipo_asistencia === 'plan' && $a->asistible) {
            $m = Carbon::parse($a->hora_entrada);
            $ventanas = self::ventanasPlanParaDia($a->asistible, $m);
            $gracia = (int) Config::get('maxpower.plan_tolerancia_despues', 0);

            foreach ($ventanas as $v) {
                if ($m->between($v['inicio'], $v['fin'])) {
                    return $v->fin->copy()->addMinutes($gracia);
                }
            }
        }

        if ($a->tipo_asistencia === 'personal' && method_exists($a->asistible, 'turnoHoy')) {
            $t = $a->asistible->turnoHoy();
            if ($t && $t->hora_fin) {
                $gracia = (int) Config::get('maxpower.personal_tolerancia_despues', 0);
                return Carbon::createFromTimeString($t->hora_fin)->addMinutes($gracia);
            }
        }

        return null;
    }
}
