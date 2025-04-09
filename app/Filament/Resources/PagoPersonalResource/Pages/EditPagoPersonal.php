<?php

namespace App\Filament\Resources\PagoPersonalResource\Pages;

use App\Filament\Resources\PagoPersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;

class EditPagoPersonal extends EditRecord
{
    protected static string $resource = PagoPersonalResource::class;

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
