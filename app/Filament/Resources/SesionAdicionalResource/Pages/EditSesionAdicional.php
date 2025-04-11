<?php

namespace App\Filament\Resources\SesionAdicionalResource\Pages;

use App\Filament\Resources\SesionAdicionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSesionAdicional extends EditRecord
{
    protected static string $resource = SesionAdicionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
