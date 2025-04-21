<?php

namespace App\Filament\Resources\PermisoPersonalResource\Pages;

use App\Filament\Resources\PermisoPersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermisoPersonal extends CreateRecord
{
    protected static string $resource = PermisoPersonalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
