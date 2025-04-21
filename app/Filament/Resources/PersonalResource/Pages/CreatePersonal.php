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
        // Generar usuario y contraseña basados en nombre y fecha de nacimiento
        $primerNombre = strtolower(explode(' ', trim($data['nombre']))[0]); // Primer nombre en minúscula
        $ci = $data['ci'];
        $usuario = $primerNombre . '_' . $ci;
        $passwordPlano = \Carbon\Carbon::parse($data['fecha_de_nacimiento'])->format('d-m-Y');

        // Verificar si ya existe un usuario con ese CI
        if (!User::where('ci', $ci)->exists()) {
            // Crear el usuario
            $usuarioNuevo = User::create([
                'name' => "{$data['nombre']} {$data['apellido_paterno']} {$data['apellido_materno']}",
                'email' => $usuario . '@sistema.com',
                'ci' => $ci,
                'foto' => $data['foto'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'password' => Hash::make($passwordPlano),
                'estado' => 'activo',
            ]);

            // Asignar rol automáticamente según cargo
            $rol = strtolower($data['cargo']);
            $rolesPermitidos = ['instructor', 'recepcionista'];
            if (in_array($rol, $rolesPermitidos) && Role::where('name', $rol)->exists()) {
                $usuarioNuevo->assignRole($rol);
            } else {
                Notification::make()
                    ->title('⚠️ Rol no permitido')
                    ->body("El cargo '{$data['cargo']}' no tiene un rol asignable en el sistema.")
                    ->warning()
                    ->send();
            }

            // Guardar el ID del usuario como quien lo registró
            $data['registrado_por'] = auth()->id();

            // Notificación visual
            Notification::make()
                ->title('🆕 Usuario creado')
                ->body("Usuario: **{$usuario}**\nContraseña: **{$passwordPlano}**")
                ->success()
                ->send();
            $data['user_id'] = $usuarioNuevo->id;
        }
        $data['registrado_por'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PersonalResource::getUrl('index');
    }
}