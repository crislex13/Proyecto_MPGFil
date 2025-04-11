<?php

namespace App\Filament\Resources\SesionAdicionalResource\Pages;

use App\Filament\Resources\SesionAdicionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSesionAdicionals extends ListRecords
{
    protected static string $resource = SesionAdicionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
