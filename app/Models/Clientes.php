<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

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
        'antecedentes_medicos',
        'contacto_emergencia_nombre',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_celular',
        'plan_id',
        'disciplina_id',
        'fecha_inicio',
        'fecha_final',
        'precio_plan',
        'a_cuenta',
        'saldo',
        'total',
        'casillero_monto',
        'metodo_pago',
        'comprobante',
        'estado',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function planDisciplina()
    {
        return PlanDisciplina::where('plan_id', $this->plan_id)
            ->where('disciplina_id', $this->disciplina_id)
            ->first();
    }
    public function getFotoUrlAttribute()
    {
        return $this->foto 
        ? asset('storage/' . $this->foto) 
        : asset('images/default-user.png');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cliente) {
            $cliente->validarFechas();
        });

        static::updating(function ($cliente) {
            $cliente->validarFechas();
        });
    }

    // Método de validación de fechas
    public function validarFechas()
{
    // Solo valida si el registro es nuevo (creación)
    if (!$this->exists && Carbon::parse($this->fecha_inicio)->lt(Carbon::today())) {
        Log::error('Error: La fecha de inicio no puede ser anterior a hoy.', ['fecha_inicio' => $this->fecha_inicio]);

        throw ValidationException::withMessages([
            'fecha_inicio' => 'La fecha de inicio no puede ser anterior a hoy.',
        ]);
    }

    // Valida que la fecha final no sea anterior a la fecha de inicio
    if ($this->fecha_final && Carbon::parse($this->fecha_final)->lt(Carbon::parse($this->fecha_inicio))) {
        Log::error('Error: La fecha de finalización no puede ser anterior a la fecha de inicio.', [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_final' => $this->fecha_final,
        ]);

        throw ValidationException::withMessages([
            'fecha_final' => 'La fecha de finalización no puede ser anterior a la fecha de inicio.',
        ]);
    }
}
}
