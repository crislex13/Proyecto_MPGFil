<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Models\PermisoCliente;
use App\Traits\HasAuditoria;


class PlanCliente extends Model
{
    use HasAuditoria;
    protected $table = 'planes_clientes';

    protected $fillable = [
        'cliente_id',
        'plan_id',
        'disciplina_id',
        'fecha_inicio',
        'fecha_final',
        'precio_plan',
        'a_cuenta',
        'saldo',
        'total',
        'metodo_pago',
        'comprobante',
        'estado',
        'dias_permitidos',
        'registrado_por',
        'modificado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'dias_permitidos' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($plan) {
            // 1) Asegurar precio_plan desde PlanDisciplina si viene 0 o vacío
            if (($plan->precio_plan ?? 0) <= 0 && $plan->plan_id && $plan->disciplina_id) {
                $precio = \App\Models\PlanDisciplina::where('plan_id', $plan->plan_id)
                    ->where('disciplina_id', $plan->disciplina_id)
                    ->value('precio');

                // Si no hay precio definido en plan_disciplinas, evita guardar basura:
                if (is_null($precio)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'precio_plan' => 'No se encontró precio para la combinación Plan/Disciplina seleccionada.',
                    ]);
                }

                $plan->precio_plan = (float) $precio;
            }

            // 2) Clamp de a_cuenta
            $precio = (float) ($plan->precio_plan ?? 0);
            $cuenta = (float) ($plan->a_cuenta ?? 0);
            if ($cuenta > $precio) {
                $cuenta = $precio;
                $plan->a_cuenta = $cuenta;
            }

            // 3) Calcular total y saldo
            $plan->total = $precio;
            $plan->saldo = max($precio - $cuenta, 0);

            // 3.5) Recalcular fecha_final SOLO cuando corresponde
            $debeRecalcularFin = empty($plan->fecha_final)
                || $plan->isDirty(['fecha_inicio', 'dias_permitidos', 'plan_id']);

            if ($plan->fecha_inicio && optional($plan->plan)->duracion_dias && $debeRecalcularFin) {
                $plan->fecha_final = $plan->calcularFechaFinalConDiasYPermisos();
            }

            // 4) Estado coherente
            $estadoOriginal = $plan->getOriginal('estado');
            $vencido = $plan->fecha_final
                ? now()->greaterThan(Carbon::parse($plan->fecha_final))
                : false;

            if ($estadoOriginal === 'bloqueado') {
                // solo vuelve a vigente si ya no hay deuda y está dentro de la vigencia
                if (
                    $plan->saldo <= 0 && $plan->fecha_inicio && $plan->fecha_final
                    && now()->between($plan->fecha_inicio, $plan->fecha_final)
                ) {
                    $plan->estado = 'vigente';
                } else {
                    $plan->estado = 'bloqueado';
                }
            } else {
                if ($vencido) {
                    $plan->estado = 'vencido';
                } elseif ($plan->saldo > 0) {
                    $plan->estado = 'deuda';
                } else {
                    $plan->estado = 'vigente';
                }
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Clientes::class, 'cliente_id');
    }

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class, 'plan_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function sesionesAdicionales()
    {
        return $this->hasMany(\App\Models\SesionAdicional::class, 'plan_cliente_id');
    }

    public function getClienteDisplayNameAttribute()
    {
        return $this->cliente
            ? $this->cliente->nombre . ' ' . $this->cliente->apellido_paterno . ' ' . $this->cliente->apellido_materno
            : 'Sin nombre';
    }

    public function recalcularTotal()
    {
        $precioPlan = $this->precio_plan ?? 0;
        $casillero = $this->casillero_monto ?? 0;
        $sesiones = $this->sesionesAdicionales()->sum('precio') ?? 0;

        $total = $precioPlan + $casillero + $sesiones;

        $this->total = $total;
        $this->saldo = max($total - $this->a_cuenta, 0);
        $this->estado = $this->calcularEstado();
        $this->save();
    }

    public function calcularEstado(): string
    {
        if ($this->estado === 'bloqueado') {
            return 'bloqueado';
        }

        // ✅ Fecha final REAL (días permitidos + permisos)
        $fin = $this->calcularFechaFinalConDiasYPermisos(); // devuelve Carbon

        if (now()->greaterThan($fin)) {
            return 'vencido';
        }

        if ($this->saldo > 0) {
            return 'deuda';
        }

        return 'vigente';
    }

    protected function castDiasToIndices($value): array
    {
        $map = ['domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6];

        return collect($value ?? [])
            ->map(fn($d) => is_numeric($d) ? intval($d) : ($map[strtolower($d)] ?? null))
            ->filter(fn($v) => $v !== null)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function setDiasPermitidosAttribute($value): void
    {
        // Normaliza nombres/índices y guarda STRING JSON.
        $norm = $this->castDiasToIndices($value);
        $this->attributes['dias_permitidos'] = json_encode($norm, JSON_UNESCAPED_UNICODE);
    }

    public function calcularFechaFinalConDiasYPermisos(): Carbon
    {
        $planDias = (int) ($this->plan?->duracion_dias ?? 0);
        $inicio = Carbon::parse($this->fecha_inicio)->startOfDay();
        $diasSel = $this->dias_permitidos ?? [];

        // Si no hay selección (o los 7 días), es rango corrido:
        if (empty($diasSel) || count($diasSel) === 7) {
            $finBase = $inicio->copy()->addDays(max($planDias - 1, 0));
        } else {
            // Cuenta solo días permitidos:
            $permitidos = collect($diasSel)->map(fn($d) => (int) $d)->unique()->all();
            $finBase = $inicio->copy();
            $pend = $planDias;
            $guard = 0;
            while ($pend > 0 && $guard < 730) { // guard: límite de seguridad
                if (in_array($finBase->dayOfWeek, $permitidos, true)) {
                    $pend--;
                }
                if ($pend > 0)
                    $finBase->addDay();
                $guard++;
            }
        }

        // Permisos aprobados dentro del rango base:
        $permisosExtra = PermisoCliente::where('cliente_id', $this->cliente_id)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$inicio, $finBase])
            ->count();

        // ⬇️ En vez de addDays($permisosExtra), avanzamos contando SOLO días permitidos
        if ($permisosExtra > 0) {
            $permitidos = collect($diasSel)->map(fn($d) => (int) $d)->unique()->all();
            $extraPend = $permisosExtra;
            $guard = 0;
            $cursor = $finBase->copy();
            while ($extraPend > 0 && $guard < 730) {
                $cursor->addDay();
                if (empty($permitidos) || count($permitidos) === 7 || in_array($cursor->dayOfWeek, $permitidos, true)) {
                    $extraPend--;
                }
                $guard++;
            }
            return $cursor;
        }

        return $finBase;
    }
    public function registradoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'modificado_por');
    }

}
