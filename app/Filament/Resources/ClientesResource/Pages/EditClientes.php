<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class EditClientes extends EditRecord
{
    protected static string $resource = ClientesResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['foto'])) {
            $data['foto'] = str_replace('public/', '', $data['foto']);
        }

        $data['modificado_por'] = auth()->id();

        $cliente = $this->record;
        if ($cliente->user_id) {
            $usuario = User::find($cliente->user_id);
            if ($usuario) {
                $primerNombre = explode(' ', $data['nombre'])[0];
                $nuevoUsuario = "{$primerNombre}_{$data['ci']}";
                $nuevaPassword = Hash::make(\Carbon\Carbon::parse($data['fecha_de_nacimiento'])->format('d-m-Y'));

                $usuario->update([
                    'name' => $data['nombre'] . ' ' . $data['apellido_paterno'],
                    'ci' => $data['ci'],
                    'telefono' => $data['telefono'],
                    'email' => $nuevoUsuario . '@sistema.com',
                    'password' => $nuevaPassword,
                ]);

                if (!$usuario->hasRole('cliente')) {
                    $usuario->syncRoles(['cliente']);
                }

                Notification::make()
                    ->title('🔄 Usuario actualizado')
                    ->body("El usuario del cliente ha sido sincronizado correctamente.")
                    ->success()
                    ->send();
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}