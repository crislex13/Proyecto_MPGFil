<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientes extends EditRecord
{
    protected static string $resource = ClientesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asegura que el path de la imagen se guarde correctamente
        if (isset($data['foto'])) {
            $data['foto'] = str_replace('public/', '', $data['foto']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}