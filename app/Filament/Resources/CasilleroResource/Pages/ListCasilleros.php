<?php

namespace App\Filament\Resources\CasilleroResource\Pages;

use App\Filament\Resources\CasilleroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListCasilleros extends ListRecords
{
    protected static string $resource = CasilleroResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Casillero')
        ];
    }
}
