<?php

namespace App\Filament\Resources\PlanDisciplinaResource\Pages;

use App\Filament\Resources\PlanDisciplinaResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\PlanDisciplina;
use Illuminate\Validation\ValidationException;

class CreatePlanDisciplina extends CreateRecord
{
    protected static string $resource = PlanDisciplinaResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->data;

        $existe = PlanDisciplina::where('plan_id', $data['plan_id'])
            ->where('disciplina_id', $data['disciplina_id'])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'disciplina_id' => 'Ya existe un precio para esta combinación de plan y disciplina.',
            ]);
        }
    }
}
