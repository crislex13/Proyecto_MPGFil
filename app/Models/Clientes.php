<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use App\Models\Asistencia;
use App\Models\SesionAdicional;

class Clientes extends Model
{
    use Notifiable;

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_de_nacimiento',
        'ci',
        'telefono',
        'correo',
        'sexo',
        'foto',
        'biometrico_id',
        'registrado_por',
        'modificado_por',
        'antecedentes_medicos',
        'contacto_emergencia_nombre',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_celular',
        'user_id',
    ];

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function getFotoUrlAttribute(): string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : asset('images/default-user.png');
    }

    public function inscripciones()
    {
        return $this->hasMany(PlanCliente::class);
    }

    public function casillero()
    {
        return $this->hasOne(Casillero::class, 'cliente_id');
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function planesCliente()
    {
        return $this->hasMany(\App\Models\PlanCliente::class, 'cliente_id');
    }

    public function planActivoDelDia(): ?\App\Models\PlanCliente
    {
        return $this->planesCliente()
            ->whereDate('fecha_inicio', '<=', Carbon::today())
            ->whereDate('fecha_final', '>=', Carbon::today())
            ->latest('fecha_final')
            ->with('plan')
            ->first();
    }

    public function puedeRegistrarAsistenciaHoy(): array
    {
        $hoy = now();

        // 1. Validar permiso aprobado hoy
        $permisoHoy = $this->permisos()
            ->where('estado', 'aprobado')
            ->whereDate('fecha', $hoy->toDateString())
            ->exists();

        if ($permisoHoy) {
            return [false, '🚫 No puedes ingresar hoy debido a un permiso aprobado.'];
        }

        // 2. Buscar el plan activo más reciente que esté dentro del rango de fechas
        $planCliente = $this->planesCliente()
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_final', '>=', $hoy)
            ->latest('fecha_final')
            ->with('plan')
            ->first();

        if (!$planCliente) {
            // Buscar si al menos tiene un plan vencido recientemente (últimos 10 días, por ejemplo)
            $ultimoPlan = $this->planesCliente()
                ->whereDate('fecha_final', '<', $hoy)
                ->latest('fecha_final')
                ->first();

            if ($ultimoPlan && $ultimoPlan->estado === 'vencido') {
                return [false, '📆 Tu plan ha vencido. Renueva para seguir asistiendo.'];
            }

            return [false, '❌ No tienes un plan registrado o en periodo válido.'];
        }

        // Validar estado si lo encontró
        if ($planCliente->estado === 'bloqueado') {
            return [false, '💸 Tu plan está bloqueado por falta de pago.'];
        }

        $plan = $planCliente->plan;

        // 4. Validar si ya registró asistencia hoy (si no tiene ingresos ilimitados)
        if (!$plan->ingresos_ilimitados) {
            $yaIngreso = Asistencia::whereDate('fecha', $hoy)
                ->where('asistible_id', $this->id)
                ->where('asistible_type', self::class)
                ->exists();

            if ($yaIngreso) {
                return [false, '⚠️ Ya registraste asistencia hoy.'];
            }
        }

        // 5. Validar horario (si el plan tiene restricciones horarias)
        if ($plan->tieneRestriccionHoraria()) {
            $horaActual = now();
            $horaInicio = Carbon::today()->setTimeFromTimeString($plan->hora_inicio);
            $horaFin = Carbon::today()->setTimeFromTimeString($plan->hora_fin);

            if (!$horaActual->between($horaInicio, $horaFin)) {
                return [false, "⏰ Puedes ingresar solo entre {$plan->hora_inicio} y {$plan->hora_fin}."];
            }
        }

        // 6. Validar días permitidos para asistir
        $diasPlan = $planCliente->dias_permitidos ?? [];
        if (empty($diasPlan)) {
            return [false, "📆 No tienes días habilitados para asistir en tu plan."];
        }

        $hoyIndex = $hoy->dayOfWeek; // 0: domingo, ..., 6: sábado
        $diasPermitidosIndices = collect($diasPlan)->map(fn($dia) => match ($dia) {
            'domingo' => 0,
            'lunes' => 1,
            'martes' => 2,
            'miercoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sabado' => 6,
            default => null,
        })->filter()->unique()->values()->toArray();

        if (!in_array($hoyIndex, $diasPermitidosIndices)) {
            $nombreDia = match ($hoyIndex) {
                0 => 'domingo',
                1 => 'lunes',
                2 => 'martes',
                3 => 'miercoles',
                4 => 'jueves',
                5 => 'viernes',
                6 => 'sabado',
            };

            return [false, "📆 Hoy ({$nombreDia}) no está habilitado en tu plan."];
        }

        // 7. Validar tolerancia de deuda
        if ($planCliente->saldo > 0) {
            $diasDeuda = $hoy->diffInDays($planCliente->fecha_inicio);

            if ($diasDeuda > 5) {
                return [false, '💸 Excediste los 5 días de tolerancia para pagar tu saldo. Plan bloqueado.'];
            }
        }

        // 8. Todo correcto
        return [true, '✅ Bienvenido. Registro exitoso.'];
    }

    public function sesionesAdicionales()
    {
        return $this->hasMany(SesionAdicional::class, 'cliente_id');
    }

    public function tieneSesionAdicionalHoy(): bool
    {
        return SesionAdicional::whereHas('planCliente', function ($query) {
            $query->where('cliente_id', $this->id);
        })
            ->whereDate('fecha', today())
            ->exists();
    }

    public function sesionesAdicionalesDeHoy()
    {
        return SesionAdicional::where('cliente_id', $this->id)
            ->whereDate('fecha', today())
            ->get();
    }

    public function sesionesAdicionalesDeHoyNoRegistradas()
    {
        return $this->sesionesAdicionalesDeHoy()->filter(function ($sesion) {
            return !Asistencia::where('sesion_adicional_id', $sesion->id)->exists();
        });
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'asistible_id')
            ->where('asistible_type', self::class);
    }

    public function getFotoPathForPdfAttribute(): string
    {
        return $this->foto
            ? public_path('storage/' . $this->foto)
            : public_path('images/default-user.png');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoCliente::class, 'cliente_id');
    }

}