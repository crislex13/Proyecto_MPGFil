<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;
class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Producto')
        ];
    }
}
