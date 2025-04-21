<?php

namespace App\Filament\Resources\CategoriaProductoResource\Pages;

use App\Filament\Resources\CategoriaProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaProducto extends CreateRecord
{
    protected static string $resource = CategoriaProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
