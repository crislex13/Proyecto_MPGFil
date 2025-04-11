<?php

namespace App\Filament\Resources\PlanDisciplinaResource\Pages;

use App\Filament\Resources\PlanDisciplinaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListPlanDisciplinas extends ListRecords
{
    protected static string $resource = PlanDisciplinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Precio') 
        ];
    }
}
