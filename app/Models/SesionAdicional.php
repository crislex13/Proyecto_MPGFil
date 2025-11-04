<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditoria;
use Carbon\Carbon;


class SesionAdicional extends Model
{
    use HasAuditoria;
    protected $table = 'sesiones_adicionales';
    protected $fillable = [
        'cliente_id',
        'plan_cliente_id',
        'disciplina_id',
        'instructor_id',
        'turno_id',
        'hora_inicio',
        'hora_fin',
        'tipo_sesion',
        'precio',
        'fecha',
        'metodo_pago',
        'registrado_por',
        'modificado_por',
    ];

    protected static function booted(): void
    {
        static::saving(function ($sesion) {
            // Normaliza fecha -> turno
            if ($sesion->turno_id && $sesion->fecha) {
                $turno = $sesion->turno()->first();
                if ($turno) {
                    // Valida día del turno VS fecha (si tu 'dia' es numérico 1..7)
                    $fecha = Carbon::parse($sesion->fecha);
                    $diaIso = (int) $fecha->isoWeekday(); // 1..7

                    // Si tu columna dia es string como "martes", adapta con un map.
                    if (is_numeric($turno->dia) && (int) $turno->dia !== $diaIso) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'fecha' => 'La fecha no corresponde al día del turno seleccionado.',
                        ]);
                    }
                }
            }

            // ⛑️ SOLO validar horas si AMBAS vienen informadas
            $hi = $sesion->hora_inicio ? Carbon::parse($sesion->hora_inicio) : null;
            $hf = $sesion->hora_fin ? Carbon::parse($sesion->hora_fin) : null;

            if ($hi && $hf) {
                if ($hf->lessThanOrEqualTo($hi)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'hora_fin' => 'La hora_fin debe ser mayor a la hora_inicio.',
                    ]);
                }

                // Validación contra el turno (solo si existe turno)
                if (!empty($sesion->turno_id)) {
                    $turno = $sesion->turno()->first();
                    if ($turno && $turno->hora_inicio && $turno->hora_fin) {
                        $tHi = Carbon::createFromTimeString($turno->hora_inicio);
                        $tHf = Carbon::createFromTimeString($turno->hora_fin);

                        if ($hi->lt($tHi) || $hf->gt($tHf)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'hora_inicio' => 'La sesión debe estar dentro del horario del turno.',
                                'hora_fin' => 'La sesión debe estar dentro del horario del turno.',
                            ]);
                        }
                    }
                }
            }

            // Valida que el instructor imparta la disciplina (si ambos existen)
            if (!empty($sesion->instructor_id) && !empty($sesion->disciplina_id)) {
                $imparte = $sesion->instructor()
                    ->first()?->disciplinas()
                    ->where('disciplinas.id', $sesion->disciplina_id)
                    ->exists();

                if (!$imparte) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'instructor_id' => 'El instructor no imparte la disciplina elegida.',
                        'disciplina_id' => 'Cambia la disciplina o el instructor.',
                    ]);
                }
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Clientes::class, 'cliente_id');
    }

    public function planCliente()
    {
        return $this->belongsTo(PlanCliente::class, 'plan_cliente_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Personal::class, 'instructor_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function disciplina()
    {
        return $this->belongsTo(\App\Models\Disciplina::class, 'disciplina_id');
    }

    public function scopeActivaEn($q, int $clienteId, Carbon $momento)
    {
        return $q->where('cliente_id', $clienteId)
            ->whereDate('fecha', $momento->toDateString())
            ->whereTime('hora_inicio', '<=', $momento->format('H:i:s'))
            ->whereTime('hora_fin', '>=', $momento->format('H:i:s'));
    }

    public function scopeDelDiaParaCliente($q, int $clienteId, Carbon $dia)
    {
        return $q->where('cliente_id', $clienteId)
            ->whereDate('fecha', $dia->toDateString());
    }

    public function scopeDentroDeVentana($q, Carbon $momento)
    {
        return $q->whereTime('hora_inicio', '<=', $momento->format('H:i:s'))
            ->whereTime('hora_fin', '>=', $momento->format('H:i:s'));
    }

    public function scopeEntreFechas($q, Carbon $desde, Carbon $hasta)
    {
        return $q->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString());
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