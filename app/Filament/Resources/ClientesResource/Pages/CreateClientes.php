<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientes extends CreateRecord
{
    protected static string $resource = ClientesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Aseguramos que el path de la imagen se guarde correctamente
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