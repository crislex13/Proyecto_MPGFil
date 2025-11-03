<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use App\Models\PlanCliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use App\Models\SesionAdicional;

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
        $existe = Asistencia::whereDate('fecha', $fecha)
            ->where('asistible_id', $asistibleId)
            ->where('asistible_type', $asistibleType)
            ->where('tipo_asistencia', $tipo)
            ->where('estado', 'acceso_denegado')
            ->first();

        if ($existe) {
            // Si quieres que reaparezca en el Kiosk cuando se repite el intento, "enciéndelo" tocando updated_at:
            // $existe->touch();
            return;
        }

        Asistencia::create([
            'asistible_id' => $asistibleId,
            'asistible_type' => $asistibleType,
            'tipo_asistencia' => $tipo,
            'fecha' => $fecha,
            'hora_entrada' => $momento,
            'estado' => 'acceso_denegado',
            'origen' => $origen,
            'usuario_registro_id' => auth()->id(),
            'observacion' => $observacion,
        ]);
    }

    /* =========================
     *  Helpers específicos (cliente)
     * =========================*/

    /** ¿Tiene permiso aprobado justo hoy? */
    protected static function clienteTienePermisoAprobadoHoy(Clientes $c, Carbon $m): bool
    {
        if (!method_exists($c, 'permisos'))
            return false;

        return $c->permisos()
            ->whereDate('fecha', $m->toDateString())
            ->where('estado', 'aprobado')
            ->exists();
    }

    /** Marca de deuda (ajusta al campo real que uses) */
    protected static function clienteEstaEnDeuda(Clientes $c): bool
    {
        // Cambia 'deuda_activa' por tu bandera real de deuda
        return (bool) data_get($c, 'deuda_activa', false);
    }

    /** ¿Sigue dentro de la ventana de gracia (N días desde fecha_inicio del plan)? */
    protected static function clienteDentroGraciaDeuda(PlanCliente $pc, Carbon $m): bool
    {
        $gracia = (int) config('maxpower.cliente_gracia_deuda_dias', 5);
        $inicio = Carbon::parse($pc->fecha_inicio)->startOfDay();
        $fin = $inicio->copy()->addDays($gracia)->endOfDay();

        return $m->greaterThanOrEqualTo($inicio) && $m->lessThanOrEqualTo($fin);
    }

    /** Cuenta de ingresos por plan hoy (para limitar a 1 si no es ilimitado) */
    protected static function clienteIngresosPlanHoy(Clientes $c, Carbon $m): int
    {
        return Asistencia::whereDate('fecha', $m->toDateString())
            ->where('asistible_id', $c->id)
            ->where('asistible_type', Clientes::class)
            ->where('tipo_asistencia', 'plan')
            ->count();
    }

    /* =========================
     *  Ventanas de PLAN (cliente)
     * =========================*/

    public static function planVigenteHoy(?Clientes $cliente, Carbon $momento): ?PlanCliente
    {
        if (!$cliente)
            return null;

        $fecha = $momento->toDateString();

        // Siempre desde DB para evitar caché/relación stale
        return PlanCliente::with('plan')
            ->where('cliente_id', $cliente->id)
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_final', '>=', $fecha)
            ->latest('fecha_final')
            ->first();
    }

    /** Días permitidos como ints 1..7 (Lun..Dom) */
    public static function diasPermitidosPlan(PlanCliente $pc): array
    {
        $src = $pc->dias_permitidos ?? [];
        $map = ['domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6];

        $out = [];
        foreach ((array) $src as $d) {
            $out[] = is_numeric($d) ? (int) $d : ($map[strtolower($d)] ?? null);
        }
        // 0..6
        return array_values(array_unique(array_filter($out, fn($v) => $v !== null && $v >= 0 && $v <= 6)));
    }

    private static function toTimeCarbon($v): ?Carbon
    {
        if (!$v)
            return null;
        if ($v instanceof \DateTimeInterface) {
            return Carbon::createFromTimeString($v->format('H:i:s'));
        }
        return Carbon::createFromTimeString((string) $v);
    }

    public static function ventanasPlanParaDia(Clientes $cliente, Carbon $momento): array
    {
        $pc = self::planVigenteHoy($cliente, $momento);
        if (!$pc)
            return [];

        // Día actual 0..6 (0=domingo … 6=sábado) porque PlanCliente guarda 0..6
        $dow = (int) $momento->dayOfWeek;

        // Días permitidos normalizados 0..6
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
                $ventanas[] = ['inicio' => $hi, 'fin' => $hf, 'origen' => 'plan'];
            }
        }

        // b) Horarios específicos (si existen)
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
                $diaHorario = (int) data_get($h, 'dia_semana');

                // Si tu tabla guarda 0..6, deja esta línea:
                $coincide = ($diaHorario === $dow);
                // Si guarda 1..7, usa en cambio: $coincide = ($diaHorario === (int) $momento->dayOfWeekIso);

                if ($coincide) {
                    $hi = self::toTimeCarbon(data_get($h, 'hora_inicio'));
                    $hf = self::toTimeCarbon(data_get($h, 'hora_fin'));
                    if ($hi && $hf) {
                        $ventanas[] = ['inicio' => $hi, 'fin' => $hf, 'origen' => 'horarios'];
                    }
                }
            }
        }

        // ✅ Fallback: si hay día permitido pero SIN horarios, permite todo el día
        if (empty($ventanas)) {
            $ventanas[] = [
                'inicio' => Carbon::createFromTimeString('00:00:00'),
                'fin' => Carbon::createFromTimeString('23:59:59'),
                'origen' => 'libre',
            ];
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
                    'fin' => Carbon::parse($hf),
                    'origen' => 'sesion',
                    'sesion' => $s,
                ];
            }
        }
        return $out;
    }

    /* ================================
     *  Ingreso directo por SESIÓN
     * ================================*/
    protected function intentarIngresoPorSesion(Clientes $cliente): array
    {
        $ahora = now();

        $sesion = SesionAdicional::with(['turno', 'disciplina', 'instructor'])
            ->activaEn($cliente->id, $ahora)
            ->first();

        if (!$sesion) {
            return $this->denegar('No tiene una sesión activa en este horario.');
        }

        if (!$this->pasaAntireboteCliente($cliente, $ahora)) {
            return $this->denegar('Espere el tiempo mínimo para volver a marcar.');
        }

        $estadoLlegada = $ahora->lte(Carbon::createFromTimeString($sesion->hora_inicio))
            ? 'puntual' : 'atrasado';

        $asistencia = $this->registrarAsistenciaCliente(
            cliente: $cliente,
            fecha: $ahora->toDateString(),
            horaEntrada: $ahora->format('H:i:s'),
            contexto: [
                'tipo' => 'sesion',
                'sesion_id' => $sesion->id,
                'disciplina_id' => $sesion->disciplina_id,
                'instructor_id' => $sesion->instructor_id,
                'turno_id' => $sesion->turno_id,
                'estado_llegada' => $estadoLlegada,
            ]
        );

        return $this->ok("Asistencia registrada a sesión ({$estadoLlegada}).", [
            'asistencia_id' => $asistencia->id,
        ]);
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

    public static function toggleCliente(Clientes $cliente, Carbon $m, string $origen = 'biometrico'): array
    {
        $fecha = $m->toDateString();
        $debounceMin = (int) Config::get('maxpower.asistencia_debounce_min', 5);
        $minSalidaMin = (int) Config::get('maxpower.cliente_min_salida_min', 15);

        $tolSesA = (int) Config::get('maxpower.sesion_tolerancia_antes', 15);
        $tolSesD = (int) Config::get('maxpower.sesion_tolerancia_despues', 0);
        $tolPlanA = (int) Config::get('maxpower.plan_tolerancia_antes', 0);
        $tolPlanD = (int) Config::get('maxpower.plan_tolerancia_despues', 0);

        // 0) Salida si hay abierta (anti-rebote + mínimo de salida)
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

        // A) Permiso aprobado hoy (bloqueo duro)
        $permitePermiso = false; // o, si prefieres por config, asegúrate: config('maxpower.permiso_cliente_permite_ingreso') === false

        if (self::clienteTienePermisoAprobadoHoy($cliente, $m)) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Tiene permiso aprobado: hoy no corresponde ingreso'
            );
            return [false, 'Acceso denegado por permiso (bloqueo activo).'];
        }

        // B) Sesión adicional (única por sesión)
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
                    'asistible_id' => $cliente->id,
                    'asistible_type' => Clientes::class,
                    'tipo_asistencia' => 'sesion',
                    'sesion_adicional_id' => $sesion->id,
                    'fecha' => $fecha,
                    'hora_entrada' => $m,
                    'estado' => $estado,
                    'origen' => $origen,
                    'usuario_registro_id' => auth()->id(),
                ]);

                return [true, 'Entrada a sesión registrada.'];
            }
        }

        // Tiene sesión hoy pero fuera de ventana → denegado
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

        // C) Validaciones del plan (vigencia + deuda + día/horario)
        $pc = self::planVigenteHoy($cliente, $m);
        if (!$pc) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Sin plan vigente en esta fecha'
            );
            return [false, 'No tiene plan vigente.'];
        }

        // Deuda con gracia de N días
        if (self::clienteEstaEnDeuda($cliente) && !self::clienteDentroGraciaDeuda($pc, $m)) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Plan en deuda y fuera del periodo de gracia'
            );
            return [false, 'Deuda fuera del periodo de gracia.'];
        }

        // Día permitido + ventanas horario
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

        // ⬇️ NUEVO: exigir estar dentro de alguna ventana del plan
        $tieneVentanaActiva = false;
        foreach ($ventanasPlan as $v) {
            if (self::contieneMomento($v, $m, $tolPlanA, $tolPlanD)) {
                $tieneVentanaActiva = true;
                break;
            }
        }

        if (!$tieneVentanaActiva) {
            self::registrarDenegadoUnaVez(
                'plan',
                $cliente->id,
                Clientes::class,
                $fecha,
                $m,
                $origen,
                'Fuera del horario permitido del plan'
            );
            return [false, 'Fuera del horario permitido del plan.'];
        }

        $pc = $pc ?? self::planVigenteHoy($cliente, $m);

        // --- Política de ingresos diarios ---
        $ilimitadoPlan = (bool) data_get($pc?->plan, 'ingresos_ilimitados', false);

        // Si tu tabla `plans` tiene un campo entero para “varios ingresos por día”
        $maxPorDiaPlan = (int) data_get($pc?->plan, 'max_ingresos_diarios', 0);
        // 0 o null => usar default del sistema

        // Fallback por config (opcional)
        $ilimitadoCfg = (bool) config('maxpower.plan_ingresos_ilimitados', false);
        $defaultMaxCfg = (int) config('maxpower.plan_max_ingresos_diarios_por_defecto', 1);

        // Resultado efectivo
        $ilimitado = $ilimitadoPlan || $ilimitadoCfg;
        $maxPorDia = $ilimitado ? null : ($maxPorDiaPlan > 0 ? $maxPorDiaPlan : $defaultMaxCfg);

        if (!$ilimitado) {
            // Solo cuentan ingresos válidos; no cuentes 'acceso_denegado'
            $estadosValidos = ['puntual', 'atrasado', 'permiso']; // ajusta si 'permiso' no debe contar
            $ingresosHoy = Asistencia::whereDate('fecha', $fecha)
                ->where('asistible_id', $cliente->id)
                ->where('asistible_type', Clientes::class)
                ->where('tipo_asistencia', 'plan')
                ->whereIn('estado', $estadosValidos)
                ->count();

            if ($ingresosHoy >= $maxPorDia) {
                return [false, "Límite diario alcanzado ({$maxPorDia})."];
            }
        }

        // Reglas:

        // Registrar por plan (siempre puntual)
        Asistencia::create([
            'asistible_id' => $cliente->id,
            'asistible_type' => Clientes::class,
            'tipo_asistencia' => 'plan',
            'fecha' => $fecha,
            'hora_entrada' => $m,
            'estado' => 'puntual',
            'origen' => $origen,
            'usuario_registro_id' => auth()->id(),
        ]);

        return [true, 'Entrada por plan registrada.'];
    }

    /* =================
     *  TOGGLE PERSONAL
     * =================*/

    public static function togglePersonal(Personal $p, Carbon $m, string $origen = 'biometrico'): array
    {
        $fecha = $m->toDateString();
        $debounceMin = (int) Config::get('maxpower.asistencia_debounce_min', 5);
        $minSalidaMin = (int) Config::get('maxpower.personal_min_salida_min', 15);
        $tolAntes = (int) Config::get('maxpower.personal_tolerancia_antes', 60);
        $tolDesp = (int) Config::get('maxpower.personal_tolerancia_despues', 0);

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

        // Permiso aprobado hoy => registrar como 'permiso' sin exigir turno
        if (
            method_exists($p, 'permisos') &&
            $p->permisos()->where('estado', 'aprobado')->vigenteEn($fecha)->exists()
        ) {
            Asistencia::create([
                'asistible_id' => $p->id,
                'asistible_type' => Personal::class,
                'tipo_asistencia' => 'personal',
                'fecha' => $fecha,
                'hora_entrada' => $m,
                'estado' => 'permiso',
                'origen' => $origen,
                'usuario_registro_id' => auth()->id(),
            ]);
            return [true, 'Asistencia registrada como permiso.'];
        }

        // Entrada dentro de turno del día
        $dow = $m->isoWeekday(); // 1..7
        $turnos = $p->turnos()->where('dia', $dow)->where('estado', 'activo')->get();

        foreach ($turnos as $t) {
            $ini = Carbon::createFromTimeString($t->hora_inicio)->subMinutes($tolAntes);
            $fin = Carbon::createFromTimeString($t->hora_fin)->addMinutes($tolDesp);

            if ($m->between($ini, $fin)) {
                $estado = $m->lte(Carbon::createFromTimeString($t->hora_inicio)) ? 'puntual' : 'atrasado';

                Asistencia::create([
                    'asistible_id' => $p->id,
                    'asistible_type' => Personal::class,
                    'tipo_asistencia' => 'personal',
                    'fecha' => $fecha,
                    'hora_entrada' => $m,
                    'estado' => $estado,
                    'origen' => $origen,
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
     *  Fin programado (para UX “tiempo restante”)
     * ==========================================*/
    public static function finProgramadoPara(Asistencia $a): ?Carbon
    {
        if (!$a->hora_entrada)
            return null;

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
                    return $v['fin']->copy()->addMinutes($gracia);
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

    /* ======================
     *  Helpers de respuesta
     * ======================*/
    protected function ok(string $msg, array $data = []): array
    {
        return [true, $msg, $data];
    }

    protected function denegar(string $msg): array
    {
        return [false, $msg];
    }

    /* ==================================
     *  Anti-rebote (cliente) reutilizable
     * ==================================*/
    protected function pasaAntireboteCliente(Clientes $cliente, Carbon $momento): bool
    {
        $debounceMin = (int) config('maxpower.asistencia_debounce_min', 5);

        if ($abierta = self::abiertaDeCliente($cliente)) {
            // Ya tiene una asistencia abierta → no permitir otra entrada
            return false;
        }

        // Revisa la última entrada de hoy para evitar spam de entradas seguidas
        $ultima = \App\Models\Asistencia::whereDate('fecha', $momento->toDateString())
            ->where('asistible_id', $cliente->id)
            ->where('asistible_type', Clientes::class)
            ->latest('hora_entrada')
            ->first();

        if ($ultima) {
            $mins = Carbon::parse($ultima->hora_entrada)->diffInMinutes($momento);
            if ($mins < $debounceMin)
                return false;
        }

        return true;
    }

    /* ==========================================
     *  Registro de asistencia (cliente genérico)
     * ==========================================*/
    protected function registrarAsistenciaCliente(
        Clientes $cliente,
        string $fecha,
        string $horaEntrada,
        array $contexto = []
    ): Asistencia {
        // Tipo por defecto: plan | sesión (acá viene 'sesion')
        $tipo = $contexto['tipo'] ?? 'plan';

        $payload = [
            'asistible_id' => $cliente->id,
            'asistible_type' => Clientes::class,
            'tipo_asistencia' => $tipo,
            'fecha' => $fecha,
            'hora_entrada' => $horaEntrada,
            'estado' => $contexto['estado_llegada'] ?? 'puntual',
            'origen' => $contexto['origen'] ?? 'biometrico',
            'usuario_registro_id' => auth()->id(),
        ];

        // Campos específicos cuando es sesión
        if ($tipo === 'sesion') {
            $payload['sesion_adicional_id'] = $contexto['sesion_id'] ?? null;
            // Si deseas guardar referencias extra (no obligatorias)
            $payload['disciplina_id'] = $contexto['disciplina_id'] ?? null;
            $payload['instructor_id'] = $contexto['instructor_id'] ?? null;
            $payload['turno_id'] = $contexto['turno_id'] ?? null;
        }

        return Asistencia::create($payload);
    }
}
