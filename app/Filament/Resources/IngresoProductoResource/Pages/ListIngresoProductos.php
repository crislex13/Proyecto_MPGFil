<?php

namespace App\Filament\Resources\IngresoProductoResource\Pages;

use App\Filament\Resources\IngresoProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListIngresoProductos extends ListRecords
{
    protected static string $resource = IngresoProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Ingreso')
        ];
    }
    
}
