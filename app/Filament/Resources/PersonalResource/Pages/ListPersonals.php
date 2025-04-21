<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use App\Filament\Resources\PersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;
class ListPersonals extends ListRecords
{
    protected static string $resource = PersonalResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Personal')
        ];
    }
}
