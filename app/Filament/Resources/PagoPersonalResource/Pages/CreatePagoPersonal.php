<?php

namespace App\Filament\Resources\PagoPersonalResource\Pages;

use App\Filament\Resources\PagoPersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePagoPersonal extends CreateRecord
{
    protected static string $resource = PagoPersonalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
