<?php

namespace App\Filament\Resources\PermisoClienteResource\Pages;

use App\Filament\Resources\PermisoClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;

class ListPermisoClientes extends ListRecords
{
    protected static string $resource = PermisoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Permiso')
        ];
    }
}
