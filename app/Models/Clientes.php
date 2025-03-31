<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

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

}
