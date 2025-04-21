<?php

namespace App\Filament\Resources\PermisoPersonalResource\Pages;

use App\Filament\Resources\PermisoPersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermisoPersonals extends ListRecords
{
    protected static string $resource = PermisoPersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
