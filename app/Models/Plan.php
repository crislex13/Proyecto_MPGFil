<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Clientes;
use App\Models\PlanDisciplina;

class Plan extends Model
{
    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'duracion_dias',
        'ingresos_ilimitados',
    ];

    protected $casts = [
        'ingresos_ilimitados' => 'boolean',
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
}

