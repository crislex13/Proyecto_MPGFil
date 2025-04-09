<?php

namespace App\Filament\Resources\CasilleroResource\Pages;

use App\Filament\Resources\CasilleroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCasillero extends EditRecord
{
    protected static string $resource = CasilleroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
