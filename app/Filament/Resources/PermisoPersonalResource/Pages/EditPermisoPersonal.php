<?php

namespace App\Filament\Resources\PermisoPersonalResource\Pages;

use App\Filament\Resources\PermisoPersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermisoPersonal extends EditRecord
{
    protected static string $resource = PermisoPersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
