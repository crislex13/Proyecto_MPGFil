<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Clientes;
use App\Models\PlanDisciplina;
use App\Traits\HasAuditoria;

class Disciplina extends Model
{
    use HasAuditoria;
    protected $table = 'disciplinas';
    protected $fillable = [
        'nombre',
        'descripcion',
        'observaciones',
        'registrado_por',
        'modificado_por',
    ];

    public function clientes(): HasMany
    {
        return $this->hasMany(Clientes::class);
    }

    public function planDisciplinas(): HasMany
    {
        return $this->hasMany(PlanDisciplina::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    public function instructores()
    {
        return $this->belongsToMany(\App\Models\Personal::class, 'personal_disciplina')
            ->withPivot(['nivel', 'activo'])
            ->withTimestamps();
    }

}
