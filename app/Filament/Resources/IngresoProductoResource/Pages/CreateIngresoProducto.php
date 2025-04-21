<?php

namespace App\Filament\Resources\IngresoProductoResource\Pages;

use App\Filament\Resources\IngresoProductoResource;
use App\Models\Productos;
use Filament\Resources\Pages\CreateRecord;

class CreateIngresoProducto extends CreateRecord
{
    protected static string $resource = IngresoProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $producto = $this->record->producto;

        if ($producto) {
            $producto->increment('stock_unidades', $this->record->cantidad_unidades);
            $producto->increment('stock_paquetes', $this->record->cantidad_paquetes ?? 0);
        }
    }
}