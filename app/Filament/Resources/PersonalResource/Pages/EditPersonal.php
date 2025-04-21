<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use App\Filament\Resources\PersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class EditPersonal extends EditRecord
{
    protected static string $resource = PersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $personal = $this->record;

        if ($personal->user_id) {
            $usuario = User::find($personal->user_id);
            if ($personal->user_id && $usuario = User::find($personal->user_id)) {
                $primerNombre = strtolower(explode(' ', trim($data['nombre']))[0]);
                $nuevoUsuario = "{$primerNombre}_{$data['ci']}";
                $nuevaPassword = Hash::make(\Carbon\Carbon::parse($data['fecha_de_nacimiento'])->format('d-m-Y'));

                $usuario->update([
                    'name' => "{$data['nombre']} {$data['apellido_paterno']} {$data['apellido_materno']}",
                    'ci' => $data['ci'],
                    'telefono' => $data['telefono'],
                    'email' => $nuevoUsuario . '@sistema.com',
                    'password' => $nuevaPassword,
                ]);

                Notification::make()
                    ->title('🔄 Usuario sincronizado')
                    ->body("Usuario actualizado a: **{$nuevoUsuario}**")
                    ->success()
                    ->send();
            }
        }
        $data['modificado_por'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PersonalResource::getUrl('index');
    }
}