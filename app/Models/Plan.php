<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Clientes;
use App\Models\PlanDisciplina;
use App\Traits\HasAuditoria;

class Plan extends Model
{
    use HasAuditoria;
    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'duracion_dias',
        'ingresos_ilimitados',
        'hora_inicio',
        'hora_fin',
        'registrado_por',
        'modificado_por',
    ];

    protected $casts = [
        'ingresos_ilimitados' => 'boolean',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    public function clientes(): HasMany
    {
        return $this->hasMany(Clientes::class);
    }

    public function planDisciplinas(): HasMany
    {
        return $this->hasMany(PlanDisciplina::class);
    }

    public function permiteIngresosIlimitados(): bool
    {
        return $this->ingresos_ilimitados;
    }

    public function tieneRestriccionHoraria(): bool
    {
        return $this->hora_inicio && $this->hora_fin;
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }
    

}