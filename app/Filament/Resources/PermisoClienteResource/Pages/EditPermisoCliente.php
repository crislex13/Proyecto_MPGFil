<?php

namespace App\Filament\Resources\PermisoClienteResource\Pages;

use App\Filament\Resources\PermisoClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\PermisoCliente;

class EditPermisoCliente extends EditRecord
{
    protected static string $resource = PermisoClienteResource::class;

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
