<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use App\Traits\HasAuditoria;

class PlanDisciplina extends Model
{
    use HasAuditoria;
    protected $table = 'plan_disciplinas';

    protected $fillable = [
        'plan_id',
        'disciplina_id',
        'precio',
        'registrado_por',
        'modificado_por',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $existe = self::where('plan_id', $model->plan_id)
                ->where('disciplina_id', $model->disciplina_id)
                ->when($model->id, fn($q) => $q->where('id', '!=', $model->id))
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'disciplina_id' => 'Ya existe un precio para esta combinación de plan y disciplina.',
                ]);
            }
        });
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
