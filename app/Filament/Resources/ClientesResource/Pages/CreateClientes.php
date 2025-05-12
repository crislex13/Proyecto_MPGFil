<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class CreateClientes extends CreateRecord
{
    protected static string $resource = ClientesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validar duplicado de CI
        if (Clientes::where('ci', $data['ci'])->exists()) {
            Notification::make()
                ->title('❌ CI duplicado')
                ->body('Ya existe un cliente con ese número de CI.')
                ->danger()
                ->send();

            $this->halt(); // Detiene la ejecución
        }

        // Formateo de imagen si existe
        if (!empty($data['foto'])) {
            $data['foto'] = str_replace('public/', '', $data['foto']);
        }

        $data['registrado_por'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $cliente = $this->record;

        $primerNombre = ucfirst(explode(' ', $cliente->nombre)[0]);
        $username = "{$primerNombre}_{$cliente->ci}";
        $passwordPlano = \Carbon\Carbon::parse($cliente->fecha_de_nacimiento)->format('d-m-Y');

        $user = User::firstOrCreate(
            ['ci' => $cliente->ci],
            [
                'name' => "{$cliente->nombre} {$cliente->apellido_paterno}",
                'foto' => $cliente->foto,
                'telefono' => $cliente->telefono,
                'username' => $username,
                'password' => bcrypt($passwordPlano),
                'estado' => 'activo',
            ]
        );

        // Actualizar datos si ya existía
        $user->update([
            'name' => "{$cliente->nombre} {$cliente->apellido_paterno}",
            'foto' => $cliente->foto,
            'telefono' => $cliente->telefono,
            'username' => $username,
        ]);

        // Asignar rol si aún no lo tiene
        if (!$user->hasRole('cliente')) {
            $user->assignRole('cliente');
        }

        $cliente->update(['user_id' => $user->id]);

        Notification::make()
            ->title('🆕 Usuario asignado')
            ->body("Usuario: **{$username}**\nContraseña: **{$passwordPlano}**")
            ->success()
            ->send();

        $this->record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}