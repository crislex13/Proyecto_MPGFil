<?php

namespace App\Filament\Resources\SalaResource\Pages;

use App\Filament\Resources\SalaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSala extends CreateRecord
{
    protected static string $resource = SalaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
