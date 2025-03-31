<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Clientes;
use App\Models\PlanDisciplina;

class Disciplina extends Model
{
    protected $table = 'disciplinas';
    protected $fillable = [
        'nombre',
        'descripcion',
        'observaciones',
    ];

    public function clientes(): HasMany
    {
        return $this->hasMany(Clientes::class);
    }

    public function planDisciplinas(): HasMany
    {
        return $this->hasMany(PlanDisciplina::class);
    }
}
