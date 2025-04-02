<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Resources\Pages\CreateRecord;


class CreateClientes extends CreateRecord
{
    protected static string $resource = ClientesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['foto'])) {
            $data['foto'] = str_replace('public/', '', $data['foto']);
        }

        $data['saldo'] = max(0, $data['saldo']);
        $data['total'] = max(0, $data['total']);
        $data['casillero_monto'] = max(0, $data['casillero_monto']);
        $data['a_cuenta'] = max(0, $data['a_cuenta']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
