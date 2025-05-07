<?php

namespace App\Filament\Resources\PermisoClienteResource\Pages;

use App\Filament\Resources\PermisoClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermisoCliente extends CreateRecord
{
    protected static string $resource = PermisoClienteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
