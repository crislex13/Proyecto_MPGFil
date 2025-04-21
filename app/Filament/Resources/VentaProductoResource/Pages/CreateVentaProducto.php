<?php

namespace App\Filament\Resources\VentaProductoResource\Pages;

use App\Filament\Resources\VentaProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVentaProducto extends CreateRecord
{
    protected static string $resource = VentaProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
