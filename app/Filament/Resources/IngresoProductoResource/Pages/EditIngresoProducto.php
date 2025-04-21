<?php

namespace App\Filament\Resources\IngresoProductoResource\Pages;

use App\Filament\Resources\IngresoProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngresoProducto extends EditRecord
{
    protected static string $resource = IngresoProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
