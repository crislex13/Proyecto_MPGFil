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
        return $this->hasOne(Casillero::class);
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
        $plan = $this->planActivoDelDia();

        if (!$plan) {
            return [false, '❌ Acceso denegado: no tienes un plan activo.'];
        }

        if ($plan->estado === 'bloqueado') {
            return [false, '⛔ Acceso denegado: tu plan está bloqueado por deuda.'];
        }

        if (!$plan->plan->ingresos_ilimitados) {
            $yaIngreso = Asistencia::whereDate('fecha', Carbon::today())
                ->where('asistible_id', $this->id)
                ->where('asistible_type', self::class)
                ->exists();

            if ($yaIngreso) {
                return [false, '⚠️ Ya registraste asistencia hoy.'];
            }
        }

        return [true, '✅ Bienvenido. Asistencia registrada.'];
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
        return $this->belongsTo(User::class);
    }

}