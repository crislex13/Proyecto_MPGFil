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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['foto'])) {
            $data['foto'] = str_replace('public/', '', $data['foto']);
        }

        // Asegurarte que el campo esté presente
        $data['casillero_monto'] = $data['casillero_monto'] ?? 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirige a la tabla después de guardar
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['fecha_final']) && !empty($data['fecha_inicio']) && !empty($data['plan_id'])) {
            $plan = \App\Models\Plan::find($data['plan_id']);
            if ($plan) {
                $data['fecha_final'] = \Carbon\Carbon::parse($data['fecha_inicio'])
                    ->addDays($plan->duracion_dias)
                    ->subDay()
                    ->toDateString();
            }
        }

        return $data;
    }

}
