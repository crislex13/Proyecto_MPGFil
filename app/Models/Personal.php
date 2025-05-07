<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class Personal extends Model
{
    protected $table = 'personals';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'telefono',
        'direccion',
        'fecha_de_nacimiento',
        'correo',
        'cargo',
        'biometrico_id',
        'horario',
        'fecha_contratacion',
        'estado',
        'foto',
        'observaciones',
        'registrado_por',
        'modificado_por',
    ];

    protected $casts = [
        'fecha_de_nacimiento' => 'date',
        'fecha_contratacion' => 'date',
        'horario' => 'array',
    ];

    protected $appends = ['nombre_completo'];
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function getFotoUrlAttribute(): string
    {
        return $this->foto ? Storage::url($this->foto) : '/default-user.png';
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class, 'personal_id');
    }

    public function turnoHoy(): ?\App\Models\Turno
    {
        $ahora = now();
        $dia = $ahora->locale('es')->isoFormat('dddd');

        return Turno::where('personal_id', $this->id)
            ->where('dia', $dia)
            ->where('estado', 'activo')
            ->get()
            ->filter(function ($turno) use ($ahora) {
                $inicio = Carbon::createFromFormat('H:i:s', $turno->hora_inicio)->subHour(); // 1h antes puede marcar
                $fin = Carbon::createFromFormat('H:i:s', $turno->hora_fin);

                return $ahora->between($inicio, $fin);
            })
            ->first();
    }
    public function puedeRegistrarEntrada(): array
    {
        $turno = $this->turnoHoy();

        if (!$turno) {
            return [
                'permitido' => false,
                'mensaje' => 'No tiene turno activo en este momento.',
            ];
        }

        $ahora = now();
        $horaInicio = Carbon::createFromFormat('H:i:s', $turno->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i:s', $turno->hora_fin);
        $horaPermitida = $horaInicio->copy()->subHour(); // puede marcar 1 hora antes

        if ($ahora->lt($horaPermitida)) {
            return [
                'permitido' => false,
                'mensaje' => 'Aún no puede registrar su entrada. Espere hasta las ' . $horaPermitida->format('H:i') . '.',
            ];
        }

        if ($ahora->gt($horaFin)) {
            return [
                'permitido' => false,
                'mensaje' => 'El turno ya ha finalizado. No se puede registrar entrada.',
            ];
        }

        $estado = $ahora->lte($horaInicio) ? 'puntual' : 'atrasado';

        return [
            'permitido' => true,
            'mensaje' => 'Puede registrar asistencia. Estado: ' . ucfirst($estado),
            'estado' => $estado,
            'turno' => $turno,
        ];
    }

    public function puedeRegistrarSalida(): bool
    {
        return Asistencia::whereDate('fecha', today())
            ->where('asistible_id', $this->id)
            ->where('asistible_type', self::class)
            ->whereNull('hora_salida')
            ->exists();
    }

    public function permisos()
    {
        return $this->hasMany(\App\Models\PermisoPersonal::class, 'personal_id');
    }

    public function tienePermisoHoy(): ?\App\Models\PermisoPersonal
    {
        return $this->permisos()
            ->where('estado', 'aprobado')
            ->whereDate('fecha_inicio', '<=', today())
            ->whereDate('fecha_fin', '>=', today())
            ->first();
    }
    public function asistencias()
    {
        return $this->morphMany(\App\Models\Asistencia::class, 'asistible');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'modificado_por');
    }

    public function getFotoPathForPdfAttribute()
    {
        return $this->foto ? public_path('storage/' . $this->foto) : public_path('images/default-user.png');
    }

    public function pagos()
    {
        return $this->hasMany(\App\Models\PagoPersonal::class, 'personal_id');
    }

}
