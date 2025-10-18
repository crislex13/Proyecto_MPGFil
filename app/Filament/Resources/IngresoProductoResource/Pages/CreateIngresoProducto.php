<?php

namespace App\Filament\Resources\IngresoProductoResource\Pages;

use App\Filament\Resources\IngresoProductoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIngresoProducto extends CreateRecord
{
    protected static string $resource = IngresoProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}