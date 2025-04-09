<?php

namespace App\Filament\Resources\SalaResource\Pages;

use App\Filament\Resources\SalaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSala extends EditRecord
{
    protected static string $resource = SalaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirige a la tabla después de guardar
    }

}
