<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use App\Filament\Resources\PersonalResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreatePersonal extends CreateRecord
{
    protected static string $resource = PersonalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $primerNombre = ucfirst(explode(' ', trim($data['nombre']))[0]);
        $username = "{$primerNombre}_{$data['ci']}";
        $passwordPlano = \Carbon\Carbon::parse($data['fecha_de_nacimiento'])->format('d-m-Y');

        $user = User::firstOrCreate(
            ['ci' => $data['ci']],
            [
                'name' => "{$data['nombre']} {$data['apellido_paterno']} {$data['apellido_materno']}",
                'foto' => $data['foto'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'username' => $username,
                'password' => bcrypt($passwordPlano),
                'estado' => 'activo',
            ]
        );

        // Actualizar datos si ya existía
        $user->update([
            'name' => "{$data['nombre']} {$data['apellido_paterno']} {$data['apellido_materno']}",
            'foto' => $data['foto'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'username' => $username,
        ]);

        $rol = strtolower($data['cargo']);
        $rolesPermitidos = ['instructor', 'recepcionista'];

        if (in_array($rol, $rolesPermitidos) && !$user->hasRole($rol)) {
            $user->assignRole($rol);
        }

        $data['user_id'] = $user->id;
        $data['registrado_por'] = auth()->id();

        Notification::make()
            ->title('🆕 Usuario asignado')
            ->body("Usuario: **{$username}**\nContraseña: **{$passwordPlano}**")
            ->success()
            ->send();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PersonalResource::getUrl('index');
    }
}