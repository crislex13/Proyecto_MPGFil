<?php

namespace App\Filament\Resources\DisciplinaResource\Pages;

use App\Filament\Resources\DisciplinaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListDisciplinas extends ListRecords
{
    protected static string $resource = DisciplinaResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Disciplina')
        ];
    }
}
