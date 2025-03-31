<?php

namespace App\Filament\Resources\PlanDisciplinaResource\Pages;

use App\Filament\Resources\PlanDisciplinaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanDisciplinas extends ListRecords
{
    protected static string $resource = PlanDisciplinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
