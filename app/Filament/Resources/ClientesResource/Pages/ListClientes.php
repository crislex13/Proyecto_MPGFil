<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListClientes extends ListRecords
{
    protected static string $resource = ClientesResource::class;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Cliente')
        ];
    }
    
}
