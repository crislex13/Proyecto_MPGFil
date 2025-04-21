<?php

namespace App\Filament\Resources\VentaProductoResource\Pages;

use App\Filament\Resources\VentaProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListVentaProductos extends ListRecords
{
    protected static string $resource = VentaProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Venta')
        ];
    }
}
