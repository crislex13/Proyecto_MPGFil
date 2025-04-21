<?php

namespace App\Filament\Resources\SesionAdicionalResource\Pages;

use App\Filament\Resources\SesionAdicionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSesionAdicional extends CreateRecord
{
    protected static string $resource = SesionAdicionalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
