<?php

namespace App\Filament\Resources\PlanDisciplinaResource\Pages;

use App\Filament\Resources\PlanDisciplinaResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\PlanDisciplina;
use Illuminate\Validation\ValidationException;

class EditPlanDisciplina extends EditRecord
{
    protected static string $resource = PlanDisciplinaResource::class;

    protected function beforeSave(): void
    {
        $data = $this->data;

        $existe = PlanDisciplina::where('plan_id', $data['plan_id'])
            ->where('disciplina_id', $data['disciplina_id'])
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'disciplina_id' => 'Ya existe un precio para esta combinación de plan y disciplina.',
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
